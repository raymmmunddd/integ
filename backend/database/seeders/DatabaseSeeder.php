<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Specialization;
use App\Models\TherapistAvailability;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Specializations
        $specializations = [
            'Physical Therapist',
            'Occupational Therapist',
            'Speech Therapist',
            'Respiratory Therapist',
            'Psychotherapist',
            'Cognitive Behavioral Therapist',
            'Family/Marriage Therapist',
            'Rehabilitation Therapist',
            'Sports Therapist',
            'Child/Developmental Therapist',
        ];

        $specializationModels = [];
        foreach ($specializations as $spec) {
            $specializationModels[$spec] = Specialization::create(['name' => $spec]);
        }

        // Create sample student
        $student = User::create([
            'first_name' => 'Juan',
            'middle_initial' => 'D',
            'last_name' => 'Cruz',
            'program' => 'BSIT',
            'email' => 'student@example.com',
            'date_of_birth' => '2000-01-15',
            'gender' => 'Male',
            'house_number' => '123',
            'barangay' => 'Barangay 1',
            'city_municipality' => 'Olongapo City',
            'phone_number' => '09123456789',
            'password' => Hash::make('password'),
            'role' => 'student',
            'email_verified_at' => now(),
        ]);

        // Create sample therapists
        $therapists = [
            [
                'first_name' => 'Willie',
                'last_name' => 'Revillame',
                'specialization' => 'Physical Therapist',
                'email' => 'willie.revillame@example.com',
                'availabilities' => [
                    ['day' => 'Monday', 'start' => '09:00', 'end' => '17:00'],
                    ['day' => 'Tuesday', 'start' => '09:00', 'end' => '17:00'],
                    ['day' => 'Wednesday', 'start' => '09:00', 'end' => '17:00'],
                    ['day' => 'Thursday', 'start' => '09:00', 'end' => '17:00'],
                    ['day' => 'Friday', 'start' => '09:00', 'end' => '17:00'],
                ]
            ],
            [
                'first_name' => 'Jane',
                'last_name' => 'Doe',
                'specialization' => 'Respiratory Therapist',
                'email' => 'jane.doe@example.com',
                'availabilities' => [
                    ['day' => 'Monday', 'start' => '10:00', 'end' => '18:00'],
                    ['day' => 'Wednesday', 'start' => '10:00', 'end' => '18:00'],
                    ['day' => 'Friday', 'start' => '10:00', 'end' => '18:00'],
                ]
            ],
            [
                'first_name' => 'John',
                'last_name' => 'Smith',
                'specialization' => 'Sports Therapist',
                'email' => 'john.smith@example.com',
                'availabilities' => [
                    ['day' => 'Tuesday', 'start' => '08:00', 'end' => '16:00'],
                    ['day' => 'Thursday', 'start' => '08:00', 'end' => '16:00'],
                ]
            ],
            [
                'first_name' => 'Maria',
                'last_name' => 'Santos',
                'specialization' => 'Psychotherapist',
                'email' => 'maria.santos@example.com',
                'availabilities' => [
                    ['day' => 'Monday', 'start' => '13:00', 'end' => '20:00'],
                    ['day' => 'Tuesday', 'start' => '13:00', 'end' => '20:00'],
                    ['day' => 'Wednesday', 'start' => '13:00', 'end' => '20:00'],
                    ['day' => 'Thursday', 'start' => '13:00', 'end' => '20:00'],
                ]
            ],
            [
                'first_name' => 'Pedro',
                'last_name' => 'Gonzales',
                'specialization' => 'Speech Therapist',
                'email' => 'pedro.gonzales@example.com',
                'availabilities' => [
                    ['day' => 'Monday', 'start' => '09:00', 'end' => '15:00'],
                    ['day' => 'Wednesday', 'start' => '09:00', 'end' => '15:00'],
                    ['day' => 'Friday', 'start' => '09:00', 'end' => '15:00'],
                ]
            ],
        ];

        foreach ($therapists as $therapistData) {
            $therapist = User::create([
                'first_name' => $therapistData['first_name'],
                'middle_initial' => null,
                'last_name' => $therapistData['last_name'],
                'program' => 'N/A',
                'email' => $therapistData['email'],
                'date_of_birth' => '1985-05-20',
                'gender' => 'Male',
                'house_number' => '456',
                'barangay' => 'Barangay 2',
                'city_municipality' => 'Olongapo City',
                'phone_number' => '09987654321',
                'password' => Hash::make('password'),
                'role' => 'therapist',
                'email_verified_at' => now(),
            ]);

            // Attach specialization
            $therapist->specializations()->attach($specializationModels[$therapistData['specialization']]->id);

            // Create availabilities
            foreach ($therapistData['availabilities'] as $availability) {
                TherapistAvailability::create([
                    'therapist_id' => $therapist->id,
                    'day_of_week' => $availability['day'],
                    'start_time' => $availability['start'],
                    'end_time' => $availability['end'],
                ]);
            }
        }

        $this->command->info('Database seeded successfully!');
        $this->command->info('Student Email: student@example.com');
        $this->command->info('Password: password');
    }
}