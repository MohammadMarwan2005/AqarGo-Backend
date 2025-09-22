<?php

namespace App\Services;

use App\Jobs\CheckNotifyMeJob;
use App\Models\Ad;
use App\Models\Apartment;
use App\Models\Land;
use App\Models\NotifyMe;
use App\Models\User;
use App\Models\UserSearches;
use App\Services\Property\ApartmentService;
use App\Services\Property\LandService;
use App\Services\Property\OfficeService;
use App\Services\Property\ShopService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;


class AdService
{
    /**
     * Create a new class instance.
     */
    protected array $propertyTypeMap = [
        'apartment' => \App\Models\Apartment::class,
        'land' => \App\Models\Land::class,
        'shop' => \App\Models\Shop::class,
        'office' => \App\Models\Office::class,
    ];
    protected ApartmentService $apartmentService;
    protected LandService $landService;
    protected OfficeService $officeService;
    protected ShopService $shopService;
    protected FavoriteService $favoriteService;

    public function __construct(ApartmentService $apartmentService, LandService $landService,
                                OfficeService $officeService, ShopService $shopService,FavoriteService $favoriteService,

    )
    {
        $this->apartmentService = $apartmentService;
        $this->landService = $landService;
        $this->officeService = $officeService;
        $this->shopService = $shopService;
        $this->favoriteService = $favoriteService;
    //    $this->fcmService = $fcmService;
    }

    public function DamascusTime($ad)
    {
        $ad->start_date=$ad->start_date->timezone('Asia/Baghdad')->subHour(10);
        $ad->end_date=$ad->end_date->timezone('Asia/Baghdad')->subHour(10);
        return $ad;
    }
    public function format($ads)
    {
        if($ads instanceof \Illuminate\Database\Eloquent\Collection) {
            $ads=$ads->map(function($ad)  {
                $ad=$this->DamascusTime($ad);
                $ad['property']['type']=strtolower(class_basename($ad['property']['propertyable_type']));
                $ad['Is_favorite']=$this->favoriteService->IsFavorite($ad['id']);
                return $ad;
            });
        }
        else
        {
            $ads=$this->DamascusTime($ads);
            $ads['property']['type']=strtolower(class_basename($ads['property']['propertyable_type']));
           $ads['Is_favorite']=$this->favoriteService->IsFavorite($ads['id']);
        }
        return $ads;
    }
    public function index()
    {
        $ads=Ad::query()->with(['property.propertyable','property.images','property.user.profile'])->paginate(10);

        if(!$ads)
        {
            $message='ads not found';
            $code=404;
        }
        $message='ads retrieved successfully';
        $code=200;

        $ads->getCollection()->transform(fn($ad) => $this->format($ad));

        return ['ads'=>$ads,'message'=>$message,'code'=>$code];
    }
    public function getUserAds( $request) : array
    {
        //for admin
        $user=auth()->user();
        if($user->hasRole('super_admin') || $user->hasRole('admin'))
        $id=$request->id;

       // for client
        if(!$request->id)
            $id=auth('api')->id();


        $user=User::query()->with(['ads.property.images','ads.property.propertyable'])->find($id);
        if(!$user)
        {
            return ['ads'=>null,'message'=>'user not found','code'=>404];
        }

//        $ads=Ad::query()->join('properties','properties.id','=','ads.property_id')
//            ->where('user_id',$user->id)->with(['property.images','property.propertyable'])->select('ads.*');
        $ads=$user->ads()->with(['property.propertyable','property.images'])->get();
        $ads=$this->format($ads);

        $message='user ads list';
        $code=200;
        return ['ads'=>$ads,'message'=>$message,'code'=>$code];

    }
    public function show($id) : array
    {
        $ad=Ad::query()->with(['property.propertyable','property.images','property.user.profile'])->find($id);

        if(!$ad)
        {
            $message='ad not found';
            $code=404;
            return ['ad'=>$ad,'message'=>$message,'code'=>$code];
        }

        if($ad->property->user_id!=auth('api')->id())
        $ad->increment('views');

        $ad=$this->format($ad);
        $message='add retrieved successfully';
        $code=200;
        return ['ad'=>$ad,'message'=>$message,'code'=>$code];

    }
    public function create($request) : array
    {
        $user = auth()->user();
        $adsCount = $user->ads()->where('is_active', true)->count();

        if ($user->hasRole('client') && $adsCount >= 3) {
            return ['ad' => null, 'message' => 'you have to upgrade your acount to have +3 ads activated', 'code' => 403];
        } else if ($user->hasRole('premium_client') && $adsCount >= 25) {
            return ['ad' => null, 'messsage' => 'u cant have +25 ads activated', 'code' => 403];
        }

        $start_date = Carbon::now();
        $end_date = now()->addDays(3);

        $ad = Ad::query()->create([
            'property_id' => $request['property_id'],
            'start_date' => $start_date,
            'end_date' => $end_date,
        ]);

        $ad->property()->update(['is_ad' => true]);
        $ad = Ad::query()->with(['property.images', 'property.propertyable'])->find($ad->id);
        $ad = $this->format($ad);

        // pass the created ad into the notifier (immediate) or dispatch a job here
        $this->sendfornotifyme($ad);

        return ['ad' => $ad, 'message' => 'ad created successfully', 'code' => 200];
    }

    public function activate($id ):array
    {
        $ad=Ad::query()->find($id);
        $user=auth('api')->user();

        if(!$ad)
        {
            $message='the ad does not published yet';
            $code=404;
            return ['ad'=>$ad,'message'=>$message,'code'=>$code];
        }

        $cnt=$user->ads()->where('is_active',true)->count();
        $ad_to_active=Ad::query()->where('is_active',true)
            ->where('id',$ad->id)->value('is_active');
        if(!$ad_to_active)
            $cnt =$cnt+1;

        if($cnt>3 && $user->hasRole('client'))
            return ['ad'=>$ad,'message'=>'u have to upgrade your acount to have +3 ads activated','code'=>403];
        else if($user->hasRole('premium_client') && $cnt >25)
            return ['ad'=>$ad,'message'=>'u cant have +25 ads activated','code'=>403];


        $ad->start_date=Carbon::now();
        $ad->end_date=now()->addDays(3);
        $ad->is_active=true;
        $ad->save();

//        $ad->start_date=$ad->start_date->timezone('Asia/Baghdad')->subHour(10);
//        $ad->end_date=$ad->end_date->timezone('Asia/Baghdad')->subHour(10);

        $ad=$this->DamascusTime($ad);

        $message='ad activated successfully';
        $code=200;

        return ['ad'=>$ad,'message'=>$message,'code'=>$code];
    }
    public function getAdsByPropertyType($request): array
    {
        if (!array_key_exists(strtolower($request['type']), $this->propertyTypeMap)) {
            return [
                'ads' => [],
                'message' => 'Invalid property type: ' . $request['type'],
                'code' => 400
            ];
        }

        $class = $this->propertyTypeMap[strtolower($request['type'])];
        $ads = Ad::query()
            ->where('is_active', true)
            ->whereHas('property', fn($q) => $q->where('propertyable_type', $class))
            ->with(['property.propertyable','property.images'])
            ->get();

      $ads=$this->format($ads);


        return [
            'ads' => $ads,
            'message' => 'Get ' . ucfirst(strtolower($request['type'])) . ' ads',
            'code' => 200
        ];
    }
    public function activateSelectedAds($request):array
    {

        $user=$request['user_id']??null;
        if(!$user)
            $user=auth('api')->user();
        if(!$user)
        {
            return ['ads'=>'error','message'=>'enter user id or token','code'=>404];
        }

     $all=$request['all']??null;
        if($all)
        {

//         $adsCount=$user->ads()->count();
//         if($adsCount>3)
//         {
//             return ['ads'=>[],'message'=>'you have to upgrade your acount to have +3 ads activated'];
//         }

            if($user->ads()->count()>3 && $user->hasRole('client'))
                return ['ads'=>null,'message'=>'u have to upgrade your acount to have +3 ads activated','code'=>403];
            else if($user->ads()->count()>25 && $user->hasRole('premium_client'))
                return ['ads'=>null,'message'=>'u cant have +25 ads activated','code'=>403];
         //updating the ads
         $user->ads()->update(['is_active'=>true,
         'start_date'=>Carbon::now(),'end_date'=>Carbon::now()->addDays(3)]);

         $ads=$user->ads;

         return ['ads'=>$ads,'message'=>'ads activated successfully','code'=>200];

        }

        $ids_to_activate=$request['ads']??[];

        $currentActivateCount=$user->ads()->where('is_active',true)->count();
        $ads_to_activate=$user->ads()->whereIn('ads.id',$ids_to_activate)->get();

        $inactivateAdsToActivate=$ads_to_activate->filter(function ($ad)
        {
            return $ad->is_active == false;
        });


        if($inactivateAdsToActivate->count()+$currentActivateCount>3  && $user->hasRole('client'))
        {
            return ['ads'=>null,'message'=>'u have to upgrade your acount to have +3 ads activated','code'=>403];
        }
        else if($inactivateAdsToActivate->count()+$currentActivateCount>25  && $user->hasRole('premium_client'))
        {
            return ['ads'=>null,'message'=>'u cant have +25 ads activated','code'=>403];
        }

        // here u have to check the number of ads
        //first u have ot check the number of activated ads
        //then check the $ids if its similar and more than 3 the total of the selected ads and the activated you have to throw an error

        foreach($ids_to_activate as $id)
        {

            Ad::query()->where('id',$id)
                ->update(['is_active'=>true,'start_date'=>Carbon::now(),'end_date'=>Carbon::now()->addDays(3)]);

            $ad=Ad::query()->find($id);
            $ad=$this->DamascusTime($ad);
            $ads[]=$ad;
        }

        return ['ads'=>$ads,'message'=>'ads activated successfully','code'=>200];
    }
    public function unactivate($id) :array
    {

        $ad=Ad::query()->find($id);
        if(!$ad)
        {
            $message='the ad does not published yet';
            $code=404;
            return ['ad'=>$ad,'message'=>$message,'code'=>$code];
        }
        $ad->is_active=false;
        $ad->save();

        $ad=$this->DamascusTime($ad);
        $message='ad unactivated successfully';
        $code=200;
        return ['ad'=>$ad,'message'=>$message,'code'=>$code];
    }
    public function delete($id):array
    {

        $ad=Ad::query()->find($id);

        if(!$ad)
        {
            $message='there is no add to delete';
            $code=404;
            return ['ad'=>$ad,'message'=>$message,'code'=>$code];
        }

        $user=auth('api')->user();
        if(!$user->hasRole('super_admin') && !$user->hasRole('admin'))
        {
            $user_id=auth('api')->id();
            if($user_id != $ad->property->user_id)
            {
                return ['ad'=>null,'message'=>'you are not allowed to delete this property','code'=>403];
            }
        }


        $ad->property->is_ad=false;
        $ad->property->save(); // ✅ This line is required
        $ad->delete();
        $message='deleted successfully';
        $code=200;
        return ['ad'=>$ad,'message'=>$message,'code'=>$code];
    }
    public function nearToYou($request): array
    {

        $user = auth('api')->user();

        $userLat = $request['latitude'];
        $userLng = $request['longitude'];

        // تأكد إنو عنده إحداثيات
        if (!$userLat || !$userLng) {
            if($user)
            {
                $userLat = $user->profile->latitude;
                $userLng = $user->profile->longitude;
            }


            if (!$userLat || !$userLng)
            {
                return [
                    'ads' => [],
                    'message' => 'User location not set',
                    'code' => 422,
                ];
            }
        }

        // المسافة باستخدام Haversine

        $user = auth()->user();


        $ads = Ad::query()
            ->where('is_active', true)
            ->whereHas('property', function ($query) {
                $query->whereNotNull('latitude')
                    ->whereNotNull('longitude');
            })
            ->join('properties', 'ads.property_id', '=', 'properties.id')
            ->selectRaw('ads.*, properties.latitude, properties.longitude,
        6371 * acos(
            cos(radians(?)) * cos(radians(properties.latitude)) *
            cos(radians(properties.longitude) - radians(?)) +
            sin(radians(?)) * sin(radians(properties.latitude))
        ) as distance', [$userLat, $userLng, $userLat])
            ->with([
                'property.images',
                'property.propertyable'
            ])
            ->orderBy('distance')
            ->paginate($request['number']??10);

        $ads->getCollection()->transform(fn($ad) => $this->format($ad));
    //    $ads=$this->format($ads);

        //we need propertyable + type
        return [
            'ads' => $ads,
            'message' => 'Ads near you',
            'code' => 200,
        ];
    }
    public function querySearch($request)
    {


        $query=Ad::query()->where('is_active',true)
            ->join('properties', 'ads.property_id', '=', 'properties.id')
            ->join('users','users.id','=','properties.user_id')
            ->orderBy('has_active_subscription','desc')
            ->select('ads.*');



        if (isset($request['min_price'])) {
            $query->where('properties.price', '>=', $request['min_price']);
        }

        if (isset($request['max_price'])) {
            $query->where('properties.price', '<=', $request['max_price']);
        }

        if (isset($request['min_area'])) {
            $query->where('properties.area', '>=', $request['min_area']);
        }

        if (isset($request['max_area'])) {
            $query->where('properties.area', '<=', $request['max_area']);
        }


        switch ($request['type']??null)
        {
            case 'apartment':
                $query= $this->apartmentService->search($query,$request['data']??[]);
                break;
            case 'land':
                $query=$this->landService->search($query,$request['data']??[]);
                break;
            case 'office':
                $query=$this->officeService->search($query,$request['data']??[]);
                break;
            case 'shop':
                $query=$this->shopService->search($query,$request['data']??[]);
                break;
        }


      // u have to do a join with premuim user and order by it


        return $query;
    }
    public function search ($request):array
    {
       $query=$this->querySearch($request);

        $ads=$query->with('property.propertyable','property.images')
            ->paginate($request['num']??10);


        $ads->getCollection()->transform(fn($ad) => $this->format($ad));

        if(auth('api')->id())
        {

            UserSearches::query()->create([
              'user_id'=>auth('api')->id(),
               'filters'=>$request
            ]);
        }

        return['ads'=>$ads,'message'=>'Search results found','code'=>200];
    }
    public function recommend($request):array
    {


        $valid=Validator::make($request->all(),[
           'user_id'=>'nullable|integer|exists:users,id',
           'num'=>'nullable|integer',
        ]);

        if($valid->fails())
        {
          return ['ads'=>null,'message'=>$valid->errors(),'code'=>422];
        }


      if(!request('user_id') && !auth()->check())
      {
          $ads=Ad::query()->where('is_active',true)
              ->with('property.propertyable','property.images')->orderBy('views','desc')
              ->paginate($request['num']??10);

          $ads->getCollection()->transform(fn($ad) => $this->format($ad));

          $message='recommended ads retrieved successfully';
          $code=200;
          return ['ads'=>$ads,'message'=>$message,'code'=>$code];
      }

      $lastSearches=UserSearches::query()
          ->where('user_id',auth('api')->id())->latest()->take(5)->get();


      $finalQuery=null;
      foreach($lastSearches as $index=> $lastSearch)
      {
          $query=$this->querySearch($lastSearch->filters);

          if($index==0)
              $finalQuery=$query;
          else
          $finalQuery=$finalQuery->union($query);
      }

      // because if $final was null it will throw an error
      if(!$finalQuery)
      {
          $finalQuery=Ad::query()
              ->where('is_active',true)
              ->orderBy('views','desc')->take(10);
      }
      else if($finalQuery->count()<10)
      {
          $finalQuery=$finalQuery->union(Ad::query()
              ->where('is_active',true)
              ->orderBy('views','desc')->take(10));

      }

      if(!$finalQuery)
      {
          $ads=null;
          $message='there is no ads on the app';
          $code=200;
          return ['ads'=>$ads,'message'=>$message,'code'=>$code];
      }


       $ads=$finalQuery->with('property.propertyable','property.images')
           ->paginate($request['num']??10);

        $ads->getCollection()->transform(fn($ad) => $this->format($ad));

      return ['ads'=>$ads,'message'=>'ok','code'=>200];

    }
    public function similarTo($id,$req)
    {


        $ad=Ad::query()->with(['property.propertyable'])->find($id);
        if(!$ad)
        {
            return ['ads'=>null,'message'=>'ads not found','code'=>404];
        }
        $ad=$this->format($ad);





        $request['min_price'] = isset($ad['property']['price']) ? max($ad['property']['price'] - 1000000, 0) : null;
        $request['max_price'] = isset($ad['property']['price']) ? $ad['property']['price'] + 1000000 : null;

        $request['min_area'] = isset($ad['property']['area']) ? max($ad['property']['area'] - 1000, 0) : null;
        $request['max_area'] = isset($ad['property']['area']) ? $ad['property']['area'] + 1000 : null;

        $query=Ad::query()->where('is_active',true)
            ->join('properties', 'ads.property_id', '=', 'properties.id')
            ->select('ads.*');

        if (isset($request['min_price'])) {
            $query->where('properties.price', '>=', $request['min_price']);
        }

        if (isset($request['max_price'])) {
            $query->where('properties.price', '<=', $request['max_price']);
        }

        if (isset($request['min_area'])) {
            $query->where('properties.area', '>=', $request['min_area']);
        }

        if (isset($request['max_area'])) {
            $query->where('properties.area', '<=', $request['max_area']);
        }


        $request['type'] = $ad['property']['type'] ?? null;

        switch ($request['type']??null)
        {
            case 'apartment':
                $query= $this->apartmentService->similarTo($ad['property']['propertyable']??[],$query);
                break;
            case 'land':
                $query=$this->landService->similarTo($ad['property']['propertyable']??[],$query);
                break;
            case 'office':
                $query=$this->officeService->similarTo($ad['property']['propertyable']??[],$query);
                break;
            case 'shop':
                $query=$this->shopService->similarTo($ad['property']['propertyable']??[],$query);
                break;
        }



      //  $query=$this->querySearch($request);


        $ads=$query->with('property.propertyable','property.images')
             -> where('ads.id','!=',$id);


        $ads=$ads->paginate($req['num']??10);

        $ads->getCollection()->transform(fn($ad) => $this->format($ad));

        return ['ads'=>$ads,'message'=>'similar ads','code'=>200];

    }
    // In AdService.php
    public function notifyme(array $filters)
    {
        $filtersString = json_encode($filters);

        $not = NotifyMe::query()->create([
            'user_id' => auth('api')->id(),
            'filters' => $filtersString,
        ]);

        return [
            'notifyme' => $not,
            'message' => 'ok',
            'code' => 200
        ];
    }

    public function sendfornotifyme(Ad $ad)
    {
        // for scale, use chunking; for simplicity:
        $nots = NotifyMe::query()->get();

        foreach ($nots as $not) {
            $filters = $this->normalizeFiltersString($not->filters);

            // Approach A (recommended): reuse your existing querySearch builder but limit to this ad id
            // Assumption: querySearch($filters) returns a Builder targeting ads (or properties) and is chainable
            $query = $this->querySearch($filters);

            // adapt column name if querySearch returns property builder; adjust 'id' -> correct table id if needed
            $matchesThisAd = (bool) $query->where('ads.id', $ad->id)->exists();

            // if your querySearch returns Ad builder with 'id' column, you can use:
            // $matchesThisAd = (bool) $query->where('id', $ad->id)->exists();

            if ($matchesThisAd) {
                $user = User::query()->find($not->user_id);
                if (!$user || empty($user->fcm_token)) {
                    continue; // skip users without valid token
                }

                try {
                    $fcm = new FcmService();
                    // send minimal payload (ad id + few fields) — client can fetch full ad if needed
                    $payload = [
                        'ad_id' => $ad->id,
                        'title' => $ad->property->title ?? null,
                    ];

                    $fcm->sendNotification(
                        $user->fcm_token,
                        'New property matches your search',
                        'A property was added that matches your previous search.',
                        $payload,
                        $ad->id,
                        $user->id
                    );
                } catch (\Throwable $e) {
                    \Log::error("FCM send failed for user {$not->user_id}: {$e->getMessage()}");
                    // continue processing other notify rows
                }
            }
        }
    }

    protected function normalizeFiltersString($filtersString) : array
    {
        // if already array, return as-is
        if (is_array($filtersString)) return $filtersString;

        if (empty($filtersString)) return [];

        // Try JSON decode (what we store now)
        $decoded = json_decode($filtersString, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        // Fallback: parse query-string (in case older rows were saved differently)
        parse_str($filtersString, $parsed);
        if (!empty($parsed)) {
            return $parsed;
        }

        // Last fallback: return raw string under a key so querySearch can handle it if needed
        return ['raw' => $filtersString];
    }


}
