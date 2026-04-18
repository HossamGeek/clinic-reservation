<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Clinic Reservation API',
    description: 'API documentation for the clinic reservation system'
)]
#[OA\Server(
    url: 'http://clinic-reservation-api.test',
    description: 'Local server'
)]
class OpenApiSpec
{
}
