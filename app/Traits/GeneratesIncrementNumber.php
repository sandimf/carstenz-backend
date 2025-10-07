<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait GeneratesIncrementNumber
{
    /**
     * Generate nomor unik aman dari bentrok
     *
     * @param  string  $table  Nama tabel
     * @param  string  $column  Kolom yang menyimpan nomor
     * @param  string  $prefix  Prefix nomor (contoh: MR, RJ)
     * @param  int  $padLength  Panjang digit angka
     * @return string
     */
    public static function generateNumber(string $table, string $column, string $prefix = '', int $padLength = 4)
    {
        return DB::transaction(function () use ($table, $column, $prefix, $padLength) {
            // Kunci table agar tidak ada proses lain baca/ubah saat ini
            $last = DB::table($table)
                ->select($column)
                ->where($column, 'like', $prefix.'%')
                ->lockForUpdate()
                ->orderByDesc('id')
                ->first();

            $lastNumber = 0;
            if ($last && $last->$column) {
                // Extract angka dari string (contoh: MR0357 -> 357)
                $lastNumber = (int) substr($last->$column, strlen($prefix));
            }

            // Buat nomor baru
            $newNumber = $prefix.str_pad($lastNumber + 1, $padLength, '0', STR_PAD_LEFT);

            return $newNumber;
        });
    }
}
