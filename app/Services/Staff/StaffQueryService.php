<?php

namespace App\Services\Staff;

use App\Models\Users\Admin;
use App\Models\Users\Cashier;
use App\Models\Users\Doctor;
use App\Models\Users\Nurse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class StaffQueryService
{
    /**
     * @var array<int, class-string<Model>>
     */
    protected array $staffModels = [
        Doctor::class,
        Cashier::class,
        Nurse::class,
        Admin::class,
    ];

    /**
     * Mengambil semua staf dari berbagai tabel peran, menggabungkannya, dan memformatnya.
     */
    public function getAllStaff(): Collection
    {
        $allStaffCollections = collect($this->staffModels)->map(function ($modelClass) {
            return $modelClass::with('user')->get();
        });

        $mergedStaff = $allStaffCollections->flatten();

        $formattedStaff = $mergedStaff->map(function (Model $staffMember) {
            return $this->formatStaffData($staffMember);
        });

        return $formattedStaff->sortBy('name')->values();
    }

    /**
     * Formatter terpusat untuk model staf mana pun.
     * Membuat penambahan peran baru menjadi mudah tanpa duplikasi kode.
     *
     * @param  Model&\Illuminate\Database\Eloquent\Relations\BelongsTo  $staffMember
     */
    private function formatStaffData(Model $staffMember): array
    {
        return [
            'uuid' => $staffMember->uuid,
            'id' => $staffMember->id,
            'nik' => $staffMember->nik,
            'role' => $staffMember->role,
            'user_id' => $staffMember->user_id,
            'name' => $staffMember->name,
            'email' => $staffMember->user->email,
            'address' => $staffMember->address,
            'date_of_birth' => $staffMember->date_of_birth,
            'phone' => $staffMember->phone,
            'signature' => $staffMember->signature,
            'created_at' => $staffMember->created_at,
            'updated_at' => $staffMember->updated_at,
        ];
    }
}
