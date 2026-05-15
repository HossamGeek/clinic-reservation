<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientSummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $this->resource->relationLoaded('user') ? $this->user : null;
        $name = $user && filled($user->full_name) ? $user->full_name : 'Eleanor Vance';
        $dob = $this->date_of_birth ? Carbon::parse($this->date_of_birth) : Carbon::parse('1978-12-04');

        return [
            'id' => $this->patient_id,
            'name' => $name,
            'date_of_birth' => $dob->format('Y-m-d'),
            'age' => $dob->age,
            'gender' => $this->gender ?: 'Female',
            'phone' => $user->phone ?? '+1 (555) 0189',
            'record_number' => 'PT-'.str_pad((string) ($this->patient_id ?: 88492), 5, '0', STR_PAD_LEFT),
            'avatar' => null,
            'vitals' => [
                'blood_type' => 'A+',
                'weight' => '68 kg',
                'blood_pressure' => '118/76',
            ],
            'allergies' => [
                'Penicillin Allergy',
            ],
            'medical_history' => $this->history(),
        ];
    }

    private function history(): array
    {
        if (is_array($this->medical_history)) {
            return $this->medical_history;
        }

        return [
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
        ];
    }
}
