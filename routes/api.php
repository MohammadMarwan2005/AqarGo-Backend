<?php

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Middleware\BlockedUser;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\JwtMiddleware;
use App\Http\Middleware\VerifiedEmail;
use App\Http\Controllers\FcmController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BlockController;
use Illuminate\Container\Attributes\Auth;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\SubscriptionController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;


Route::get('/telegram/link', function () {
    return response()->json([
        'status' => 200,
        'data' => 'https://t.me/aqargo',
        'message' => 'Telegram link fetched successfully',
    ], 200);
});


Route::get('/links', function () {
    return response()->json([
        'status' => 200,
        'data' => [
            'telegram' => 'https://t.me/aqargo',
            'github' => 'https://github.com/MohammadMarwan2005/aqar-go',
        ],
        'message' => 'Links fetched successfully',
    ], 200);
});


Route::get('/telegram/username', function () {
    return response()->json([
        'status' => 200,
        'data' => 'aqargo',
        'message' => 'Telegram username fetched successfully',
    ], 200);
});

Route::get('/user', function (Request $request) {
//    return $request->user();
   // $user=\App\Models\User::findOrFail(1);
   // return $user->properties()->with('propertyable')->get();
});

//)->middleware('auth:sanctum');

//Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
//    $request->fulfill();
//    event(new Verified(\App\Models\User::query()->find($request->route('id'))));
//    return \App\Http\Responses\Response::Success(true,'Email is verified');
//})->middleware(['auth:api','signed'])->name('verification.verify');


Route::get('/email/verify/{id}/{hash}', function (Request $request) {
    $user = User::findOrFail($request->route('id'));

    if (! hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification()))) {
        return response()->json(['message' => 'Invalid verification link'], 403);
    }

    if ($user->hasVerifiedEmail()) {
        return response()->json(['message' => 'Email already verified'], 200);
    }

    $user->markEmailAsVerified();
    event(new Verified($user));
    //this two line is instead of $request->fullfill() because it redirect to route login

    return response()->json(['message' => 'Email verified successfully'], 200);
})
    ->middleware(['signed'])->name('verification.verify');

// resend verification email
Route::post('/email/verification-notification', function (Request $request) {
   $request->user()->sendEmailVerificationNotification();
   return \App\Http\Responses\Response::Success(true,'Verification link sent!');
})
    ->middleware(['auth:api','throttle:6,1'])->name('verification.send');


//auth
Route::controller(AuthController::class)->prefix('auth')
    ->name('auth.')
    ->group(function () {
        Route::post('/register', 'register')->name('register');
        Route::post('/login', 'login')->name('login');
        Route::middleware('auth:api')->post('/logout', 'logout')->name('logout');
        Route::middleware('auth:api')->post('/refresh', 'refresh')->name('refresh');
        Route::middleware('auth:api')->post('/me', 'me')->name('me');
        Route::post('/forgetPassword','forgetPassword')->name('forgetPassword');
        Route::post('/resetPassword','resetPassword')->name('resetPassword');
        Route::post('/checkCode','checkCode')->name('checkCode');
    });

//Profile
Route::controller(ProfileController::class)->prefix('profile')
    ->name('profile.')
    ->middleware([JwtMiddleware::class])
    ->group(function (){
        Route::get('getMyProfile','get_my_profile')->name('get_my_profile')->middleware('can:profile.get_my_profile');
        Route::post('/create', 'create')->name('create')->middleware('can:profile.create');
        Route::get('/show/{id}', 'show')->name('show')->middleware('can:profile.show'); // 2 types and u have to put {id}
        Route::post('/update', 'update')->name('update')->middleware('can:profile.update');
        Route::delete('/delete', 'delete')->name('delete')->middleware('can:profile.delete');
    });

Route::middleware([JwtMiddleware::class,VerifiedEmail::class,BlockedUser::class])->group(function () {

// User
    Route::controller(UserController::class)->prefix('user')
        ->name('user.')
        ->group(function () {
            Route::post('/getUserByEmail','getUserByEmail')->name('getUserByEmail')->middleware('can:user.getUserByEmail');
            Route::get('/getUsers', 'get_users')->name('getUsers')->middleware('can:user.getUsers');
            Route::post('/create', 'create')->name('create')->middleware('can:user.create');
            Route::get('/show/{id}', 'show')->name('show')->middleware('can:user.show');
            Route::post('update/{id}', 'update')->name('update')->middleware('can:user.update');
            Route::delete('/delete/{id}', 'delete')->name('delete')->middleware('can:user.delete');
            Route::get('upgradeToPremium/{id}','upgradeToPremium')->name('upgradeToPremium')
                ->middleware('can:user.upgradeToPremium');
            Route::post('assignUserRole','assignUserRole')->name('assignUserRole')
                ->middleware('can:user.assignUserRole');
            Route::get('upgrade','upgrade')->name('upgrade');
            Route::get('downgrade','downgrade')->name('downgrade');
            Route::get('extra_info','extra_info')->name('extra_info');
        });


//property
Route::controller(\App\Http\Controllers\PropertyController::class)->prefix('property')
->name('property.')
    ->group(function () {
        Route::get('/getProperty/{id}','getProperty')->name('getProperty')->middleware('can:property.getProperty');
       Route::get('/getUserProperties', 'getUserProperties')->name('getUserProperties')->middleware('can:property.getUserProperties');
       Route::post('/create', 'create')->name('create')->middleware('can:property.create');
       Route::post('/getAttributes','getAttributes')->name('getAttributes')->middleware('can:property.getAttributes');
       Route::post('/update/{id}', 'update')->name('update')->middleware('can:property.update');
       Route::delete('/delete/{id}', 'delete')->name('delete')->middleware('can:property.delete');
    });

 //ads
    Route::controller(\App\Http\Controllers\AdController::class)->prefix('ad')
        ->name('ad.')
        ->group(function () {
            Route::get('index','index')
                ->withoutMiddleware([VerifiedEmail::class,JwtMiddleware::class,BlockedUser::class]) ->name('index');
            Route::post('/create', 'create')->name('create')->middleware('can:ad.create');
            Route::get('/activate/{id}','activate')->name('activate')->middleware('can:ad.activate');
            Route::get('show/{id}', 'show')
                ->withoutMiddleware([VerifiedEmail::class,JwtMiddleware::class,BlockedUser::class])->name('show');
            Route::get('unactivate/{id}','unactivate')->name('unactivate')->middleware('can:ad.unactivate');
            Route::post('getAdsByPropertyType','getAdsByPropertyType')
                ->withoutMiddleware([VerifiedEmail::class,JwtMiddleware::class,BlockedUser::class])->name('getAdsByPropertyType');
            Route::post('getUserAds','getUserAds')->name('getUserAds')->middleware('can:ad.getUserAds');
            Route::post('activateSelectedAds','activateSelectedAds')->name('activateSelectedAds')
                ->middleware('can:ad.activateSelectedAds');
            Route::delete('delete/{id}','delete')->name('delete')->middleware('can:ad.delete'); // 2 types
            Route::post('nearToYou','nearToYou')
                ->withoutMiddleware([VerifiedEmail::class,JwtMiddleware::class,BlockedUser::class])->name('nearToYou');
            Route::post('search','search')
                ->withoutMiddleware([VerifiedEmail::class,JwtMiddleware::class,BlockedUser::class])->name('search');
            Route::post('recommend','recommend')
                ->withoutMiddleware([VerifiedEmail::class,JwtMiddleware::class,BlockedUser::class]) ->name('recommend');
            Route::post('similarTo/{id}','similarTo')
                ->withoutMiddleware([VerifiedEmail::class,JwtMiddleware::class,BlockedUser::class]) ->name('similarTo');
            Route::post('notifyme','notifyme')->name('notifyme');
        });

    //block
    Route::controller(BlockController::class)->prefix('block')
        ->name('block.')
        ->group(function () {
           Route::post('/create', 'block')->name('block')->middleware('can:block.block');
           Route::delete('/unblock/{id}', 'unblock')->name('unblock')->middleware('can:block.unblock');
           Route::get('index','index')->name('index');
               //->middleware('can:block.index');
        });
    //report
    Route::controller(ReportController::class)->prefix('report')
        ->name('report.')
        ->group(function () {
           Route::post('/index', 'index')->name('index')->middleware('can:report.index');
           Route::post('/create','create')->name('create')->middleware('can:report.create');
           Route::get('/show/{id}', 'show')->name('show')->middleware('can:report.show');
           Route::post('/showAdReports', 'showAdReports')->name('showAdReports')->middleware('can:report.showAdReports');
           Route::delete('/delete/{id}', 'delete')->name('delete')->middleware('can:report.delete');

        });

});


/*********************************************************************
 *                           yahia routes                            *
 *********************************************************************/


Route::middleware(JwtMiddleware::class)
    ->controller(SubscriptionController::class)                     ->prefix('subscriptions')
                                                                    ->name('subscriptions.')
    ->group(function () {

    Route::get('/activated/admin', 'allActiveSub')                  ->name('allActiveSub')
        ->middleware('can:subscriptions.allActiveSub');
    Route::get('/admin', 'index')                                   ->name('index.admin')
        ->middleware('can:subscriptions.index.admin');
    Route::put('/deactivate/{id}/admin', 'deactivate')              ->name('deactivate.admin')
    ->middleware('can:subscriptions.deactivate.admin');
    Route::get('/{id}/admin', 'show')                               ->name('show.admin')
    ->middleware('can:subscriptions.show.admin');
    Route::delete('/{id}/admin', 'destroy')                         ->name('destroy.admin')
    ->middleware('can:subscriptions.destroy.admin');

    Route::get('/active/client', 'userActiveSub')                   ->name('activeSub.client')
    ->middleware('can:subscriptions.activeSub.client');
    Route::put('/deactivate/client', 'userDeactivate')              ->name('deactivate.client')
    ->middleware('can:subscriptions.deactivate.client');
    Route::get('/client', 'userIndex')                              ->name('index.client')
    ->middleware('can:subscriptions.index.client');
    Route::get('/{id}/client', 'userShow')                          ->name('show.client')
    ->middleware('can:subscriptions.show.client');
    Route::post('/client', 'userCreate')                            ->name('store.client')
    ->middleware('can:subscriptions.store.client');
    Route::get('/time_remaining/{id}/client', 'time_remaining')     ->name('timeRemaining.client')
    ->middleware('can:subscriptions.timeRemaining.client');
});

Route::controller(ReviewController::class)
                                                                    ->name('reviews.')
    ->group(function() {
    Route::get('/ad/{ad_id}/reviews', 'ad_index')
        ->withoutMiddleware(JwtMiddleware::class)->name('ad.index');
        //->middleware('can:reviews.ad.index');
    Route::group(['middleware' => JwtMiddleware::class], function () {
        Route::get('/reviews', 'index')                                 ->name('index')
        ->middleware('can:reviews.index');
        Route::post('/reviews', 'user_store')                           ->name('store')
        ->middleware('can:reviews.store');
        Route::get('/reviews/{id}', 'show')                             ->name('show')
        ->middleware('can:reviews.show');
        Route::put('/reviews/{id}', 'user_update')                      ->name('update')
        ->middleware('can:reviews.update');
        Route::delete('/reviews/{id}', 'destroy')                       ->name('destroy')
        ->middleware('can:reviews.destroy');
        Route::delete('/client/reviews/{id}', 'client_destroy')         ->name('client_destroy')
        ->middleware('can:reviews.client_destroy');
        Route::get('/user/ad/{ad_id}/reviews', 'get_user_reviews')      ->name('user.ad.index')
        ->middleware('can:reviews.user.ad.index');
    });
});

Route::middleware(JwtMiddleware::class)
     ->controller(PlanController::class)
                                                                    ->name('plans.')
     ->group(function () {

    Route::get('/plans/yearly_plans', 'getYearlyPlans')             ->name('yearlyPlans')
    ->middleware('can:plans.yearlyPlans');
    Route::get('/plans/monthly_plans', 'getMonthlyPlans')           ->name('monthlyPlans')
    ->middleware('can:plans.monthlyPlans');
    Route::get('/plans', 'index')                                   ->name('index')
    ->middleware('can:plans.index');
    Route::post('/plans', 'store')                                  ->name('store')
    ->middleware('can:plans.store');
    Route::get('/plans/{id}', 'show')                               ->name('show')
    ->middleware('can:plans.show');
    Route::put('/plans/{id}', 'update')                             ->name('update')
    ->middleware('can:plans.update');
    Route::delete('/plans/{id}', 'destroy')                         ->name('destroy')
    ->middleware('can:plans.destroy');
});

Route::middleware(JwtMiddleware::class)
     ->controller(FavoriteController::class)
                                                                    ->prefix('favorites')
                                                                    ->name('favorite.')
     ->group(function () {

    Route::get('/', 'index')                                        ->name('index')->middleware('can:favorite.index');
    Route::post('/{id}', 'add')                                     ->name('add')->middleware('can:favorite.add');
    Route::delete('/{id}', 'remove')                                ->name('remove')->middleware('can:favorite.remove');
    Route::get('/{id}', 'IsInFavorites')                            ->name('check')->middleware('can:favorite.check');
});


// ***                    notification here                     *** //
Route::controller(FcmController::class)
    ->name('fcm.')
    ->group(function () {

    Route::group(['middleware' => JwtMiddleware::class], function () {

        // إرسال إشعار لجهاز واحد
        Route::post('/fcm/send', 'sendNotification')->name('send');

        // إرسال إشعار لجميع المستخدمين
        Route::post('/fcm/send-all', 'sendNotificationToAll')->name('send_all');

        // جلب كل الإشعارات للمستخدم الحالي
        Route::get('/fcm/notifications', 'index')->name('index');

        // جلب الإشعارات المقروءة
        Route::get('/fcm/notifications/read', 'readIndex')->name('read_index');

        // جلب الإشعارات غير المقروءة
        Route::get('/fcm/notifications/unread', 'unreadIndex')->name('unread_index');

        // جلب عدد الإشعارات غير المقروءة
        Route::get('/fcm/notifications/unread-count', 'unreadCount')->name('unread_count');
    });
});


/*****************************  end here  ****************************/


////location
//Route::controller(LocationController::class)->prefix('location')
//    ->name('location.')
//    ->group(function () {
//       Route::get('/index', 'index')->name('index');
//       Route::post('/create', 'create')->name('create');
//       Route::post('/update/{id}', 'update')->name('update');
//       Route::delete('/delete/{id}', 'delete')->name('delete');
//    });
////governorate
//Route::controller(\App\Http\Controllers\GovernorateController::class)->prefix('governorate')
//    ->name('governorate.')
//    ->group(function () {
//        Route::get('/index', 'index')->name('index');
//        Route::post('/create', 'create')->name('create');
//        Route::post('/update/{id}', 'update')->name('update');
//        Route::delete('/delete/{id}', 'delete')->name('delete');
//    });
////city
//Route::controller(\App\Http\Controllers\CityController::class)->prefix('city')
//    ->name('city.')
//    ->group(function () {
//       Route::get('show/{id}', 'show')->name('show');
//       Route::get('getCitiesByGovernorate/{governorate_id}', 'getCitiesByGovernorate')->name('getCitiesByGovernorate');
//       Route::post('/create', 'create')->name('create');
//       Route::post('/update/{id}', 'update')->name('update');
//       Route::delete('/delete/{id}', 'delete')->name('delete');
//    });
////suggested location
// Route::controller(\App\Http\Controllers\SuggestedLocationController::class)->prefix('suggested-location')
//     ->name('suggested-location.')
//     ->group(function () {
//        Route::get('/index', 'index')->name('index');
//        Route::post('/suggestedLocationsByGovernorate','suggestedLocationsByGovernorate')->name('suggestedLocationsByGovernorate');
//        Route::post('/userSuggestedLocations','userSuggestedLocations')->name('userSuggestedLocations');
//         Route::post('/create', 'create')->name('create');
//         Route::post('/update/{id}', 'update')->name('update');
//        Route::get('/show/{id}', 'show')->name('show');
//        Route::delete('/delete/{id}', 'delete')->name('delete');
//        Route::post('/approve/{id}', 'approve')->name('approve');
//     });
