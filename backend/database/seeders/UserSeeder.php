<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Therapist account
        User::create([
            'first_name' => 'John',
            'middle_initial' => 'A',
            'last_name' => 'Doe',
            'program' => 'BSIT',
            'email' => 'john@gmail.com',
            'date_of_birth' => '1985-05-15',
            'gender' => 'Male',
            'house_number' => '123',
            'barangay' => 'Central',
            'city_municipality' => 'Metro City',
            'phone_number' => '09171234567',
            'image' => null,
            'password' => Hash::make('123'),
            'role' => 'therapist',
        ]);

        // Student account
        User::create([
            'first_name' => 'Jane',
            'middle_initial' => 'B',
            'last_name' => 'Smith',
            'program' => 'BSCS',
            'email' => 'jane@gmail.com',
            'date_of_birth' => '2002-11-20',
            'gender' => 'Female',
            'house_number' => '456',
            'barangay' => 'Westside',
            'city_municipality' => 'Metro City',
            'phone_number' => '09179876543',
            'image' => null,
            'password' => Hash::make('123'),
            'role' => 'student',
        ]);
    }
}
