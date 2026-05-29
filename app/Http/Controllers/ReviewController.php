<?php

namespace App\Http\Controllers;

use App\Http\Requests\Review\CreateReviewRequest;
use App\Http\Requests\Review\UpdateReviewRequest;
use App\Http\Responses\Response;
use App\Services\ReviewService;
use OpenApi\Attributes as OA;
use Throwable;

class ReviewController extends Controller
{
    protected ReviewService $service;

    public function __construct(ReviewService $service)
    {
        $this->service = $service;
    }

    #[OA\Get(
        path: '/api/reviews',
        summary: 'List all reviews (admin)',
        security: [['bearerAuth' => []]],
        tags: ['Reviews'],
        responses: [
            new OA\Response(response: 200, description: 'Reviews retrieved', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 500, description: 'Server error', ref: '#/components/schemas/ErrorResponse'),
        ]
    )]
    public function index()
    {
        $data = [];
        try {
            $data = $this->service->index();
            return Response::Success($data['reviews'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Post(
        path: '/api/reviews',
        summary: 'Create a review for an ad',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['ad_id', 'rating'],
                properties: [
                    new OA\Property(property: 'ad_id', type: 'integer', example: 1),
                    new OA\Property(property: 'rating', type: 'number', format: 'float', minimum: 0, maximum: 5, example: 4.5),
                    new OA\Property(property: 'comment', type: 'string', example: 'Great property!'),
                ]
            )
        ),
        tags: ['Reviews'],
        responses: [
            new OA\Response(response: 201, description: 'Review created', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 422, description: 'Validation error', ref: '#/components/schemas/ValidationErrorResponse'),
        ]
    )]
    public function user_store(CreateReviewRequest $request)
    {
        $data = [];
        try {
            $data = $this->service->user_store($request->validated());
            return Response::Success($data['review'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Get(
        path: '/api/reviews/{id}',
        summary: 'Get a single review',
        security: [['bearerAuth' => []]],
        tags: ['Reviews'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Review retrieved', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 404, description: 'Not found', ref: '#/components/schemas/ErrorResponse'),
        ]
    )]
    public function show(string $id)
    {
        $data = [];
        try {
            $data = $this->service->show($id);
            return Response::Success($data['review'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Put(
        path: '/api/reviews/{id}',
        summary: 'Update a review',
        security: [['bearerAuth' => []]],
        tags: ['Reviews'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'rating', type: 'number', format: 'float', minimum: 0, maximum: 5, example: 4.0),
                    new OA\Property(property: 'comment', type: 'string', example: 'Updated comment'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Review updated', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 422, description: 'Validation error', ref: '#/components/schemas/ValidationErrorResponse'),
        ]
    )]
    public function user_update(UpdateReviewRequest $request, string $id)
    {
        $data = [];
        try {
            $data = $this->service->user_update($request->validated(), $id);
            return Response::Success($data['review'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Delete(
        path: '/api/reviews/{id}',
        summary: 'Delete a review (admin)',
        security: [['bearerAuth' => []]],
        tags: ['Reviews'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Review deleted', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 500, description: 'Server error', ref: '#/components/schemas/ErrorResponse'),
        ]
    )]
    public function destroy(string $id)
    {
        $data = [];
        try {
            $data = $this->service->destroy($id);
            return Response::Success($data['review'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Delete(
        path: '/api/client/reviews/{id}',
        summary: 'Delete own review (client)',
        security: [['bearerAuth' => []]],
        tags: ['Reviews'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Review deleted', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 500, description: 'Server error', ref: '#/components/schemas/ErrorResponse'),
        ]
    )]
    public function client_destroy(string $id)
    {
        $data = [];
        try {
            $data = $this->service->client_destroy($id);
            return Response::Success($data['review'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Get(
        path: '/api/ad/{ad_id}/reviews',
        summary: "Get all reviews for a specific ad",
        tags: ['Reviews'],
        parameters: [
            new OA\Parameter(name: 'ad_id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Reviews retrieved', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 500, description: 'Server error', ref: '#/components/schemas/ErrorResponse'),
        ]
    )]
    public function ad_index(string $ad_id)
    {
        $data = [];
        try {
            $data = $this->service->ad_index($ad_id);
            return Response::Success($data['reviews'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Get(
        path: '/api/user/ad/{ad_id}/reviews',
        summary: "Get the authenticated user's reviews for an ad",
        security: [['bearerAuth' => []]],
        tags: ['Reviews'],
        parameters: [
            new OA\Parameter(name: 'ad_id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Reviews retrieved', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 500, description: 'Server error', ref: '#/components/schemas/ErrorResponse'),
        ]
    )]
    public function get_user_reviews($ad_id)
    {
        $data = [];
        try {
            $data = $this->service->get_user_reviews($ad_id);
            return Response::Success($data['reviews'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }
}
