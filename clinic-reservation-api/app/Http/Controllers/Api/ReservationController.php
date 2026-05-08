<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Reservation;
use App\Support\BookingSlots;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;
use Throwable;

class ReservationController extends Controller
{
    #[OA\Post(
        path: '/api/reservations',
        operationId: 'createReservation',
        tags: ['Reservations'],
        summary: 'Book an appointment reservation',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['doctor_id', 'reservation_date', 'time_slot', 'patient_name', 'patient_email', 'patient_phone'],
                properties: [
                    new OA\Property(property: 'doctor_id', type: 'integer', example: 1),
                    new OA\Property(property: 'reservation_date', type: 'string', format: 'date', example: '2026-05-13'),
                    new OA\Property(property: 'time_slot', type: 'string', example: '10:00 AM'),
                    new OA\Property(property: 'patient_name', type: 'string', example: 'Mona Ahmed'),
                    new OA\Property(property: 'patient_email', type: 'string', format: 'email', example: 'mona@example.com'),
                    new OA\Property(property: 'patient_phone', type: 'string', example: '01012345678'),
                    new OA\Property(property: 'notes', type: 'string', nullable: true, example: 'First visit'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Reservation created successfully'),
            new OA\Response(response: 409, description: 'Selected time slot is unavailable'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'doctor_id' => ['required', 'integer', 'exists:doctors,id'],
            'reservation_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'time_slot' => ['required', 'string', 'max:20'],
            'patient_name' => ['required', 'string', 'max:150'],
            'patient_email' => ['required', 'email', 'max:150'],
            'patient_phone' => ['required', 'string', 'max:30'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Please complete the required booking details.',
                'data' => [
                    'errors' => $validator->errors(),
                ],
            ], 422);
        }

        $payload = $validator->validated();

        if (! BookingSlots::isKnownSlot($payload['time_slot'])) {
            return response()->json([
                'success' => false,
                'message' => 'Selected time slot is invalid.',
                'data' => [],
            ], 422);
        }

        if (BookingSlots::isBlockedSlot($payload['time_slot'])) {
            return response()->json([
                'success' => false,
                'message' => 'Selected time slot is unavailable.',
                'data' => [],
            ], 409);
        }

        $doctor = Doctor::query()->findOrFail($payload['doctor_id']);
        $hasActiveReservation = Reservation::query()
            ->where('doctor_id', $doctor->id)
            ->whereDate('reservation_date', $payload['reservation_date'])
            ->where('time_slot', $payload['time_slot'])
            ->where('status', '!=', 'cancelled')
            ->exists();

        if ($hasActiveReservation) {
            return response()->json([
                'success' => false,
                'message' => 'Selected time slot is already booked. Please choose another slot.',
                'data' => [],
            ], 409);
        }

        $reservation = DB::transaction(function () use ($payload, $doctor): Reservation {
            return Reservation::query()->create([
                'reservation_code' => $this->generateReservationCode($payload['reservation_date']),
                'patient_id' => $this->resolvePatientId(),
                'doctor_id' => $doctor->id,
                'reservation_date' => $payload['reservation_date'],
                'time_slot' => $payload['time_slot'],
                'status' => 'confirmed',
                'patient_name' => $payload['patient_name'],
                'patient_email' => $payload['patient_email'],
                'patient_phone' => $payload['patient_phone'],
                'notes' => $payload['notes'] ?? null,
                'consultation_fee' => $doctor->consultation_fee ?? 150,
                'processing_fee' => 5,
                'location' => $doctor->location,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Appointment confirmed successfully.',
            'data' => [
                'reservation' => $this->formatReservation($reservation->fresh('doctor')),
            ],
        ], 201);
    }

    private function resolvePatientId(): ?int
    {
        try {
            $user = auth('api')->user();
        } catch (Throwable) {
            return null;
        }

        if (! $user || $user->role !== 'PATIENT') {
            return null;
        }

        return optional($user->patient)->patient_id;
    }

    private function generateReservationCode(string $date): string
    {
        $prefix = 'RES-'.Carbon::parse($date)->format('Ymd').'-';

        do {
            $code = $prefix.random_int(1000, 9999);
        } while (Reservation::query()->where('reservation_code', $code)->exists());

        return $code;
    }

    private function formatReservation(Reservation $reservation): array
    {
        $consultationFee = (float) ($reservation->consultation_fee ?? 0);
        $processingFee = (float) ($reservation->processing_fee ?? 0);

        return [
            'id' => $reservation->id,
            'reservation_id' => $reservation->reservation_id,
            'reservation_code' => $reservation->reservation_code,
            'patient_id' => $reservation->patient_id,
            'patient_name' => $reservation->patient_name,
            'patient_email' => $reservation->patient_email,
            'patient_phone' => $reservation->patient_phone,
            'doctor_id' => $reservation->doctor_id,
            'doctor_name' => optional($reservation->doctor)->name ?: optional(optional($reservation->doctor)->user)->full_name,
            'reservation_date' => optional($reservation->reservation_date)->format('Y-m-d'),
            'time_slot' => $reservation->time_slot,
            'status' => $reservation->status,
            'notes' => $reservation->notes,
            'location' => $reservation->location,
            'consultation_fee' => $consultationFee,
            'processing_fee' => $processingFee,
            'total_estimated' => $consultationFee + $processingFee,
        ];
    }
}
