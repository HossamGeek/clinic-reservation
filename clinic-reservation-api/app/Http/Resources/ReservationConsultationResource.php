<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReservationConsultationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $date = $this->appointmentDateForApi() ?: now()->toDateString();
        $doctor = $this->resource->relationLoaded('doctor') ? $this->doctor : null;
        $patient = $this->resource->relationLoaded('patient') ? $this->patient : null;

        return [
            'id' => $this->reservation_id,
            'reservation_id' => $this->reservation_id,
            'reservation_code' => $this->reservation_code,
            'status' => $this->statusForUi(),
            'date' => $date,
            'time_slot' => $this->time_slot ?: '10:30 AM',
            'header_time' => Carbon::parse($date.' '.($this->time_slot ?: '10:30 AM'))->format('D, g:i A'),
            'doctor_name' => $doctor?->display_name ?: 'Dr. Sarah Jenkins',
            'patient' => $this->patientPayload($request, $patient),
            'clinical_notes' => [
                'chief_complaint' => $this->chief_complaint,
                'objective_observations' => $this->objective_observations,
                'assessment_plan' => $this->assessment_plan,
            ],
            'prescription' => $this->prescriptionPayload(),
            'completed_at' => $this->completed_at?->toIso8601String(),
        ];
    }

    private function prescriptionPayload(): array
    {
        $prescription = is_array($this->prescription_info) ? $this->prescription_info : [];
        $medications = $prescription['medications'] ?? [];

        if (! is_array($medications) || $medications === []) {
            $medications = [[
                'medication_name' => $prescription['medication_name'] ?? '',
                'dosage' => $prescription['dosage'] ?? '',
                'frequency' => $prescription['frequency'] ?? 'Once daily',
            ]];
        }

        return [
            'medications' => array_values($medications),
            'medication_name' => $medications[0]['medication_name'] ?? '',
            'dosage' => $medications[0]['dosage'] ?? '',
            'frequency' => $medications[0]['frequency'] ?? 'Once daily',
            'pharmacy_instructions' => $prescription['pharmacy_instructions'] ?? '',
        ];
    }

    private function statusForUi(): string
    {
        if ($this->completed_at || in_array($this->status, ['completed', 'Completed', 'done', 'Done'], true)) {
            return 'completed';
        }

        return 'in_progress';
    }

    private function patientPayload(Request $request, mixed $patient): array
    {
        $payload = $patient
            ? (new PatientSummaryResource($patient))->resolve($request)
            : $this->fallbackPatient();
        $payload['medical_history'] = $payload['medical_history'] ?? [];

        $reservationHistory = $patient ? $this->reservationHistory($patient) : [];

        if ($reservationHistory) {
            $payload['medical_history'] = array_merge($reservationHistory, $payload['medical_history']);

            return $payload;
        }

        $currentHistory = $this->currentConsultationHistory();
        if ($currentHistory) {
            array_unshift($payload['medical_history'], $currentHistory);
        }

        return $payload;
    }

    private function currentConsultationHistory(): ?array
    {
        $summary = $this->assessment_plan ?: $this->chief_complaint ?: $this->objective_observations;

        if (! filled($summary)) {
            return null;
        }

        return [
            'title' => $this->status === 'completed' ? 'Completed Consultation' : 'Active Consultation',
            'date' => $this->appointmentDateForApi() ?: now()->toDateString(),
            'summary' => $summary,
            'reservation_id' => $this->reservation_id,
            'clinical_notes' => [
                'chief_complaint' => $this->chief_complaint,
                'objective_observations' => $this->objective_observations,
                'assessment_plan' => $this->assessment_plan,
            ],
            'prescription' => $this->prescriptionPayload(),
        ];
    }

    private function reservationHistory(mixed $patient): array
    {
        if (! $patient->relationLoaded('reservations')) {
            return [];
        }

        return $patient->reservations
            ->filter(fn ($reservation): bool => $this->hasRecordContent($reservation))
            ->sortByDesc(fn ($reservation): string => (string) ($reservation->completed_at ?? $reservation->updated_at ?? $reservation->created_at))
            ->values()
            ->map(fn ($reservation): array => $this->historyItemForReservation($reservation))
            ->all();
    }

    private function hasRecordContent(mixed $reservation): bool
    {
        $prescription = is_array($reservation->prescription_info) ? $reservation->prescription_info : [];
        $medications = $prescription['medications'] ?? [];

        return filled($reservation->chief_complaint)
            || filled($reservation->objective_observations)
            || filled($reservation->assessment_plan)
            || filled($prescription['pharmacy_instructions'] ?? null)
            || collect(is_array($medications) ? $medications : [])->contains(
                fn (array $medication): bool => filled($medication['medication_name'] ?? null) || filled($medication['dosage'] ?? null)
            );
    }

    private function historyItemForReservation(mixed $reservation): array
    {
        $isCompleted = $reservation->completed_at || in_array($reservation->status, ['completed', 'Completed', 'done', 'Done'], true);
        $summary = $reservation->assessment_plan
            ?: $reservation->chief_complaint
            ?: $reservation->objective_observations
            ?: $this->prescriptionSummary($reservation);

        return [
            'title' => $isCompleted ? 'Completed Consultation' : 'Saved Records',
            'date' => $reservation->appointmentDateForApi() ?: now()->toDateString(),
            'summary' => $summary ?: 'Clinical record saved.',
            'reservation_id' => $reservation->reservation_id,
            'clinical_notes' => [
                'chief_complaint' => $reservation->chief_complaint,
                'objective_observations' => $reservation->objective_observations,
                'assessment_plan' => $reservation->assessment_plan,
            ],
            'prescription' => $this->prescriptionPayloadFor($reservation),
        ];
    }

    private function prescriptionPayloadFor(mixed $reservation): array
    {
        return (new self($reservation))->prescriptionPayload();
    }

    private function prescriptionSummary(mixed $reservation): ?string
    {
        $prescription = is_array($reservation->prescription_info) ? $reservation->prescription_info : [];
        $medications = $prescription['medications'] ?? [];

        if (is_array($medications) && isset($medications[0])) {
            $name = $medications[0]['medication_name'] ?? null;
            $dosage = $medications[0]['dosage'] ?? null;

            return trim($name.' '.$dosage) ?: null;
        }

        return null;
    }

    private function fallbackPatient(): array
    {
        return [
            'id' => $this->patient_id,
            'name' => $this->patient_name ?: 'Eleanor Vance',
            'date_of_birth' => '1978-12-04',
            'age' => 45,
            'gender' => 'Female',
            'phone' => $this->patient_phone ?: '+1 (555) 0189',
            'record_number' => 'PT-88492',
            'avatar' => null,
            'vitals' => [
                'blood_type' => 'A+',
                'weight' => '68 kg',
                'blood_pressure' => '118/76',
            ],
            'allergies' => [
                'Penicillin Allergy',
            ],
            'medical_history' => [
                [
                    'title' => 'Migraine Management',
                    'date' => '2023-10-12',
                    'summary' => 'Patient reported increased frequency of visual auras. Prescribed follow-up care.',
                ],
                [
                    'title' => 'Annual Physical',
                    'date' => '2023-01-05',
                    'summary' => 'All vitals normal. Comprehensive blood panel requested.',
                ],
                [
                    'title' => 'Sprained Ankle',
                    'date' => '2022-08-22',
                    'summary' => 'Grade 1 sprain on right ankle. RICE protocol advised.',
                ],
            ],
        ];
    }
}
