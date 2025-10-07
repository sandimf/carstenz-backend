<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $userId = DB::table('users')->insertGetId([
            'uuid' => (string) Str::uuid(),
            'username' => 'doctor',
            'name' => 'doctor',
            'email' => 'doctor@kun.or.id',
            'role' => 'doctor',
            'password' => Hash::make('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('doctors')->insert([
            'uuid' => (string) Str::uuid(),
            'user_id' => $userId,
            'nik' => '1234567890',
            'email' => 'doctor@kun.or.id',
            'name' => 'Doctor',
            'address' => 'Jl. x No.1',
            'date_of_birth' => '1990-01-01',
            'phone' => '081234567890',
            'role' => 'doctor',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

    }
}
