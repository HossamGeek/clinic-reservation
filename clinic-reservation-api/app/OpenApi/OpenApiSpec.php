<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Clinic Reservation API',
    description: 'API documentation for the clinic reservation system'
)]
#[OA\Server(
    url: 'http://127.0.0.1:8000',
    description: 'Local server'
)]
class OpenApiSpec
{
}
