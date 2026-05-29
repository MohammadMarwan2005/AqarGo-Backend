<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\CreateUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Responses\Response;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Throwable;

class UserController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    #[OA\Post(
        path: '/api/user/getUserByEmail',
        summary: 'Find a user by email address',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'user@example.com'),
                ]
            )
        ),
        tags: ['Users'],
        responses: [
            new OA\Response(response: 200, description: 'User found', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 404, description: 'Not found', ref: '#/components/schemas/ErrorResponse'),
        ]
    )]
    public function getUserByEmail(Request $request): JsonResponse
    {
        $data = [];
        try {
            $data = $this->userService->getUserByEmail($request);
            return Response::Success($data['user'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Get(
        path: '/api/user/getUsers',
        summary: 'List all users (admin)',
        security: [['bearerAuth' => []]],
        tags: ['Users'],
        responses: [
            new OA\Response(response: 200, description: 'Users retrieved', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 500, description: 'Server error', ref: '#/components/schemas/ErrorResponse'),
        ]
    )]
    public function get_users(): JsonResponse
    {
        $data = [];
        try {
            $data = $this->userService->get_users();
            return Response::Success($data['users'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Get(
        path: '/api/user/show/{id}',
        summary: 'Get a user by ID',
        security: [['bearerAuth' => []]],
        tags: ['Users'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'User retrieved', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 404, description: 'Not found', ref: '#/components/schemas/ErrorResponse'),
        ]
    )]
    public function show($id): JsonResponse
    {
        $data = [];
        try {
            $data = $this->userService->show($id);
            return Response::Success($data['user'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Post(
        path: '/api/user/create',
        summary: 'Create a new user (admin)',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'first_name', type: 'string', example: 'Ahmad'),
                    new OA\Property(property: 'last_name', type: 'string', example: 'Ali'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'admin@example.com'),
                    new OA\Property(property: 'password', type: 'string', minLength: 8, example: 'password123'),
                    new OA\Property(property: 'phone_number', type: 'string', example: '0912345678'),
                ]
            )
        ),
        tags: ['Users'],
        responses: [
            new OA\Response(response: 201, description: 'User created', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 422, description: 'Validation error', ref: '#/components/schemas/ValidationErrorResponse'),
        ]
    )]
    public function create(CreateUserRequest $request): JsonResponse
    {
        $data = [];
        try {
            $data = $this->userService->create($request->validated());
            return Response::Success($data['user'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Post(
        path: '/api/user/update/{id}',
        summary: 'Update a user',
        security: [['bearerAuth' => []]],
        tags: ['Users'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'first_name', type: 'string', example: 'Ahmad'),
                    new OA\Property(property: 'last_name', type: 'string', example: 'Ali'),
                    new OA\Property(property: 'email', type: 'string', format: 'email'),
                    new OA\Property(property: 'phone_number', type: 'string', example: '0912345678'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'User updated', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 422, description: 'Validation error', ref: '#/components/schemas/ValidationErrorResponse'),
        ]
    )]
    public function update(UpdateUserRequest $request, $id): JsonResponse
    {
        $data = [];
        try {
            $data = $this->userService->update($request->validated(), $id);
            return Response::Success($data['user'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Delete(
        path: '/api/user/delete/{id}',
        summary: 'Delete a user',
        security: [['bearerAuth' => []]],
        tags: ['Users'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'User deleted', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 500, description: 'Server error', ref: '#/components/schemas/ErrorResponse'),
        ]
    )]
    public function delete(string $id): JsonResponse
    {
        $data = [];
        try {
            $data = $this->userService->delete($id);
            return Response::Success($data['user'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Get(
        path: '/api/user/upgradeToPremium/{id}',
        summary: 'Upgrade a user to premium (admin)',
        security: [['bearerAuth' => []]],
        tags: ['Users'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'User upgraded', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 500, description: 'Server error', ref: '#/components/schemas/ErrorResponse'),
        ]
    )]
    public function upgradeToPremium($id): JsonResponse
    {
        $data = [];
        try {
            $data = $this->userService->upgradeToPremium($id);
            return Response::Success($data['user'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Post(
        path: '/api/user/assignUserRole',
        summary: 'Assign a role to a user (admin)',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'user_id', type: 'integer', example: 2),
                    new OA\Property(property: 'role', type: 'string', example: 'admin'),
                ]
            )
        ),
        tags: ['Users'],
        responses: [
            new OA\Response(response: 200, description: 'Role assigned', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 500, description: 'Server error', ref: '#/components/schemas/ErrorResponse'),
        ]
    )]
    public function assignUserRole(Request $request): JsonResponse
    {
        $data = [];
        try {
            $data = $this->userService->assignUserRole($request);
            return Response::Success($data['user'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Get(
        path: '/api/user/upgrade',
        summary: 'Upgrade the authenticated user to premium',
        security: [['bearerAuth' => []]],
        tags: ['Users'],
        responses: [
            new OA\Response(response: 200, description: 'Upgraded', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 500, description: 'Server error', ref: '#/components/schemas/ErrorResponse'),
        ]
    )]
    public function upgrade()
    {
        $data = [];
        try {
            $data = $this->userService->upgrade();
            return Response::Success($data['user'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Get(
        path: '/api/user/downgrade',
        summary: 'Downgrade the authenticated user from premium',
        security: [['bearerAuth' => []]],
        tags: ['Users'],
        responses: [
            new OA\Response(response: 200, description: 'Downgraded', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 500, description: 'Server error', ref: '#/components/schemas/ErrorResponse'),
        ]
    )]
    public function downgrade()
    {
        $data = [];
        try {
            $data = $this->userService->downgrade();
            return Response::Success($data['user'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Get(
        path: '/api/user/extra_info',
        summary: 'Get extra info for the authenticated user',
        security: [['bearerAuth' => []]],
        tags: ['Users'],
        responses: [
            new OA\Response(response: 200, description: 'Extra info retrieved', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 500, description: 'Server error', ref: '#/components/schemas/ErrorResponse'),
        ]
    )]
    public function extra_info()
    {
        $data = [];
        try {
            $data = $this->userService->extra_info();
            return Response::Success($data['extra_info'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }
}
