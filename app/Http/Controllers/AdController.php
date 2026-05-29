<?php

namespace App\Http\Controllers;

use App\Http\Requests\Ads\ActivateSelectedAdsRequest;
use App\Http\Requests\Ads\CreateAdRequest;
use App\Http\Requests\Ads\SearchAdRequest;
use App\Http\Responses\Response;
use App\Services\AdService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Throwable;

class AdController extends Controller
{
    protected AdService $adservice;

    public function __construct(AdService $adService)
    {
        $this->adservice = $adService;
    }

    #[OA\Get(
        path: '/api/ad/index',
        summary: 'List all ads',
        tags: ['Ads'],
        responses: [
            new OA\Response(response: 200, description: 'Ads retrieved', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 500, description: 'Server error', ref: '#/components/schemas/ErrorResponse'),
        ]
    )]
    public function index()
    {
        $data = [];
        try {
            $data = $this->adservice->index();
            return Response::Success($data['ads'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Post(
        path: '/api/ad/getUserAds',
        summary: 'Get ads belonging to a specific user',
        security: [['bearerAuth' => []]],
        tags: ['Ads'],
        responses: [
            new OA\Response(response: 200, description: 'User ads retrieved', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 500, description: 'Server error', ref: '#/components/schemas/ErrorResponse'),
        ]
    )]
    public function getUserAds(Request $request)
    {
        $data = [];
        try {
            $data = $this->adservice->getUserAds($request);
            return Response::Success($data['ads'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Get(
        path: '/api/ad/show/{id}',
        summary: 'Show a single ad',
        tags: ['Ads'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Ad retrieved', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 404, description: 'Not found', ref: '#/components/schemas/ErrorResponse'),
        ]
    )]
    public function show($id)
    {
        $data = [];
        try {
            $data = $this->adservice->show($id);
            return Response::Success($data['ad'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Get(
        path: '/api/ad/activate/{id}',
        summary: 'Activate an ad',
        security: [['bearerAuth' => []]],
        tags: ['Ads'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Ad activated', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 500, description: 'Server error', ref: '#/components/schemas/ErrorResponse'),
        ]
    )]
    public function activate($id)
    {
        $data = [];
        try {
            $data = $this->adservice->activate($id);
            return Response::Success($data['ad'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Post(
        path: '/api/ad/create',
        summary: 'Create a new ad for a property',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['property_id'],
                properties: [
                    new OA\Property(property: 'property_id', type: 'integer', example: 1),
                ]
            )
        ),
        tags: ['Ads'],
        responses: [
            new OA\Response(response: 201, description: 'Ad created', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 422, description: 'Validation error', ref: '#/components/schemas/ValidationErrorResponse'),
            new OA\Response(response: 500, description: 'Server error', ref: '#/components/schemas/ErrorResponse'),
        ]
    )]
    public function create(CreateAdRequest $request)
    {
        $data = [];
        try {
            $data = $this->adservice->create($request->validated());
            return Response::Success($data['ad'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Post(
        path: '/api/ad/getAdsByPropertyType',
        summary: 'Get ads filtered by property type',
        tags: ['Ads'],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'type', type: 'string', enum: ['apartment', 'land', 'office', 'shop']),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Ads retrieved', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 500, description: 'Server error', ref: '#/components/schemas/ErrorResponse'),
        ]
    )]
    public function getAdsByPropertyType(Request $request)
    {
        $data = [];
        try {
            $data = $this->adservice->getAdsByPropertyType($request);
            return Response::Success($data['ads'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Post(
        path: '/api/ad/activateSelectedAds',
        summary: 'Activate multiple ads at once (admin)',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'user_id', type: 'integer', example: 1),
                    new OA\Property(property: 'all', type: 'boolean', example: false),
                    new OA\Property(property: 'ads', type: 'array', items: new OA\Items(type: 'integer'), example: [1, 2, 3]),
                ]
            )
        ),
        tags: ['Ads'],
        responses: [
            new OA\Response(response: 200, description: 'Ads activated', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 500, description: 'Server error', ref: '#/components/schemas/ErrorResponse'),
        ]
    )]
    public function activateSelectedAds(ActivateSelectedAdsRequest $request)
    {
        $data = [];
        try {
            $data = $this->adservice->activateSelectedAds($request->validated());
            return Response::Success($data['ads'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Get(
        path: '/api/ad/unactivate/{id}',
        summary: 'Deactivate an ad',
        security: [['bearerAuth' => []]],
        tags: ['Ads'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Ad deactivated', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 500, description: 'Server error', ref: '#/components/schemas/ErrorResponse'),
        ]
    )]
    public function unactivate($id)
    {
        $data = [];
        try {
            $data = $this->adservice->unactivate($id);
            return Response::Success($data['ad'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Delete(
        path: '/api/ad/delete/{id}',
        summary: 'Delete an ad',
        security: [['bearerAuth' => []]],
        tags: ['Ads'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Ad deleted', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 500, description: 'Server error', ref: '#/components/schemas/ErrorResponse'),
        ]
    )]
    public function delete($id)
    {
        $data = [];
        try {
            $data = $this->adservice->delete($id);
            return Response::Success($data['ad'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Post(
        path: '/api/ad/nearToYou',
        summary: 'Get ads near a given location',
        tags: ['Ads'],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'latitude', type: 'number', format: 'float', example: 33.5138),
                    new OA\Property(property: 'longitude', type: 'number', format: 'float', example: 36.2765),
                    new OA\Property(property: 'radius', type: 'number', example: 10),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Nearby ads retrieved', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 500, description: 'Server error', ref: '#/components/schemas/ErrorResponse'),
        ]
    )]
    public function nearToYou(Request $request)
    {
        $data = [];
        try {
            $data = $this->adservice->nearToYou($request);
            return Response::Success($data['ads'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Post(
        path: '/api/ad/search',
        summary: 'Search ads with filters',
        tags: ['Ads'],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'type', type: 'string', enum: ['apartment', 'land', 'office', 'shop']),
                    new OA\Property(property: 'min_price', type: 'number', example: 50000),
                    new OA\Property(property: 'max_price', type: 'number', example: 200000),
                    new OA\Property(property: 'min_area', type: 'number', example: 50),
                    new OA\Property(property: 'max_area', type: 'number', example: 300),
                    new OA\Property(property: 'num', type: 'integer', example: 10),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Search results', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 422, description: 'Validation error', ref: '#/components/schemas/ValidationErrorResponse'),
        ]
    )]
    public function search(SearchAdRequest $request)
    {
        $data = [];
        try {
            $data = $this->adservice->Search($request->validated());
            return Response::Success($data['ads'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Post(
        path: '/api/ad/recommend',
        summary: 'Get recommended ads',
        tags: ['Ads'],
        responses: [
            new OA\Response(response: 200, description: 'Recommended ads', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 500, description: 'Server error', ref: '#/components/schemas/ErrorResponse'),
        ]
    )]
    public function recommend(Request $request)
    {
        $data = [];
        try {
            $data = $this->adservice->recommend($request);
            return Response::Success($data['ads'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Post(
        path: '/api/ad/similarTo/{id}',
        summary: 'Get ads similar to a given ad',
        tags: ['Ads'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Similar ads retrieved', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 500, description: 'Server error', ref: '#/components/schemas/ErrorResponse'),
        ]
    )]
    public function similarTo($id, Request $req)
    {
        $data = [];
        try {
            $data = $this->adservice->similarTo($id, $req);
            return Response::Success($data['ads'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Post(
        path: '/api/ad/notifyme',
        summary: 'Subscribe to notifications for matching ads',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'type', type: 'string', enum: ['apartment', 'land', 'office', 'shop']),
                    new OA\Property(property: 'min_price', type: 'number', example: 50000),
                    new OA\Property(property: 'max_price', type: 'number', example: 200000),
                    new OA\Property(property: 'min_area', type: 'number', example: 50),
                    new OA\Property(property: 'max_area', type: 'number', example: 300),
                ]
            )
        ),
        tags: ['Ads'],
        responses: [
            new OA\Response(response: 200, description: 'Notification preference saved', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 500, description: 'Server error', ref: '#/components/schemas/ErrorResponse'),
        ]
    )]
    public function notifyme(SearchAdRequest $request)
    {
        $data = [];
        try {
            $data = $this->adservice->notifyme($request->validated());
            return Response::Success($data['notifyme'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }
}
