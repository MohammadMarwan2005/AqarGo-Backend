<?php

namespace App\Http\Controllers;

use App\Http\Responses\Response;
use App\Services\FcmService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Throwable;

class FcmController extends Controller
{
    protected FcmService $service;

    public function __construct(FcmService $service)
    {
        $this->service = $service;
    }

    #[OA\Post(
        path: '/api/fcm/send',
        summary: 'Send a push notification to a single device',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'device_token', type: 'string', example: 'fcm_device_token'),
                    new OA\Property(property: 'title', type: 'string', example: 'New message'),
                    new OA\Property(property: 'body', type: 'string', example: 'You have a new notification'),
                    new OA\Property(property: 'ad_id', type: 'integer', example: 1),
                    new OA\Property(property: 'data', type: 'object'),
                ]
            )
        ),
        tags: ['Notifications'],
        responses: [
            new OA\Response(response: 200, description: 'Notification sent', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 500, description: 'Failed to send', ref: '#/components/schemas/ErrorResponse'),
        ]
    )]
    public function sendNotification(Request $request)
    {
        $data = [];
        try {
            $deviceToken = $request->input('device_token');
            $title = $request->input('title');
            $body = $request->input('body');
            $extraData = $request->input('data', []);
            $ad_id = $request->input('ad_id');

            $success = $this->service->sendNotification($deviceToken, $title, $body, $extraData, $ad_id);

            return $success
                ? Response::Success([], 'Notification sent successfully', 200)
                : Response::Error([], 'Failed to send notification');
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Post(
        path: '/api/fcm/send-all',
        summary: 'Send a push notification to all users',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'title', type: 'string', example: 'Announcement'),
                    new OA\Property(property: 'body', type: 'string', example: 'Broadcast message'),
                    new OA\Property(property: 'ad_id', type: 'integer', example: 1),
                    new OA\Property(property: 'data', type: 'object'),
                ]
            )
        ),
        tags: ['Notifications'],
        responses: [
            new OA\Response(response: 200, description: 'Notifications sent', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 500, description: 'Server error', ref: '#/components/schemas/ErrorResponse'),
        ]
    )]
    public function sendNotificationToAll(Request $request)
    {
        $data = [];
        try {
            $this->service->sendNotificationToAll(
                $request->input('title'),
                $request->input('body'),
                $request->input('data', []),
                $request->input('ad_id')
            );
            return Response::Success([], 'Notifications sent to all users', 200);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Get(
        path: '/api/fcm/notifications',
        summary: 'Get all notifications for the authenticated user',
        security: [['bearerAuth' => []]],
        tags: ['Notifications'],
        responses: [
            new OA\Response(response: 200, description: 'Notifications retrieved', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 500, description: 'Server error', ref: '#/components/schemas/ErrorResponse'),
        ]
    )]
    public function index(Request $request)
    {
        $data = [];
        try {
            $data = $this->service->index($request);
            return Response::Success($data['notifications'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Get(
        path: '/api/fcm/notifications/read',
        summary: 'Get read notifications',
        security: [['bearerAuth' => []]],
        tags: ['Notifications'],
        responses: [
            new OA\Response(response: 200, description: 'Read notifications retrieved', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 500, description: 'Server error', ref: '#/components/schemas/ErrorResponse'),
        ]
    )]
    public function readIndex(Request $req)
    {
        $data = [];
        try {
            $data = $this->service->readIndex($req);
            return Response::Success($data['notifications'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Get(
        path: '/api/fcm/notifications/unread',
        summary: 'Get unread notifications',
        security: [['bearerAuth' => []]],
        tags: ['Notifications'],
        responses: [
            new OA\Response(response: 200, description: 'Unread notifications retrieved', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 500, description: 'Server error', ref: '#/components/schemas/ErrorResponse'),
        ]
    )]
    public function unreadIndex(Request $req)
    {
        $data = [];
        try {
            $data = $this->service->unreadIndex($req);
            return Response::Success($data['notifications'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }

    #[OA\Get(
        path: '/api/fcm/notifications/unread-count',
        summary: 'Get count of unread notifications',
        security: [['bearerAuth' => []]],
        tags: ['Notifications'],
        responses: [
            new OA\Response(response: 200, description: 'Unread count returned', ref: '#/components/schemas/SuccessResponse'),
            new OA\Response(response: 500, description: 'Server error', ref: '#/components/schemas/ErrorResponse'),
        ]
    )]
    public function unreadCount()
    {
        $data = [];
        try {
            $count = $this->service->getUnreadCount();
            return Response::Success(['unread_count' => $count], 'Unread notifications count', 200);
        } catch (Throwable $th) {
            return Response::Error($data, $th->getMessage());
        }
    }
}
