<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateReportRequest;
use App\Http\Responses\Response;
use App\Services\ReportService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Throwable;

class ReportController extends Controller
{
    protected ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    #[OA\Post(
        path: '/api/report/index',
        summary: 'List all reports (admin)',
        security: [['bearerAuth' => []]],
        tags: ['Reports'],
        responses: [
            new OA\Response(response: 200, description: 'Reports retrieved', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 500, description: 'Server error', ref: '#/components/schemas/ErrorResponse'),
        ]
    )]
    public function index(Request $request)
    {
        $data = [];
        try {
            $data = $this->reportService->index($request);
            return Response::Success($data['reports'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Post(
        path: '/api/report/create',
        summary: 'Submit a report against an ad',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['ad_id'],
                properties: [
                    new OA\Property(property: 'ad_id', type: 'integer', example: 1),
                    new OA\Property(
                        property: 'reason',
                        type: 'string',
                        enum: ['sexual_content', 'harassment', 'spam', 'hate_speech', 'violence', 'scam', 'fake_information', 'other'],
                        example: 'spam'
                    ),
                    new OA\Property(property: 'description', type: 'string', example: 'This ad seems fraudulent'),
                ]
            )
        ),
        tags: ['Reports'],
        responses: [
            new OA\Response(response: 201, description: 'Report submitted', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 422, description: 'Validation error', ref: '#/components/schemas/ValidationErrorResponse'),
        ]
    )]
    public function create(CreateReportRequest $request)
    {
        $data = [];
        try {
            $data = $this->reportService->create($request->validated());
            return Response::Success($data['report'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Get(
        path: '/api/report/show/{id}',
        summary: 'Get a report by ID',
        security: [['bearerAuth' => []]],
        tags: ['Reports'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Report retrieved', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 404, description: 'Not found', ref: '#/components/schemas/ErrorResponse'),
        ]
    )]
    public function show($id)
    {
        $data = [];
        try {
            $data = $this->reportService->show($id);
            return Response::Success($data['report'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Post(
        path: '/api/report/showAdReports',
        summary: 'Get all reports for a specific ad',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'ad_id', type: 'integer', example: 1),
                ]
            )
        ),
        tags: ['Reports'],
        responses: [
            new OA\Response(response: 200, description: 'Reports retrieved', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 500, description: 'Server error', ref: '#/components/schemas/ErrorResponse'),
        ]
    )]
    public function showAdReports(Request $request)
    {
        $data = [];
        try {
            $data = $this->reportService->showAdReports($request);
            return Response::Success($data['reports'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Delete(
        path: '/api/report/delete/{id}',
        summary: 'Delete a report (admin)',
        security: [['bearerAuth' => []]],
        tags: ['Reports'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Report deleted', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 500, description: 'Server error', ref: '#/components/schemas/ErrorResponse'),
        ]
    )]
    public function delete($id)
    {
        $data = [];
        try {
            $data = $this->reportService->delete($id);
            return Response::Success($data['report'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }
}
