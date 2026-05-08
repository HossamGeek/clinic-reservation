<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Reservation;
use App\Support\BookingSlots;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

class DoctorController extends Controller
{
    #[OA\Get(
        path: '/api/doctors',
        operationId: 'listDoctors',
        tags: ['Doctors'],
        summary: 'List doctors available for booking',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Doctors retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Doctors retrieved successfully.'),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(
                                    property: 'doctors',
                                    type: 'array',
                                    items: new OA\Items(type: 'object')
                                ),
                            ],
                            type: 'object'
                        ),
                    ],
                    type: 'object'
                )
            ),
        ]
    )]
    public function index(): JsonResponse
    {
        $doctors = Doctor::query()
            ->orderBy('name')
            ->orderBy('specialty')
            ->get()
            ->map(fn (Doctor $doctor): array => $this->formatDoctor($doctor))
            ->values();

        return response()->json([
            'success' => true,
            'message' => 'Doctors retrieved successfully.',
            'data' => [
                'doctors' => $doctors,
            ],
        ]);
    }

    #[OA\Get(
        path: '/api/doctors/{id}',
        operationId: 'showDoctor',
        tags: ['Doctors'],
        summary: 'Show doctor details',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
                example: 1
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Doctor retrieved successfully'),
            new OA\Response(response: 404, description: 'Doctor not found'),
        ]
    )]
    public function show(int $id): JsonResponse
    {
        $doctor = Doctor::query()->find($id);

        if (! $doctor) {
            return response()->json([
                'success' => false,
                'message' => 'Doctor not found.',
                'data' => [],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Doctor retrieved successfully.',
            'data' => [
                'doctor' => $this->formatDoctor($doctor),
            ],
        ]);
    }

    #[OA\Get(
        path: '/api/doctors/{id}/available-slots',
        operationId: 'doctorAvailableSlots',
        tags: ['Doctors'],
        summary: 'Get available appointment slots for a doctor and date',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
                example: 1
            ),
            new OA\Parameter(
                name: 'date',
                in: 'query',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'date'),
                example: '2026-05-13'
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Available slots retrieved successfully'),
            new OA\Response(response: 404, description: 'Doctor not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function availableSlots(Request $request, int $id): JsonResponse
    {
        $doctor = Doctor::query()->find($id);

        if (! $doctor) {
            return response()->json([
                'success' => false,
                'message' => 'Doctor not found.',
                'data' => [],
            ], 404);
        }

        $validator = Validator::make($request->query(), [
            'date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Please select a valid appointment date.',
                'data' => [
                    'errors' => $validator->errors(),
                ],
            ], 422);
        }

        $date = $validator->validated()['date'];
        $reservedSlots = Reservation::query()
            ->where('doctor_id', $doctor->id)
            ->whereDate('reservation_date', $date)
            ->where('status', '!=', 'cancelled')
            ->pluck('time_slot')
            ->all();

        return response()->json([
            'success' => true,
            'message' => 'Available slots retrieved successfully.',
            'data' => [
                'doctor' => $this->formatDoctor($doctor),
                'date' => $date,
                'slot_groups' => BookingSlots::groupedWithAvailability($reservedSlots),
            ],
        ]);
    }

    private function formatDoctor(Doctor $doctor): array
    {
        return [
            'id' => $doctor->id,
            'doctor_id' => $doctor->doctor_id,
            'name' => $doctor->name ?: optional($doctor->user)->full_name ?: 'Doctor '.$doctor->id,
            'specialty' => $doctor->specialty,
            'bio' => $doctor->bio,
            'rating' => $doctor->rating,
            'available_time' => $doctor->available_time,
            'location' => $doctor->location,
            'image' => $doctor->image,
            'consultation_fee' => (float) ($doctor->consultation_fee ?? 150),
        ];
    }
}
