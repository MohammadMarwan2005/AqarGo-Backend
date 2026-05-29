<?php

namespace App\Http\Controllers;

use App\Http\Requests\Profile\CreateProfileRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Responses\Response;
use App\Services\ProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Throwable;

class ProfileController extends Controller
{
    protected ProfileService $profileService;

    public function __construct(ProfileService $profileService)
    {
        $this->profileService = $profileService;
    }

    #[OA\Get(
        path: '/api/profile/getMyProfile',
        summary: "Get the authenticated user's profile",
        security: [['bearerAuth' => []]],
        tags: ['Profile'],
        responses: [
            new OA\Response(response: 200, description: 'Profile retrieved', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 401, description: 'Unauthenticated', ref: '#/components/schemas/ErrorResponse'),
        ]
    )]
    public function get_my_profile(): JsonResponse
    {
        $data = [];
        try {
            $data = $this->profileService->get_my_profile();
            return Response::Success($data['profile'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Get(
        path: '/api/profile/show/{id}',
        summary: "Show another user's profile",
        security: [['bearerAuth' => []]],
        tags: ['Profile'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Profile retrieved', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 404, description: 'Not found', ref: '#/components/schemas/ErrorResponse'),
        ]
    )]
    public function show($id): JsonResponse
    {
        $data = [];
        try {
            $data = $this->profileService->show($id);
            return Response::Success($data['profile'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Post(
        path: '/api/profile/create',
        summary: 'Create a user profile',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'first_name', type: 'string', example: 'Ahmad'),
                        new OA\Property(property: 'last_name', type: 'string', example: 'Ali'),
                        new OA\Property(property: 'phone_number', type: 'string', example: '0912345678'),
                        new OA\Property(property: 'image_url', type: 'string', format: 'binary'),
                        new OA\Property(property: 'gender', type: 'string', enum: ['male', 'female']),
                        new OA\Property(property: 'latitude', type: 'number', format: 'float', example: 33.51),
                        new OA\Property(property: 'longitude', type: 'number', format: 'float', example: 36.27),
                        new OA\Property(property: 'address', type: 'string', example: 'Damascus, Syria'),
                    ]
                )
            )
        ),
        tags: ['Profile'],
        responses: [
            new OA\Response(response: 201, description: 'Profile created', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 422, description: 'Validation error', ref: '#/components/schemas/ValidationErrorResponse'),
        ]
    )]
    public function create(CreateProfileRequest $request): JsonResponse
    {
        $data = [];
        try {
            $data = $this->profileService->create($request->validated());
            return Response::Success($data['profile'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Post(
        path: '/api/profile/update',
        summary: 'Update the authenticated user profile',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'first_name', type: 'string', example: 'Ahmad'),
                        new OA\Property(property: 'last_name', type: 'string', example: 'Ali'),
                        new OA\Property(property: 'phone_number', type: 'string', example: '0912345678'),
                        new OA\Property(property: 'image_url', type: 'string', format: 'binary'),
                        new OA\Property(property: 'gender', type: 'string', enum: ['male', 'female']),
                        new OA\Property(property: 'user_id', type: 'integer', example: 1),
                        new OA\Property(property: 'latitude', type: 'number', format: 'float', example: 33.51),
                        new OA\Property(property: 'longitude', type: 'number', format: 'float', example: 36.27),
                        new OA\Property(property: 'address', type: 'string', example: 'Damascus, Syria'),
                    ]
                )
            )
        ),
        tags: ['Profile'],
        responses: [
            new OA\Response(response: 200, description: 'Profile updated', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 422, description: 'Validation error', ref: '#/components/schemas/ValidationErrorResponse'),
        ]
    )]
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $data = [];
        try {
            $data = $this->profileService->update($request->validated());
            return Response::Success($data['profile'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Delete(
        path: '/api/profile/delete',
        summary: 'Delete the authenticated user profile',
        security: [['bearerAuth' => []]],
        tags: ['Profile'],
        responses: [
            new OA\Response(response: 200, description: 'Profile deleted', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 500, description: 'Server error', ref: '#/components/schemas/ErrorResponse'),
        ]
    )]
    public function delete(Request $request): JsonResponse
    {
        $data = [];
        try {
            $data = $this->profileService->delete($request);
            return Response::Success($data['profile'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }
}
