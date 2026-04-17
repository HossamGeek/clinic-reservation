<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    #[OA\Post(
        path: '/api/register',
        operationId: 'registerUser',
        tags: ['Auth'],
        summary: 'Register a new patient or doctor',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['full_name', 'email', 'password', 'password_confirmation', 'role'],
                properties: [
                    new OA\Property(property: 'full_name', type: 'string', example: 'Ahmed Hassan'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'ahmed@example.com'),
                    new OA\Property(property: 'phone', type: 'string', nullable: true, example: '01012345678'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'secret123'),
                    new OA\Property(property: 'password_confirmation', type: 'string', format: 'password', example: 'secret123'),
                    new OA\Property(property: 'role', type: 'string', enum: ['PATIENT', 'DOCTOR'], example: 'PATIENT'),
                    new OA\Property(property: 'gender', type: 'string', nullable: true, enum: ['MALE', 'FEMALE'], example: 'MALE'),
                    new OA\Property(property: 'date_of_birth', type: 'string', format: 'date', nullable: true, example: '1999-01-15'),
                    new OA\Property(property: 'address', type: 'string', nullable: true, example: 'Cairo'),
                    new OA\Property(property: 'medical_history', type: 'string', nullable: true, example: 'No chronic diseases'),
                    new OA\Property(property: 'specialty', type: 'string', nullable: true, example: 'Dermatology'),
                    new OA\Property(property: 'available_time', type: 'string', nullable: true, example: '5PM - 9PM'),
                    new OA\Property(property: 'bio', type: 'string', nullable: true, example: 'Senior dermatologist'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'User registered successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'User registered successfully'),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(
                                    property: 'user',
                                    properties: [
                                        new OA\Property(property: 'user_id', type: 'integer', example: 10),
                                        new OA\Property(property: 'full_name', type: 'string', example: 'Ahmed Hassan'),
                                        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'ahmed@example.com'),
                                        new OA\Property(property: 'phone', type: 'string', nullable: true, example: '01012345678'),
                                        new OA\Property(property: 'role', type: 'string', example: 'PATIENT'),
                                        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                                    ],
                                    type: 'object'
                                ),
                                new OA\Property(property: 'profile', type: 'object')
                            ],
                            type: 'object'
                        ),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 422, description: 'Validation error')
        ]
    )]
    public function register(RegisterRequest $request): JsonResponse
    {
        $payload = $request->validated();

        $result = DB::transaction(function () use ($payload) {
            $user = User::create([
                'full_name' => $payload['full_name'],
                'email' => $payload['email'],
                'phone' => $payload['phone'] ?? null,
                'password_hash' => Hash::make($payload['password']),
                'role' => $payload['role'],
                'created_at' => now(),
            ]);

            if ($payload['role'] === 'PATIENT') {
                $profile = Patient::create([
                    'user_id' => $user->user_id,
                    'gender' => $payload['gender'] ?? null,
                    'date_of_birth' => $payload['date_of_birth'] ?? null,
                    'address' => $payload['address'] ?? null,
                    'medical_history' => $payload['medical_history'] ?? null,
                    'created_at' => now(),
                ]);
            } else {
                $profile = Doctor::create([
                    'user_id' => $user->user_id,
                    'specialty' => $payload['specialty'],
                    'rating' => 0.0,
                    'available_time' => $payload['available_time'] ?? null,
                    'bio' => $payload['bio'] ?? null,
                    'created_at' => now(),
                ]);
            }

            return [
                'user' => $user,
                'profile' => $profile,
            ];
        });

        return response()->json([
            'message' => 'User registered successfully',
            'data' => $result,
        ], 201);
    }
}
