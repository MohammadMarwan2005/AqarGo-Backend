<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateBlockRequest;
use App\Http\Responses\Response;
use App\Services\BlockService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Throwable;

class BlockController extends Controller
{
    protected BlockService $blockservice;

    public function __construct(BlockService $blockservice)
    {
        $this->blockservice = $blockservice;
    }

    #[OA\Post(
        path: '/api/block/create',
        summary: 'Block a user',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['blocked_id'],
                properties: [
                    new OA\Property(property: 'blocked_id', type: 'integer', example: 5),
                    new OA\Property(property: 'reason', type: 'string', example: 'Spam'),
                    new OA\Property(property: 'days', type: 'integer', example: 7),
                ]
            )
        ),
        tags: ['Block'],
        responses: [
            new OA\Response(response: 200, description: 'User blocked', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 422, description: 'Validation error', ref: '#/components/schemas/ValidationErrorResponse'),
        ]
    )]
    public function block(CreateBlockRequest $request)
    {
        $data = [];
        try {
            $data = $this->blockservice->block($request->validated());
            return Response::Success($data['block'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Delete(
        path: '/api/block/unblock/{id}',
        summary: 'Unblock a user',
        security: [['bearerAuth' => []]],
        tags: ['Block'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Block ID', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'User unblocked', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 500, description: 'Server error', ref: '#/components/schemas/ErrorResponse'),
        ]
    )]
    public function unblock($id)
    {
        $data = [];
        try {
            $data = $this->blockservice->unblock($id);
            return Response::Success($data['block'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Get(
        path: '/api/block/index',
        summary: "List the authenticated user's blocks",
        security: [['bearerAuth' => []]],
        tags: ['Block'],
        responses: [
            new OA\Response(response: 200, description: 'Blocks retrieved', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 500, description: 'Server error', ref: '#/components/schemas/ErrorResponse'),
        ]
    )]
    public function index()
    {
        $data = [];
        try {
            $data = $this->blockservice->index();
            return Response::Success($data['blocks'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }
}
