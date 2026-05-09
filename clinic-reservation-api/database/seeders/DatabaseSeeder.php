<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (! Schema::hasTable('doctors')) {
            return;
        }

        $hadDoctors = Doctor::query()->exists();

        DB::transaction(function () use ($hadDoctors): void {
            $this->seedDoctor([
                'name' => 'Dr. Sarah Jenkins',
                'email' => 'sarah.jenkins@clinicreserve.test',
                'phone' => '01000000001',
                'specialty' => 'Cardiology',
                'bio' => 'Dr. Jenkins brings over 15 years of specialized experience in cardiovascular health. She is board-certified and focuses on preventive cardiology, echocardiography, and complex hypertension management.',
                'rating' => 4.9,
                'available_time' => 'Mon-Fri, 09:00 AM - 04:00 PM',
                'location' => 'Main City Hospital, Building A, Suite 302',
                'consultation_fee' => 150,
            ]);

            if ($hadDoctors) {
                return;
            }

            $this->seedDoctor([
                'name' => 'Dr. Omar Hassan',
                'email' => 'omar.hassan@clinicreserve.test',
                'phone' => '01000000002',
                'specialty' => 'Dermatology',
                'bio' => 'Experienced dermatologist focused on acne care, skin allergy treatment, and preventive skin health consultations.',
                'rating' => 4.7,
                'available_time' => 'Sun-Thu, 10:00 AM - 03:00 PM',
                'location' => 'ClinicReserve Medical Center, Floor 2',
                'consultation_fee' => 130,
            ]);

            $this->seedDoctor([
                'name' => 'Dr. Lina Morgan',
                'email' => 'lina.morgan@clinicreserve.test',
                'phone' => '01000000003',
                'specialty' => 'Pediatrics',
                'bio' => 'Pediatric specialist providing friendly preventive care, wellness checks, and child development consultations.',
                'rating' => 4.8,
                'available_time' => 'Mon-Wed, 09:00 AM - 02:30 PM',
                'location' => 'Family Care Wing, Room 118',
                'consultation_fee' => 120,
            ]);
        });
    }

    private function seedDoctor(array $profile): void
    {
        $user = $this->doctorUser($profile);
        $doctor = $this->findDoctor($profile['name'], $user);
        $doctorData = $this->doctorData($profile, $user?->user_id);

        if (! $doctor) {
            Doctor::query()->create($doctorData);

            return;
        }

        $doctor->fill($doctorData);
        $doctor->save();
    }

    private function doctorUser(array $profile): ?User
    {
        if (! Schema::hasTable('users')) {
            return null;
        }

        $user = null;

        if (Schema::hasColumn('users', 'full_name')) {
            $user = User::query()
                ->where('full_name', $profile['name'])
                ->first();
        }

        if (! $user && Schema::hasColumn('users', 'email')) {
            $user = User::query()
                ->where('email', $profile['email'])
                ->first();
        }

        if (! $user) {
            return User::query()->create($this->userData($profile, true));
        }

        $user->fill($this->userData($profile, false));
        $user->save();

        return $user;
    }

    private function findDoctor(string $name, ?User $user): ?Doctor
    {
        if ($user && Schema::hasColumn('doctors', 'user_id')) {
            $doctor = Doctor::query()
                ->where('user_id', $user->user_id)
                ->first();

            if ($doctor) {
                return $doctor;
            }
        }

        if (Schema::hasColumn('doctors', 'name')) {
            return Doctor::query()
                ->where('name', $name)
                ->first();
        }

        return null;
    }

    private function userData(array $profile, bool $isNew): array
    {
        $available = array_flip(Schema::getColumnListing('users'));
        $data = $isNew ? [
            'full_name' => $profile['name'],
            'email' => $profile['email'],
            'phone' => $profile['phone'],
            'password_hash' => Hash::make('password'),
            'role' => 'DOCTOR',
            'created_at' => now(),
        ] : [
            'full_name' => $profile['name'],
            'role' => 'DOCTOR',
        ];

        return array_intersect_key($data, $available);
    }

    private function doctorData(array $profile, ?int $userId): array
    {
        $available = array_flip(Schema::getColumnListing('doctors'));
        $data = [
            'user_id' => $userId,
            'name' => $profile['name'],
            'specialty' => $profile['specialty'],
            'bio' => $profile['bio'],
            'rating' => $profile['rating'],
            'available_time' => $profile['available_time'],
            'location' => $profile['location'],
            'image' => null,
            'consultation_fee' => $profile['consultation_fee'],
            'created_at' => now(),
            'updated_at' => now(),
        ];

        return array_intersect_key($data, $available);
    }
}
