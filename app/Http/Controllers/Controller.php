<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    description: 'AqarGo real estate platform API documentation',
    title: 'AqarGo API',
    contact: new OA\Contact(email: 'support@aqargo.com')
)]
#[OA\Server(url: L5_SWAGGER_CONST_HOST, description: 'Local server')]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT'
)]
#[OA\Schema(
    schema: 'SuccessResponse',
    properties: [
        new OA\Property(property: 'status', type: 'integer', example: 1),
        new OA\Property(property: 'data', type: 'object'),
        new OA\Property(property: 'message', type: 'string'),
    ]
)]
#[OA\Schema(
    schema: 'ErrorResponse',
    properties: [
        new OA\Property(property: 'status', type: 'integer', example: 0),
        new OA\Property(property: 'data', type: 'object'),
        new OA\Property(property: 'message', type: 'string'),
    ]
)]
#[OA\Schema(
    schema: 'ValidationErrorResponse',
    properties: [
        new OA\Property(property: 'status', type: 'integer', example: 0),
        new OA\Property(property: 'data', type: 'object'),
        new OA\Property(property: 'message', type: 'string', example: 'Validation Error.'),
    ]
)]
abstract class Controller
{
    //
}
