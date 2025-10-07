<?php

namespace App\Services\Staff;

use App\Models\User;
use App\Models\Users\Admin;
use App\Models\Users\Cashier;
use App\Models\Users\Doctor;
use App\Models\Users\FrontOffice;
use App\Models\Users\Nurse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StaffCreationService
{
    /**
     * @var array<string, class-string>
     */
    protected array $classMap = [
        'doctor' => Doctor::class,
        'nurse' => nurse::class,
        'cashier' => Cashier::class,
        'admin' => Admin::class,
        'frontoffice' => FrontOffice::class,
    ];

    public function createStaff(array $data): void
    {
        $role = $data['role'];

        DB::transaction(function () use ($data, $role) {
            $plainPassword = $data['password'];

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($plainPassword),
                'email_verified_at' => now(),
                'role' => $role,
            ]);

            $personnelData = [
                'user_id' => $user->id,
                'nik' => $data['nik'],
                'email' => $data['email'],
                'name' => $data['name'],
                'address' => $data['address'],
                'date_of_birth' => $data['date_of_birth'],
                'phone' => $data['phone'],
                'role' => $role,
                'signature' => $data['signature'] ?? null,
            ];

            $this->classMap[$role]::create($personnelData);

            // SendStaffCredentialsEmail::dispatch($user, $plainPassword);
        });
    }
}
