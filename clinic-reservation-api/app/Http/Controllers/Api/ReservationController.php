<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReservationConsultationResource;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Reservation;
use App\Support\BookingSlots;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;
use Throwable;

class ReservationController extends Controller
{
    public function activeConsultation(Request $request): JsonResponse
    {
        $patientId = $request->integer('patient_id') ?: null;
        $doctorId = $request->integer('doctor_id') ?: null;
        $patient = $patientId ? $this->patientForId($patientId) : null;
        $doctor = $doctorId ? $this->doctorForId($doctorId) : null;

        if ($patientId && ! $patient) {
            return response()->json([
                'success' => false,
                'message' => 'Patient profile was not found.',
                'data' => [],
            ], 404);
        }

        if ($doctorId && ! $doctor) {
            return response()->json([
                'success' => false,
                'message' => 'Doctor profile was not found.',
                'data' => [],
            ], 404);
        }

        $reservation = $this->consultationQuery()
            ->when($patient, fn ($query, Patient $patient) => $query->where('patient_id', $patient->patient_id))
            ->when($doctor, fn ($query, Doctor $doctor) => $query->where('doctor_id', $doctor->doctor_id))
            ->when($this->reservationHasColumn('status'), function ($query): void {
                $query->where(function ($statusQuery): void {
                    $statusQuery->whereIn('status', ['in_progress', 'confirmed', 'Confirmed', 'pending', 'Pending'])
                        ->orWhereNull('status');
                });
            })
            ->when($this->reservationHasColumn('status'), fn ($query) => $query->orderByRaw("case when status = 'in_progress' then 0 else 1 end"))
            ->orderBy(Reservation::dateColumn())
            ->orderBy('time_slot')
            ->first();

        if (! $reservation) {
            $reservation = $this->ensureActiveConsultation($patient, $doctor);
        }

        return response()->json([
            'success' => true,
            'message' => 'Active consultation retrieved successfully.',
            'data' => [
                'consultation' => new ReservationConsultationResource($reservation->loadMissing(['doctor.user', 'patient.user', 'patient.reservations'])),
            ],
        ]);
    }

    public function createActiveConsultation(Request $request): JsonResponse
    {
        $patientId = $request->integer('patient_id') ?: null;
        $doctorId = $request->integer('doctor_id') ?: null;
        $patient = $patientId ? $this->patientForId($patientId) : null;
        $doctor = $doctorId ? $this->doctorForId($doctorId) : null;

        if ($patientId && ! $patient) {
            return response()->json([
                'success' => false,
                'message' => 'Patient profile was not found.',
                'data' => [],
            ], 404);
        }

        if ($doctorId && ! $doctor) {
            return response()->json([
                'success' => false,
                'message' => 'Doctor profile was not found.',
                'data' => [],
            ], 404);
        }

        $reservation = $this->ensureActiveConsultation($patient, $doctor);

        return response()->json([
            'success' => true,
            'message' => 'New consultation record created.',
            'data' => [
                'consultation' => new ReservationConsultationResource($reservation->loadMissing(['doctor.user', 'patient.user', 'patient.reservations'])),
            ],
        ], 201);
    }

    public function showConsultation(int $id): JsonResponse
    {
        $reservation = $this->consultationQuery()->find($id);

        if (! $reservation) {
            return response()->json([
                'success' => false,
                'message' => 'Consultation reservation not found.',
                'data' => [],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Consultation retrieved successfully.',
            'data' => [
                'consultation' => new ReservationConsultationResource($reservation),
            ],
        ]);
    }

    public function updateConsultation(Request $request, int $id): JsonResponse
    {
        $reservation = $this->consultationQuery()->find($id);

        if (! $reservation) {
            return response()->json([
                'success' => false,
                'message' => 'Consultation reservation not found.',
                'data' => [],
            ], 404);
        }

        $validator = Validator::make($request->all(), $this->consultationRules());

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Please review the consultation details.',
                'data' => [
                    'errors' => $validator->errors(),
                ],
            ], 422);
        }

        $payload = $this->consultationPayload($validator->validated(), false);

        if ($this->reservationHasColumn('status') && $reservation->status !== 'completed') {
            $payload['status'] = $this->databaseStatus('in_progress');
        }

        $reservation->fill($payload);
        $reservation->save();

        return response()->json([
            'success' => true,
            'message' => 'Consultation records saved successfully.',
            'data' => [
                'consultation' => new ReservationConsultationResource($reservation->fresh(['doctor.user', 'patient.user', 'patient.reservations'])),
            ],
        ]);
    }

    public function completeConsultation(Request $request, int $id): JsonResponse
    {
        $reservation = $this->consultationQuery()->find($id);

        if (! $reservation) {
            return response()->json([
                'success' => false,
                'message' => 'Consultation reservation not found.',
                'data' => [],
            ], 404);
        }

        $validator = Validator::make($request->all(), $this->consultationRules());

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Please review the consultation details.',
                'data' => [
                    'errors' => $validator->errors(),
                ],
            ], 422);
        }

        $reservation->fill($this->consultationPayload($validator->validated(), true));
        $reservation->save();

        return response()->json([
            'success' => true,
            'message' => 'Consultation marked as complete.',
            'data' => [
                'consultation' => new ReservationConsultationResource($reservation->fresh(['doctor.user', 'patient.user', 'patient.reservations'])),
            ],
        ]);
    }

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
        $requestData = $request->all();
        $requestData['reservation_date'] = $requestData['reservation_date']
            ?? $requestData['appointment_date']
            ?? null;

        $validator = Validator::make($requestData, [
            'doctor_id' => ['required', 'integer', Rule::exists('doctors', Doctor::keyColumn())],
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

        $doctor = Doctor::query()
            ->with('user')
            ->find($payload['doctor_id']);

        if (! $doctor) {
            return response()->json([
                'success' => false,
                'message' => 'Selected doctor was not found.',
                'data' => [],
            ], 422);
        }

        $reservationDateColumn = Reservation::dateColumn();
        $activeReservationQuery = Reservation::query()
            ->where('doctor_id', $doctor->doctor_id)
            ->whereDate($reservationDateColumn, $payload['reservation_date'])
            ->where('time_slot', $payload['time_slot']);

        if ($this->reservationHasColumn('status')) {
            $activeReservationQuery->where('status', '!=', 'cancelled');
        }

        $hasActiveReservation = $activeReservationQuery->exists();

        if ($hasActiveReservation) {
            return response()->json([
                'success' => false,
                'message' => 'Selected time slot is already booked. Please choose another slot.',
                'data' => [],
            ], 409);
        }

        $reservation = DB::transaction(function () use ($payload, $doctor, $reservationDateColumn): Reservation {
            return Reservation::query()->create($this->reservationPayload(
                $payload,
                $doctor,
                $reservationDateColumn
            ));
        });

        return response()->json([
            'success' => true,
            'message' => 'Appointment confirmed successfully.',
            'data' => [
                'reservation' => $this->formatReservation($reservation->fresh('doctor.user')),
            ],
        ], 201);
    }

    #[OA\Patch(
        path: '/api/reservations/{id}/cancel',
        operationId: 'cancelReservation',
        tags: ['Reservations'],
        summary: 'Cancel an appointment reservation',
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
            new OA\Response(response: 200, description: 'Reservation cancelled successfully'),
            new OA\Response(response: 404, description: 'Reservation not found'),
            new OA\Response(response: 409, description: 'Reservation cannot be cancelled'),
        ]
    )]
    public function cancel(int $id): JsonResponse
    {
        $reservation = Reservation::query()
            ->with('doctor.user')
            ->find($id);

        if (! $reservation) {
            return response()->json([
                'success' => false,
                'message' => 'Reservation not found.',
                'data' => [],
            ], 404);
        }

        if (! $this->reservationHasColumn('status')) {
            return response()->json([
                'success' => false,
                'message' => 'Reservation status is not available for cancellation.',
                'data' => [],
            ], 409);
        }

        $status = $reservation->effectiveStatus();

        if ($status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'This appointment is already cancelled.',
                'data' => [
                    'reservation' => $this->formatReservation($reservation),
                ],
            ], 409);
        }

        if ($status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Completed appointments cannot be cancelled.',
                'data' => [
                    'reservation' => $this->formatReservation($reservation),
                ],
            ], 409);
        }

        $reservation->status = 'cancelled';
        $reservation->save();

        return response()->json([
            'success' => true,
            'message' => 'Appointment cancelled successfully.',
            'data' => [
                'reservation' => $this->formatReservation($reservation->fresh('doctor.user')),
            ],
        ]);
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

    private function reservationPayload(array $payload, Doctor $doctor, string $reservationDateColumn): array
    {
        $data = [
            'patient_id' => $this->resolvePatientId(),
            'doctor_id' => $doctor->doctor_id,
            $reservationDateColumn => $payload['reservation_date'],
            'time_slot' => $payload['time_slot'],
        ];

        $optionalData = [
            'reservation_code' => $this->generateReservationCode($payload['reservation_date']),
            'status' => $this->databaseStatus('confirmed'),
            'patient_name' => $payload['patient_name'],
            'patient_email' => $payload['patient_email'],
            'patient_phone' => $payload['patient_phone'],
            'notes' => $payload['notes'] ?? null,
            'consultation_fee' => $doctor->consultationFeeForBooking(),
            'processing_fee' => 5,
            'location' => $doctor->locationForBooking(),
        ];

        if ($reservationDateColumn !== 'appointment_date' && $this->reservationHasColumn('appointment_date')) {
            $optionalData['appointment_date'] = $payload['reservation_date'];
        }

        if ($reservationDateColumn !== 'reservation_date' && $this->reservationHasColumn('reservation_date')) {
            $optionalData['reservation_date'] = $payload['reservation_date'];
        }

        if ($this->reservationHasColumn('session_details')) {
            $optionalData['session_details'] = $payload['notes'] ?? 'Patient booking through ClinicReserve.';
        }

        if ($this->reservationHasColumn('prescription_info')) {
            $optionalData['prescription_info'] = [
                'medications' => [[
                    'medication_name' => '',
                    'dosage' => '',
                    'frequency' => 'Once daily',
                ]],
                'medication_name' => '',
                'dosage' => '',
                'frequency' => 'Once daily',
                'pharmacy_instructions' => '',
            ];
        }

        foreach ($optionalData as $column => $value) {
            if ($this->reservationHasColumn($column)) {
                $data[$column] = $value;
            }
        }

        return $data;
    }

    private function generateReservationCode(string $date): string
    {
        $prefix = 'RES-'.Carbon::parse($date)->format('Ymd').'-';

        do {
            $code = $prefix.random_int(1000, 9999);
        } while ($this->reservationHasColumn('reservation_code') && Reservation::query()->where('reservation_code', $code)->exists());

        return $code;
    }

    private function formatReservation(Reservation $reservation): array
    {
        $doctor = $reservation->doctor;
        $date = $reservation->appointmentDateForApi();
        $consultationFee = (float) ($reservation->consultation_fee ?? optional($doctor)->consultationFeeForBooking() ?? 150);
        $processingFee = (float) ($reservation->processing_fee ?? 5);

        return [
            'id' => $reservation->reservation_id,
            'reservation_id' => $reservation->reservation_id,
            'reservation_code' => $reservation->reservation_code,
            'patient_id' => $reservation->patient_id,
            'patient_name' => $reservation->patient_name,
            'patient_email' => $reservation->patient_email,
            'patient_phone' => $reservation->patient_phone,
            'doctor_id' => $reservation->doctor_id,
            'doctor_name' => optional($doctor)->display_name,
            'appointment_date' => $date,
            'reservation_date' => $date,
            'time_slot' => $reservation->time_slot,
            'status' => $reservation->effectiveStatus(),
            'can_cancel' => $reservation->canBeCancelled(),
            'notes' => $reservation->notes,
            'location' => $reservation->location ?: optional($doctor)->locationForBooking(),
            'consultation_fee' => $consultationFee,
            'processing_fee' => $processingFee,
            'total_estimated' => $consultationFee + $processingFee,
        ];
    }

    private function reservationHasColumn(string $column): bool
    {
        try {
            return Schema::hasColumn('reservations', $column);
        } catch (Throwable) {
            return false;
        }
    }

    private function consultationQuery()
    {
        return Reservation::query()->with(['doctor.user', 'patient.user', 'patient.reservations']);
    }

    private function consultationRules(): array
    {
        return [
            'chief_complaint' => ['nullable', 'string', 'max:4000'],
            'objective_observations' => ['nullable', 'string', 'max:4000'],
            'assessment_plan' => ['nullable', 'string', 'max:4000'],
            'prescription.medication_name' => ['nullable', 'string', 'max:150'],
            'prescription.dosage' => ['nullable', 'string', 'max:80'],
            'prescription.frequency' => ['nullable', 'string', 'max:80'],
            'prescription.pharmacy_instructions' => ['nullable', 'string', 'max:1000'],
            'prescription.medications' => ['nullable', 'array', 'max:20'],
            'prescription.medications.*.medication_name' => ['nullable', 'string', 'max:150'],
            'prescription.medications.*.dosage' => ['nullable', 'string', 'max:80'],
            'prescription.medications.*.frequency' => ['nullable', 'string', 'max:80'],
        ];
    }

    private function consultationPayload(array $data, bool $complete): array
    {
        $payload = [];

        foreach (['chief_complaint', 'objective_observations', 'assessment_plan'] as $column) {
            if ($this->reservationHasColumn($column) && array_key_exists($column, $data)) {
                $payload[$column] = $data[$column];
            }
        }

        if ($this->reservationHasColumn('prescription_info') && array_key_exists('prescription', $data)) {
            $payload['prescription_info'] = $this->prescriptionPayload($data['prescription']);
        }

        if ($complete) {
            if ($this->reservationHasColumn('status')) {
                $payload['status'] = $this->databaseStatus('completed');
            }

            if ($this->reservationHasColumn('completed_at')) {
                $payload['completed_at'] = now();
            }
        }

        return $payload;
    }

    private function prescriptionPayload(array $prescription): array
    {
        $medications = collect($prescription['medications'] ?? [])
            ->map(fn (array $medication): array => [
                'medication_name' => trim((string) ($medication['medication_name'] ?? '')),
                'dosage' => trim((string) ($medication['dosage'] ?? '')),
                'frequency' => trim((string) ($medication['frequency'] ?? 'Once daily')) ?: 'Once daily',
            ])
            ->filter(fn (array $medication): bool => $medication['medication_name'] !== '' || $medication['dosage'] !== '')
            ->values()
            ->all();

        if (! $medications) {
            $medications = [[
                'medication_name' => trim((string) ($prescription['medication_name'] ?? '')),
                'dosage' => trim((string) ($prescription['dosage'] ?? '')),
                'frequency' => trim((string) ($prescription['frequency'] ?? 'Once daily')) ?: 'Once daily',
            ]];
        }

        return [
            'medications' => $medications,
            'medication_name' => $medications[0]['medication_name'] ?? '',
            'dosage' => $medications[0]['dosage'] ?? '',
            'frequency' => $medications[0]['frequency'] ?? 'Once daily',
            'pharmacy_instructions' => trim((string) ($prescription['pharmacy_instructions'] ?? '')),
        ];
    }

    private function ensureActiveConsultation(?Patient $patient = null, ?Doctor $doctor = null): Reservation
    {
        $doctor ??= $this->defaultConsultationDoctor();
        $dateColumn = Reservation::dateColumn();
        $date = now()->toDateString();
        $patientUser = $patient?->relationLoaded('user') ? $patient->user : $patient?->user;
        $payload = [
            'patient_id' => $patient?->patient_id,
            'doctor_id' => $doctor->doctor_id,
            $dateColumn => $date,
            'time_slot' => '10:30 AM',
        ];

        $optionalData = [
            'reservation_code' => $this->generateReservationCode($date),
            'status' => $this->databaseStatus('in_progress'),
            'patient_name' => $patientUser?->full_name ?: 'Eleanor Vance',
            'patient_email' => $patientUser?->email ?: 'eleanor.vance@example.test',
            'patient_phone' => $patientUser?->phone ?: '+1 (555) 0189',
            'notes' => 'Active consultation created from provider workspace.',
            'consultation_fee' => $doctor->consultationFeeForBooking(),
            'processing_fee' => 5,
            'location' => $doctor->locationForBooking(),
            'chief_complaint' => '',
            'objective_observations' => '',
            'assessment_plan' => '',
            'prescription_info' => [
                'medications' => [[
                    'medication_name' => '',
                    'dosage' => '',
                    'frequency' => 'Once daily',
                ]],
                'medication_name' => '',
                'dosage' => '',
                'frequency' => 'Once daily',
                'pharmacy_instructions' => '',
            ],
        ];

        if ($dateColumn !== 'appointment_date' && $this->reservationHasColumn('appointment_date')) {
            $optionalData['appointment_date'] = $date;
        }

        if ($dateColumn !== 'reservation_date' && $this->reservationHasColumn('reservation_date')) {
            $optionalData['reservation_date'] = $date;
        }

        foreach ($optionalData as $column => $value) {
            if ($this->reservationHasColumn($column)) {
                $payload[$column] = $value;
            }
        }

        return Reservation::query()->create($payload);
    }

    private function patientForId(int $patientId): ?Patient
    {
        try {
            if (! Schema::hasTable('patients')) {
                return null;
            }

            return Patient::query()
                ->with('user')
                ->find($patientId);
        } catch (Throwable) {
            return null;
        }
    }

    private function doctorForId(int $doctorId): ?Doctor
    {
        try {
            if (! Schema::hasTable('doctors')) {
                return null;
            }

            return Doctor::query()
                ->with('user')
                ->find($doctorId);
        } catch (Throwable) {
            return null;
        }
    }

    private function defaultConsultationDoctor(): Doctor
    {
        $doctor = $this->doctorHasColumn('name')
            ? Doctor::query()->where('name', 'Dr. Sarah Jenkins')->first()
            : null;

        if ($doctor) {
            return $doctor;
        }

        $doctor = Doctor::query()->first();

        if ($doctor) {
            return $doctor;
        }

        $availableColumns = array_flip(Schema::getColumnListing('doctors'));
        $data = [
            'name' => 'Dr. Sarah Jenkins',
            'specialty' => 'Cardiology',
            'bio' => 'Board-certified provider focused on preventive cardiology and consultation care.',
            'rating' => 4.9,
            'available_time' => 'Mon-Fri, 09:00 AM - 04:00 PM',
            'location' => 'Main City Hospital, Building A, Suite 302',
            'consultation_fee' => 150,
            'reviews_count' => 120,
            'accepts_new_patients' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        return Doctor::query()->create(array_intersect_key($data, $availableColumns));
    }

    private function doctorHasColumn(string $column): bool
    {
        try {
            return Schema::hasColumn('doctors', $column);
        } catch (Throwable) {
            return false;
        }
    }

    private function databaseStatus(string $desired): string
    {
        $allowed = $this->reservationStatusValues();

        if (! $allowed || in_array($desired, $allowed, true)) {
            return $desired;
        }

        $fallbacks = match ($desired) {
            'in_progress' => ['confirmed', 'Confirmed', 'pending', 'Pending'],
            'completed' => ['completed', 'Completed', 'done', 'Done', 'confirmed', 'Confirmed'],
            default => ['confirmed', 'Confirmed', 'pending', 'Pending'],
        };

        foreach ($fallbacks as $fallback) {
            if (in_array($fallback, $allowed, true)) {
                return $fallback;
            }
        }

        return $allowed[0];
    }

    private function reservationStatusValues(): array
    {
        try {
            $column = DB::selectOne("SHOW COLUMNS FROM reservations LIKE 'status'");
            $type = $column->Type ?? $column->type ?? null;

            if (! is_string($type) || ! str_starts_with($type, 'enum(')) {
                return [];
            }

            preg_match_all("/'((?:[^'\\\\]|\\\\.)*)'/", $type, $matches);

            return array_map(
                fn (string $value): string => stripslashes($value),
                $matches[1] ?? []
            );
        } catch (Throwable) {
            return [];
        }
    }
}
