<?php

namespace App\Http\Controllers;

use App\Http\Requests\Plan\CreatePlanRequest;
use App\Http\Requests\Plan\UpdatePlanRequest;
use App\Http\Responses\Response;
use App\Services\PlanService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Throwable;

class PlanController extends Controller
{
    protected PlanService $service;

    public function __construct(PlanService $service)
    {
        $this->service = $service;
    }

    #[OA\Get(
        path: '/api/plans',
        summary: 'List all plans',
        security: [['bearerAuth' => []]],
        tags: ['Plans'],
        responses: [
            new OA\Response(response: 200, description: 'Plans retrieved', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 500, description: 'Server error', ref: '#/components/schemas/ErrorResponse'),
        ]
    )]
    public function index()
    {
        $data = [];
        try {
            $data = $this->service->index();
            return Response::Success($data['plan'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Get(
        path: '/api/plans/{id}',
        summary: 'Get a single plan',
        security: [['bearerAuth' => []]],
        tags: ['Plans'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Plan retrieved', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 404, description: 'Not found', ref: '#/components/schemas/ErrorResponse'),
        ]
    )]
    public function show(string $id)
    {
        $data = [];
        try {
            $data = $this->service->show($id);
            return Response::Success($data['plan'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Delete(
        path: '/api/plans/{id}',
        summary: 'Delete a plan (admin)',
        security: [['bearerAuth' => []]],
        tags: ['Plans'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Plan deleted', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 500, description: 'Server error', ref: '#/components/schemas/ErrorResponse'),
        ]
    )]
    public function destroy(string $id)
    {
        $data = [];
        try {
            $data = $this->service->destroy($id);
            return Response::Success($data['plan'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Post(
        path: '/api/plans',
        summary: 'Create a new plan (admin)',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'features', 'duration', 'type', 'price'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Gold Plan'),
                    new OA\Property(property: 'features', type: 'string', example: 'Unlimited ads, priority listing'),
                    new OA\Property(property: 'duration', type: 'integer', example: 30),
                    new OA\Property(property: 'type', type: 'string', enum: ['monthly', 'yearly']),
                    new OA\Property(property: 'price', type: 'number', example: 9.99),
                ]
            )
        ),
        tags: ['Plans'],
        responses: [
            new OA\Response(response: 201, description: 'Plan created', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 422, description: 'Validation error', ref: '#/components/schemas/ValidationErrorResponse'),
        ]
    )]
    public function store(CreatePlanRequest $request)
    {
        $data = [];
        try {
            $data = $this->service->store($request->validated());
            return Response::Success($data['plan'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Put(
        path: '/api/plans/{id}',
        summary: 'Update a plan (admin)',
        security: [['bearerAuth' => []]],
        tags: ['Plans'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Gold Plan'),
                    new OA\Property(property: 'features', type: 'string', example: 'Unlimited ads'),
                    new OA\Property(property: 'duration', type: 'integer', example: 30),
                    new OA\Property(property: 'type', type: 'string', enum: ['monthly', 'yearly']),
                    new OA\Property(property: 'price', type: 'number', example: 9.99),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Plan updated', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 422, description: 'Validation error', ref: '#/components/schemas/ValidationErrorResponse'),
        ]
    )]
    public function update(UpdatePlanRequest $request, string $id)
    {
        $data = [];
        try {
            $data = $this->service->update($request->validated(), $id);
            return Response::Success($data['plan'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Get(
        path: '/api/plans/yearly_plans',
        summary: 'Get all yearly plans',
        security: [['bearerAuth' => []]],
        tags: ['Plans'],
        responses: [
            new OA\Response(response: 200, description: 'Yearly plans retrieved', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 500, description: 'Server error', ref: '#/components/schemas/ErrorResponse'),
        ]
    )]
    public function getYearlyPlans()
    {
        $data = [];
        try {
            $data = $this->service->getYearlyPlans();
            return Response::Success($data['plan'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Get(
        path: '/api/plans/monthly_plans',
        summary: 'Get all monthly plans',
        security: [['bearerAuth' => []]],
        tags: ['Plans'],
        responses: [
            new OA\Response(response: 200, description: 'Monthly plans retrieved', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 500, description: 'Server error', ref: '#/components/schemas/ErrorResponse'),
        ]
    )]
    public function getMonthlyPlans()
    {
        $data = [];
        try {
            $data = $this->service->getMonthlyPlans();
            return Response::Success($data['plan'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }
}
