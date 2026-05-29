<?php

namespace App\Http\Controllers;

use App\Http\Responses\Response;
use App\Services\FavoriteService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Throwable;

class FavoriteController extends Controller
{
    protected FavoriteService $service;

    public function __construct(FavoriteService $service)
    {
        $this->service = $service;
    }

    #[OA\Get(
        path: '/api/favorites',
        summary: "Get the authenticated user's favorite ads",
        security: [['bearerAuth' => []]],
        tags: ['Favorites'],
        responses: [
            new OA\Response(response: 200, description: 'Favorites retrieved', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 500, description: 'Server error', ref: '#/components/schemas/ErrorResponse'),
        ]
    )]
    public function index(Request $request)
    {
        $data = [];
        try {
            $data = $this->service->index($request);
            return Response::Success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Post(
        path: '/api/favorites/{id}',
        summary: 'Add an ad to favorites',
        security: [['bearerAuth' => []]],
        tags: ['Favorites'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Ad ID', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Added to favorites', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 500, description: 'Server error', ref: '#/components/schemas/ErrorResponse'),
        ]
    )]
    public function add($ad_id)
    {
        $data = [];
        try {
            $data = $this->service->add($ad_id);
            return Response::Success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Delete(
        path: '/api/favorites/{id}',
        summary: 'Remove an ad from favorites',
        security: [['bearerAuth' => []]],
        tags: ['Favorites'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Ad ID', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Removed from favorites', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 500, description: 'Server error', ref: '#/components/schemas/ErrorResponse'),
        ]
    )]
    public function remove($ad_id)
    {
        $data = [];
        try {
            $data = $this->service->remove($ad_id);
            return Response::Success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Get(
        path: '/api/favorites/{id}',
        summary: 'Check if an ad is in favorites',
        security: [['bearerAuth' => []]],
        tags: ['Favorites'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Ad ID', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Check result', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 500, description: 'Server error', ref: '#/components/schemas/ErrorResponse'),
        ]
    )]
    public function IsInFavorites($ad_id)
    {
        $data = [];
        try {
            $data = $this->service->IsInFavorites($ad_id);
            return Response::Success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }
}
