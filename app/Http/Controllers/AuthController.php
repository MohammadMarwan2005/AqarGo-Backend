<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Responses\Response;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Throwable;

class AuthController extends Controller
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    #[OA\Post(
        path: '/api/auth/register',
        summary: 'Register a new user',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password', 'password_confirmation', 'phone_number'],
                properties: [
                    new OA\Property(property: 'first_name', type: 'string', example: 'Ahmad'),
                    new OA\Property(property: 'last_name', type: 'string', example: 'Ali'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'ahmad@example.com'),
                    new OA\Property(property: 'password', type: 'string', minLength: 8, example: 'password123'),
                    new OA\Property(property: 'password_confirmation', type: 'string', example: 'password123'),
                    new OA\Property(property: 'phone_number', type: 'string', example: '0912345678'),
                    new OA\Property(property: 'fcm_token', type: 'string', example: 'fcm_token_here'),
                ]
            )
        ),
        tags: ['Auth'],
        responses: [
            new OA\Response(response: 201, description: 'Registered successfully', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 422, description: 'Validation error', ref: '#/components/schemas/ValidationErrorResponse'),
            new OA\Response(response: 500, description: 'Server error', ref: '#/components/schemas/ErrorResponse'),
        ]
    )]
    public function register(RegisterRequest $request): JsonResponse
    {
        $data = [];
        try {
            $data = $this->authService->register($request->validated());
            return Response::Success($data['user'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Post(
        path: '/api/auth/login',
        summary: 'Login and get JWT token',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'ahmad@example.com'),
                    new OA\Property(property: 'password', type: 'string', minLength: 8, example: 'password123'),
                    new OA\Property(property: 'fcm_token', type: 'string', example: 'fcm_token_here'),
                ]
            )
        ),
        tags: ['Auth'],
        responses: [
            new OA\Response(response: 200, description: 'Login successful', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 401, description: 'Invalid credentials', ref: '#/components/schemas/ErrorResponse'),
            new OA\Response(response: 422, description: 'Validation error', ref: '#/components/schemas/ValidationErrorResponse'),
        ]
    )]
    public function login(LoginRequest $request): JsonResponse
    {
        $data = [];
        try {
            $data = $this->authService->login($request->validated());
            return Response::Success($data['user'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Post(
        path: '/api/auth/logout',
        summary: 'Logout the authenticated user',
        security: [['bearerAuth' => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(response: 200, description: 'Logged out successfully', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 401, description: 'Unauthenticated', ref: '#/components/schemas/ErrorResponse'),
        ]
    )]
    public function logout(): JsonResponse
    {
        $data = [];
        try {
            $data = $this->authService->logout();
            return Response::Success($data['user'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Post(
        path: '/api/auth/refresh',
        summary: 'Refresh the JWT token',
        security: [['bearerAuth' => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(response: 200, description: 'Token refreshed', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 401, description: 'Unauthenticated', ref: '#/components/schemas/ErrorResponse'),
        ]
    )]
    public function refresh(Request $request): JsonResponse
    {
        $data = [];
        try {
            $data = $this->authService->refresh($request);
            return Response::Success($data['user'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Post(
        path: '/api/auth/me',
        summary: "Get the authenticated user's info",
        security: [['bearerAuth' => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(response: 200, description: 'User info returned', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 401, description: 'Unauthenticated', ref: '#/components/schemas/ErrorResponse'),
        ]
    )]
    public function me(): JsonResponse
    {
        $data = [];
        try {
            $data = $this->authService->me();
            return Response::Success($data['user'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Post(
        path: '/api/auth/forgetPassword',
        summary: 'Send password reset code to email',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'ahmad@example.com'),
                ]
            )
        ),
        tags: ['Auth'],
        responses: [
            new OA\Response(response: 200, description: 'Reset code sent', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 500, description: 'Server error', ref: '#/components/schemas/ErrorResponse'),
        ]
    )]
    public function forgetPassword(Request $request): JsonResponse
    {
        $data = [];
        try {
            $data = $this->authService->forgetPassword($request);
            return Response::Success($data['info'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Post(
        path: '/api/auth/checkCode',
        summary: 'Verify the password reset code',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'code'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'ahmad@example.com'),
                    new OA\Property(property: 'code', type: 'string', example: '123456'),
                ]
            )
        ),
        tags: ['Auth'],
        responses: [
            new OA\Response(response: 200, description: 'Code verified', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 500, description: 'Invalid or expired code', ref: '#/components/schemas/ErrorResponse'),
        ]
    )]
    public function checkCode(Request $request): JsonResponse
    {
        $data = [];
        try {
            $data = $this->authService->checkCode($request);
            return Response::Success($data['info'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Post(
        path: '/api/auth/resetPassword',
        summary: "Reset the user's password",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password', 'password_confirmation'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'ahmad@example.com'),
                    new OA\Property(property: 'password', type: 'string', minLength: 8, example: 'newpassword123'),
                    new OA\Property(property: 'password_confirmation', type: 'string', example: 'newpassword123'),
                ]
            )
        ),
        tags: ['Auth'],
        responses: [
            new OA\Response(response: 200, description: 'Password reset successfully', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 500, description: 'Server error', ref: '#/components/schemas/ErrorResponse'),
        ]
    )]
    public function resetPassword(Request $request): JsonResponse
    {
        $data = [];
        try {
            $data = $this->authService->resetPassword($request);
            return Response::Success($data['info'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }
}
