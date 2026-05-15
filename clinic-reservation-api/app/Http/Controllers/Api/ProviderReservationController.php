<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;
use Throwable;

class ProviderReservationController extends Controller
{
    #[OA\Get(
        path: '/api/provider/reservations',
        operationId: 'providerReservations',
        tags: ['Provider Reservations'],
        summary: 'List reservation records for provider dashboard',
        parameters: [
            new OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string', maxLength: 100), example: 'john'),
            new OA\Parameter(name: 'q', in: 'query', required: false, schema: new OA\Schema(type: 'string', maxLength: 100), example: 'CR-8829A'),
            new OA\Parameter(name: 'date', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date'), example: '2026-05-11'),
            new OA\Parameter(name: 'doctor_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer'), example: 3),
            new OA\Parameter(name: 'status', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['confirmed', 'pending', 'completed', 'cancelled']), example: 'confirmed'),
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', minimum: 1), example: 1),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 50), example: 5),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Doctor reservations retrieved successfully'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->query(), [
            'search' => ['nullable', 'string', 'max:100'],
            'q' => ['nullable', 'string', 'max:100'],
            'date' => ['nullable', 'date_format:Y-m-d'],
            'doctor_id' => ['nullable', 'integer'],
            'status' => ['nullable', Rule::in(['confirmed', 'pending', 'completed', 'cancelled'])],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide valid reservation filters.',
                'data' => [
                    'errors' => $validator->errors(),
                ],
            ], 422);
        }

        $filters = $validator->validated();
        $page = (int) ($filters['page'] ?? 1);
        $perPage = (int) ($filters['per_page'] ?? 5);

        if (! Schema::hasTable('reservations')) {
            return $this->emptyReservationsResponse($page, $perPage);
        }

        $records = $this->filteredReservations($filters);
        $total = $records->count();
        $pageRecords = $records
            ->slice(($page - 1) * $perPage, $perPage)
            ->values();

        return response()->json([
            'success' => true,
            'message' => $total === 0
                ? 'No matching reservations found.'
                : 'Doctor reservations retrieved successfully.',
            'data' => [
                'records' => $pageRecords,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'from' => $total === 0 ? 0 : (($page - 1) * $perPage) + 1,
                    'to' => $total === 0 ? 0 : min($page * $perPage, $total),
                ],
            ],
        ]);
    }

    #[OA\Get(
        path: '/api/provider/active-consultation/{patientId}/{doctorId}',
        operationId: 'providerActiveConsultation',
        tags: ['Provider Reservations'],
        summary: 'Show active consultation context for a patient and doctor',
        parameters: [
            new OA\Parameter(name: 'patientId', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 1),
            new OA\Parameter(name: 'doctorId', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 3),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Active consultation context retrieved successfully'),
            new OA\Response(response: 404, description: 'Patient, doctor, or reservation context not found'),
        ]
    )]
    public function activeConsultation(int $patientId, int $doctorId): JsonResponse
    {
        if (! Schema::hasTable('reservations')) {
            return $this->notFound('Reservation context was not found.');
        }

        if (! $this->reservationHasColumn('patient_id') || ! $this->reservationHasColumn('doctor_id')) {
            return $this->notFound('Reservation context is not available in this database.');
        }

        $patient = $this->findPatient($patientId);

        if (! $patient) {
            return $this->notFound('Patient profile was not found.');
        }

        $doctor = $this->findDoctor($doctorId);

        if (! $doctor) {
            return $this->notFound('Doctor profile was not found.');
        }

        $reservation = $this->reservationQuery()
            ->where('patient_id', $patient->patient_id)
            ->where('doctor_id', $doctor->doctor_id)
            ->get()
            ->sortBy(fn (Reservation $reservation): string => $this->consultationSortKey($reservation))
            ->first();

        if (! $reservation) {
            return $this->notFound('Reservation context was not found for this patient and doctor.');
        }

        return response()->json([
            'success' => true,
            'message' => 'Active consultation context retrieved successfully.',
            'data' => [
                'patient' => $this->formatPatient($patient, $reservation),
                'doctor' => $this->formatDoctor($doctor),
                'reservation' => $this->formatConsultationReservation($reservation),
            ],
        ]);
    }

    private function filteredReservations(array $filters): Collection
    {
        $search = $this->cleanSearch($filters['search'] ?? $filters['q'] ?? null);
        $query = $this->reservationQuery();

        if (isset($filters['doctor_id'])) {
            $query->where('doctor_id', (int) $filters['doctor_id']);
        }

        if (isset($filters['date'])) {
            $query->whereDate(Reservation::dateColumn(), $filters['date']);
        }

        if (isset($filters['status']) && $this->reservationHasColumn('status')) {
            $query->whereRaw('LOWER(status) = ?', [strtolower($filters['status'])]);
        }

        return $query
            ->get()
            ->sortBy(fn (Reservation $reservation): string => $this->reservationSortKey($reservation))
            ->map(fn (Reservation $reservation): array => $this->formatReservationRow($reservation))
            ->filter(function (array $record) use ($search): bool {
                if (! $search) {
                    return true;
                }

                return str_contains(mb_strtolower($record['patient_name']), $search)
                    || str_contains(mb_strtolower((string) $record['reservation_code']), $search);
            })
            ->values();
    }

    private function emptyReservationsResponse(int $page, int $perPage): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'No matching reservations found.',
            'data' => [
                'records' => [],
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => 0,
                    'from' => 0,
                    'to' => 0,
                ],
            ],
        ]);
    }

    private function reservationQuery(): Builder
    {
        $query = Reservation::query();
        $relations = $this->reservationRelations();

        if ($relations !== []) {
            $query->with($relations);
        }

        return $query;
    }

    private function reservationRelations(): array
    {
        $relations = [];

        if (Schema::hasTable('doctors')) {
            $relations[] = 'doctor';

            if (Schema::hasTable('users')) {
                $relations[] = 'doctor.user';
            }
        }

        if (Schema::hasTable('patients')) {
            $relations[] = 'patient';

            if (Schema::hasTable('users')) {
                $relations[] = 'patient.user';
            }
        }

        return $relations;
    }

    private function formatReservationRow(Reservation $reservation): array
    {
        $patientName = $this->patientName($reservation);
        $patientId = $this->resolvePatientId($reservation, $patientName);
        $doctorId = $this->resolveDoctorId($reservation);
        $date = $reservation->appointmentDateForApi();
        $dashboardAvailable = $patientId !== null && $doctorId !== null;

        return [
            'reservation_id' => $reservation->reservation_id,
            'reservation_code' => $reservation->reservation_code ?: 'RES-'.$reservation->reservation_id,
            'patient_id' => $patientId,
            'doctor_id' => $doctorId,
            'patient_name' => $patientName,
            'patient_initials' => $this->initials($patientName),
            'appointment_date' => $date,
            'formatted_date' => $this->formattedDate($date),
            'time_slot' => $reservation->time_slot ?: 'Time pending',
            'status' => $this->statusForApi($reservation),
            'dashboard_url' => $dashboardAvailable
                ? '/provider/active-consultation/'.$patientId.'/'.$doctorId
                : null,
            'dashboard_available' => $dashboardAvailable,
        ];
    }

    private function formatConsultationReservation(Reservation $reservation): array
    {
        $date = $reservation->appointmentDateForApi();

        return [
            'reservation_id' => $reservation->reservation_id,
            'reservation_code' => $reservation->reservation_code ?: 'RES-'.$reservation->reservation_id,
            'patient_id' => $reservation->patient_id,
            'doctor_id' => $reservation->doctor_id,
            'appointment_date' => $date,
            'formatted_date' => $this->formattedDate($date),
            'time_slot' => $reservation->time_slot ?: 'Time pending',
            'status' => $this->statusForApi($reservation),
            'session_details' => $this->reservationValue($reservation, 'session_details') ?: $reservation->notes,
            'chief_complaint' => $this->reservationValue($reservation, 'chief_complaint'),
            'objective_observations' => $this->reservationValue($reservation, 'objective_observations'),
            'assessment_plan' => $this->reservationValue($reservation, 'assessment_plan'),
            'prescription_info' => $this->prescriptionInfo($reservation),
            'notes' => $reservation->notes,
        ];
    }

    private function formatPatient(Patient $patient, Reservation $reservation): array
    {
        $user = $patient->relationLoaded('user') ? $patient->user : null;
        $name = $this->patientName($reservation);

        return [
            'patient_id' => $patient->patient_id,
            'name' => $name,
            'initials' => $this->initials($name),
            'email' => $reservation->patient_email ?: $user?->email,
            'phone' => $reservation->patient_phone ?: $user?->phone,
            'gender' => $this->patientValue($patient, 'gender'),
            'date_of_birth' => $this->dateValue($this->patientValue($patient, 'date_of_birth')),
            'address' => $this->patientValue($patient, 'address'),
            'medical_history' => $this->patientValue($patient, 'medical_history'),
        ];
    }

    private function formatDoctor(Doctor $doctor): array
    {
        $user = $doctor->relationLoaded('user') ? $doctor->user : null;

        return [
            'doctor_id' => $doctor->doctor_id,
            'name' => $doctor->display_name,
            'specialty' => $doctor->specialty ?: 'General Care',
            'email' => $user?->email,
            'phone' => $user?->phone,
            'location' => $doctor->locationForBooking(),
        ];
    }

    private function patientName(Reservation $reservation): string
    {
        $patient = $reservation->relationLoaded('patient') ? $reservation->patient : null;
        $user = $patient && $patient->relationLoaded('user') ? $patient->user : null;

        return $reservation->patient_name
            ?: $user?->full_name
            ?: ($patient?->patient_id ? 'Patient '.$patient->patient_id : 'Patient');
    }

    private function resolvePatientId(Reservation $reservation, string $patientName): ?int
    {
        if (is_numeric($reservation->patient_id) && (int) $reservation->patient_id > 0) {
            return (int) $reservation->patient_id;
        }

        $patient = $reservation->relationLoaded('patient') ? $reservation->patient : null;

        if (is_numeric($patient?->patient_id) && (int) $patient->patient_id > 0) {
            return (int) $patient->patient_id;
        }

        $matchedPatient = $this->findPatientByReservationContact($reservation, $patientName);

        return is_numeric($matchedPatient?->patient_id) && (int) $matchedPatient->patient_id > 0
            ? (int) $matchedPatient->patient_id
            : null;
    }

    private function resolveDoctorId(Reservation $reservation): ?int
    {
        if (is_numeric($reservation->doctor_id) && (int) $reservation->doctor_id > 0) {
            return (int) $reservation->doctor_id;
        }

        $doctor = $reservation->relationLoaded('doctor') ? $reservation->doctor : null;

        return is_numeric($doctor?->doctor_id) && (int) $doctor->doctor_id > 0
            ? (int) $doctor->doctor_id
            : null;
    }

    private function findPatientByReservationContact(Reservation $reservation, string $patientName): ?Patient
    {
        if (! Schema::hasTable('patients') || ! Schema::hasTable('users') || ! Schema::hasColumn('patients', 'user_id')) {
            return null;
        }

        $matches = [
            'email' => $this->reservationValue($reservation, 'patient_email'),
            'phone' => $this->reservationValue($reservation, 'patient_phone'),
            'full_name' => $this->safePatientNameForLookup($reservation, $patientName),
        ];

        foreach ($matches as $column => $value) {
            if (! filled($value) || ! $this->usersHasColumn($column)) {
                continue;
            }

            $patient = Patient::query()
                ->with('user')
                ->whereHas('user', fn (Builder $query): Builder => $query->where($column, trim((string) $value)))
                ->first();

            if ($patient) {
                return $patient;
            }
        }

        return null;
    }

    private function safePatientNameForLookup(Reservation $reservation, string $patientName): ?string
    {
        $storedName = $this->reservationValue($reservation, 'patient_name');
        $name = filled($storedName) ? (string) $storedName : $patientName;
        $name = trim($name);

        if ($name === '' || preg_match('/^Patient(?:\s+\d+)?$/i', $name)) {
            return null;
        }

        return $name;
    }

    private function statusForApi(Reservation $reservation): string
    {
        $status = strtolower(trim((string) ($reservation->status ?? 'pending')));

        return $status !== '' ? $status : 'pending';
    }

    private function prescriptionInfo(Reservation $reservation): mixed
    {
        $prescription = $this->reservationValue($reservation, 'prescription_info');

        if (is_string($prescription)) {
            $decoded = json_decode($prescription, true);

            return json_last_error() === JSON_ERROR_NONE ? $decoded : $prescription;
        }

        return $prescription;
    }

    private function reservationSortKey(Reservation $reservation): string
    {
        return ($reservation->appointmentDateForApi() ?: '9999-12-31').' '.($reservation->time_slot ?: '99:99');
    }

    private function consultationSortKey(Reservation $reservation): string
    {
        $statusPriority = match ($this->statusForApi($reservation)) {
            'in_progress' => '0',
            'confirmed', 'pending' => '1',
            'completed' => '2',
            default => '3',
        };
        $date = $reservation->appointmentDateForApi();
        $timestamp = $date ? Carbon::parse($date)->timestamp : 0;
        $latestFirst = str_pad((string) (9999999999 - $timestamp), 10, '0', STR_PAD_LEFT);

        return $statusPriority.' '.$latestFirst.' '.($reservation->time_slot ?: '99:99');
    }

    private function cleanSearch(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : mb_strtolower($value);
    }

    private function initials(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name)) ?: [];
        $parts = array_values(array_filter($parts));

        if ($parts === []) {
            return 'P';
        }

        return strtoupper(substr($parts[0], 0, 1).substr($parts[1] ?? $parts[0], 0, 1));
    }

    private function formattedDate(?string $date): ?string
    {
        if (! $date) {
            return null;
        }

        try {
            return Carbon::parse($date)->format('M d, Y');
        } catch (Throwable) {
            return $date;
        }
    }

    private function dateValue(mixed $date): ?string
    {
        if (! $date) {
            return null;
        }

        try {
            return Carbon::parse($date)->format('Y-m-d');
        } catch (Throwable) {
            return null;
        }
    }

    private function findPatient(int $patientId): ?Patient
    {
        if (! Schema::hasTable('patients')) {
            return null;
        }

        $query = Patient::query();

        if (Schema::hasTable('users')) {
            $query->with('user');
        }

        return $query->find($patientId);
    }

    private function findDoctor(int $doctorId): ?Doctor
    {
        if (! Schema::hasTable('doctors')) {
            return null;
        }

        $query = Doctor::query();

        if (Schema::hasTable('users')) {
            $query->with('user');
        }

        return $query->find($doctorId);
    }

    private function patientValue(Patient $patient, string $column): mixed
    {
        return $this->patientHasColumn($column) ? $patient->getAttribute($column) : null;
    }

    private function reservationValue(Reservation $reservation, string $column): mixed
    {
        return $this->reservationHasColumn($column) ? $reservation->getAttribute($column) : null;
    }

    private function patientHasColumn(string $column): bool
    {
        try {
            return Schema::hasColumn('patients', $column);
        } catch (Throwable) {
            return false;
        }
    }

    private function reservationHasColumn(string $column): bool
    {
        try {
            return Schema::hasColumn('reservations', $column);
        } catch (Throwable) {
            return false;
        }
    }

    private function usersHasColumn(string $column): bool
    {
        try {
            return Schema::hasColumn('users', $column);
        } catch (Throwable) {
            return false;
        }
    }

    private function notFound(string $message): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => [],
        ], 404);
    }
}
