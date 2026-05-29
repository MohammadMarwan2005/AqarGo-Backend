<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateSubscriptionRequest;
use App\Http\Responses\Response;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Throwable;

class SubscriptionController extends Controller
{
    protected SubscriptionService $service;

    public function __construct(SubscriptionService $service)
    {
        $this->service = $service;
    }

    #[OA\Get(
        path: '/api/subscriptions/activated/admin',
        summary: 'Get all active subscriptions (admin)',
        security: [['bearerAuth' => []]],
        tags: ['Subscriptions'],
        responses: [
            new OA\Response(response: 200, description: 'Active subscriptions retrieved', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 500, description: 'Server error', ref: '#/components/schemas/ErrorResponse'),
        ]
    )]
    public function allActiveSub()
    {
        $data = [];
        try {
            $data = $this->service->allActiveSub();
            return Response::Success($data['subscription'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Put(
        path: '/api/subscriptions/deactivate/{id}/admin',
        summary: 'Deactivate a subscription (admin)',
        security: [['bearerAuth' => []]],
        tags: ['Subscriptions'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Subscription deactivated', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 500, description: 'Server error', ref: '#/components/schemas/ErrorResponse'),
        ]
    )]
    public function deactivate($sub_id)
    {
        $data = [];
        try {
            $data = $this->service->deactivate($sub_id);
            return Response::Success($data['subscription'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Get(
        path: '/api/subscriptions/admin',
        summary: 'List all subscriptions (admin)',
        security: [['bearerAuth' => []]],
        tags: ['Subscriptions'],
        responses: [
            new OA\Response(response: 200, description: 'Subscriptions retrieved', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 500, description: 'Server error', ref: '#/components/schemas/ErrorResponse'),
        ]
    )]
    public function index()
    {
        $data = [];
        try {
            $data = $this->service->index();
            return Response::Success($data['subscription'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Get(
        path: '/api/subscriptions/{id}/admin',
        summary: 'Get a subscription by ID (admin)',
        security: [['bearerAuth' => []]],
        tags: ['Subscriptions'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Subscription retrieved', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 404, description: 'Not found', ref: '#/components/schemas/ErrorResponse'),
        ]
    )]
    public function show($sub_id)
    {
        $data = [];
        try {
            $data = $this->service->show($sub_id);
            return Response::Success($data['subscription'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Delete(
        path: '/api/subscriptions/{id}/admin',
        summary: 'Delete a subscription (admin)',
        security: [['bearerAuth' => []]],
        tags: ['Subscriptions'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Subscription deleted', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 500, description: 'Server error', ref: '#/components/schemas/ErrorResponse'),
        ]
    )]
    public function destroy($sub_id)
    {
        $data = [];
        try {
            $data = $this->service->destroy($sub_id);
            return Response::Success($data['subscription'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Get(
        path: '/api/subscriptions/active/client',
        summary: "Get the authenticated user's active subscription",
        security: [['bearerAuth' => []]],
        tags: ['Subscriptions'],
        responses: [
            new OA\Response(response: 200, description: 'Active subscription retrieved', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 500, description: 'Server error', ref: '#/components/schemas/ErrorResponse'),
        ]
    )]
    public function userActiveSub()
    {
        $data = [];
        try {
            $data = $this->service->userActiveSub();
            return Response::Success($data['subscription'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Put(
        path: '/api/subscriptions/deactivate/client',
        summary: "Deactivate the authenticated user's subscription",
        security: [['bearerAuth' => []]],
        tags: ['Subscriptions'],
        responses: [
            new OA\Response(response: 200, description: 'Subscription deactivated', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 500, description: 'Server error', ref: '#/components/schemas/ErrorResponse'),
        ]
    )]
    public function userDeactivate()
    {
        $data = [];
        try {
            $data = $this->service->userDeactivate();
            return Response::Success($data['subscription'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Get(
        path: '/api/subscriptions/client',
        summary: "List the authenticated user's subscriptions",
        security: [['bearerAuth' => []]],
        tags: ['Subscriptions'],
        responses: [
            new OA\Response(response: 200, description: 'Subscriptions retrieved', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 500, description: 'Server error', ref: '#/components/schemas/ErrorResponse'),
        ]
    )]
    public function userIndex()
    {
        $data = [];
        try {
            $data = $this->service->userIndex();
            return Response::Success($data['subscriptions'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Get(
        path: '/api/subscriptions/{id}/client',
        summary: "Get a specific subscription of the authenticated user",
        security: [['bearerAuth' => []]],
        tags: ['Subscriptions'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Subscription retrieved', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 404, description: 'Not found', ref: '#/components/schemas/ErrorResponse'),
        ]
    )]
    public function userShow($sub_id)
    {
        $data = [];
        try {
            $data = $this->service->userShow($sub_id);
            return Response::Success($data['subscription'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Post(
        path: '/api/subscriptions/client',
        summary: 'Subscribe to a plan',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['plan_id'],
                properties: [
                    new OA\Property(property: 'plan_id', type: 'integer', example: 1),
                ]
            )
        ),
        tags: ['Subscriptions'],
        responses: [
            new OA\Response(response: 201, description: 'Subscribed successfully', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 422, description: 'Validation error', ref: '#/components/schemas/ValidationErrorResponse'),
        ]
    )]
    public function userCreate(CreateSubscriptionRequest $req)
    {
        $data = [];
        try {
            $data = $this->service->userCreate($req->validated());
            return Response::Success($data['subscription'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Get(
        path: '/api/subscriptions/time_remaining/{id}/client',
        summary: 'Get remaining time for a subscription',
        security: [['bearerAuth' => []]],
        tags: ['Subscriptions'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Remaining time retrieved', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 500, description: 'Server error', ref: '#/components/schemas/ErrorResponse'),
        ]
    )]
    public function time_remaining($sub_id)
    {
        $data = [];
        try {
            $data = $this->service->time_remaining($sub_id);
            return Response::Success($data['time'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }
}
