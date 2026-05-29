<?php

namespace App\Http\Controllers;

use App\Http\Requests\Property\CreatePropertyRequest;
use App\Http\Requests\Property\UpdatePropertyRequest;
use App\Http\Responses\Response;
use App\Services\Property\PropertyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Throwable;

class PropertyController extends Controller
{
    protected PropertyService $propertyService;

    public function __construct(PropertyService $propertyService)
    {
        $this->propertyService = $propertyService;
    }

    #[OA\Get(
        path: '/api/property/getProperty/{id}',
        summary: 'Get a single property by ID',
        security: [['bearerAuth' => []]],
        tags: ['Property'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Property retrieved', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 404, description: 'Not found', ref: '#/components/schemas/ErrorResponse'),
        ]
    )]
    public function getProperty($id)
    {
        $data = [];
        try {
            $data = $this->propertyService->getProperty($id);
            return Response::Success($data['property'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Get(
        path: '/api/property/getUserProperties',
        summary: "Get the authenticated user's properties",
        security: [['bearerAuth' => []]],
        tags: ['Property'],
        responses: [
            new OA\Response(response: 200, description: 'Properties retrieved', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 500, description: 'Server error', ref: '#/components/schemas/ErrorResponse'),
        ]
    )]
    public function getUserProperties(Request $request): JsonResponse
    {
        $data = [];
        try {
            $data = $this->propertyService->getUserProperties($request);
            return Response::Success($data['properties'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Post(
        path: '/api/property/create',
        summary: 'Create a new property',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['type'],
                properties: [
                    new OA\Property(property: 'type', type: 'string', enum: ['apartment', 'land', 'office', 'shop']),
                    new OA\Property(
                        property: 'property',
                        type: 'object',
                        properties: [
                            new OA\Property(property: 'area', type: 'number', example: 120),
                            new OA\Property(property: 'price', type: 'number', example: 150000),
                            new OA\Property(property: 'description', type: 'string'),
                        ]
                    ),
                ]
            )
        ),
        tags: ['Property'],
        responses: [
            new OA\Response(response: 201, description: 'Property created', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 422, description: 'Validation error', ref: '#/components/schemas/ValidationErrorResponse'),
        ]
    )]
    public function create(CreatePropertyRequest $request): JsonResponse
    {
        $data = [];
        try {
            $data = $this->propertyService->create($request->validated());
            return Response::Success($data['property'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Post(
        path: '/api/property/update/{id}',
        summary: 'Update a property',
        security: [['bearerAuth' => []]],
        tags: ['Property'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'type', type: 'string', enum: ['apartment', 'land', 'office', 'shop']),
                    new OA\Property(
                        property: 'property',
                        type: 'object',
                        properties: [
                            new OA\Property(property: 'area', type: 'number', example: 120),
                            new OA\Property(property: 'price', type: 'number', example: 150000),
                        ]
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Property updated', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 422, description: 'Validation error', ref: '#/components/schemas/ValidationErrorResponse'),
        ]
    )]
    public function update(UpdatePropertyRequest $request, $id): JsonResponse
    {
        $data = [];
        try {
            $data = $this->propertyService->update($request->validated(), $id);
            return Response::Success($data['property'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Delete(
        path: '/api/property/delete/{id}',
        summary: 'Delete a property',
        security: [['bearerAuth' => []]],
        tags: ['Property'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Property deleted', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 500, description: 'Server error', ref: '#/components/schemas/ErrorResponse'),
        ]
    )]
    public function delete($id): JsonResponse
    {
        $data = [];
        try {
            $data = $this->propertyService->delete($id);
            return Response::Success($data['property'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Post(
        path: '/api/property/getAttributes',
        summary: 'Get available attributes for a property type',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'type', type: 'string', enum: ['apartment', 'land', 'office', 'shop']),
                ]
            )
        ),
        tags: ['Property'],
        responses: [
            new OA\Response(response: 200, description: 'Attributes retrieved', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 500, description: 'Server error', ref: '#/components/schemas/ErrorResponse'),
        ]
    )]
    public function getAttributes(Request $request): JsonResponse
    {
        $data = [];
        try {
            $data = $this->propertyService->getAttributes($request);
            return Response::Success($data['attributes'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }
}
