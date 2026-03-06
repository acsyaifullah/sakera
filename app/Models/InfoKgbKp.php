<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InfoKgbKp extends Model
{
    protected $table = 'tb_info_kgb_kp';

    // WAJIB: Daftar semua kolom agar bisa disimpan ke database
    protected $fillable = [
        'user_id', 'nama', 'nip', 'pangkat', 'golongan', 'tmt_cpns',
        'tmt_kgb_terakhir', 'tmt_kgb_selanjutnya', 'deadline_kgb', 'status_kgb',
        'tmt_kp_terakhir', 'tmt_kp_selanjutnya', 'deadline_kp', 'status_kp'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
}