<?php

namespace Database\Seeders;

use App\Models\Doctor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (Doctor::query()->exists()) {
            return;
        }

        Doctor::query()->insert([
            [
                'name' => 'Dr. Sarah Jenkins',
                'specialty' => 'Cardiology',
                'bio' => 'Dr. Jenkins brings over 15 years of specialized experience in cardiovascular health. She is board-certified and focuses on preventive cardiology, echocardiography, and complex hypertension management.',
                'rating' => 4.9,
                'available_time' => 'Mon-Fri, 09:00 AM - 04:00 PM',
                'location' => 'Main City Hospital, Building A, Suite 302',
                'image' => null,
                'consultation_fee' => 150,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Dr. Omar Hassan',
                'specialty' => 'Dermatology',
                'bio' => 'Experienced dermatologist focused on acne care, skin allergy treatment, and preventive skin health consultations.',
                'rating' => 4.7,
                'available_time' => 'Sun-Thu, 10:00 AM - 03:00 PM',
                'location' => 'ClinicReserve Medical Center, Floor 2',
                'image' => null,
                'consultation_fee' => 130,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Dr. Lina Morgan',
                'specialty' => 'Pediatrics',
                'bio' => 'Pediatric specialist providing friendly preventive care, wellness checks, and child development consultations.',
                'rating' => 4.8,
                'available_time' => 'Mon-Wed, 09:00 AM - 02:30 PM',
                'location' => 'Family Care Wing, Room 118',
                'image' => null,
                'consultation_fee' => 120,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
