<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    /**
     * Menentukan kolom mana saja yang boleh diisi (Mass Assignment)
     * Ditambahkan kolom 'period' dan 'quarter' untuk mendukung multi-file SKP/SPT
     */
    protected $fillable = [
        'user_id', 
        'category', 
        'title', 
        'doc_title',
        'training_hours',
        'file_path', 
        'status', 
        'admin_note',
        'period',
        'quarter'
    ];

    /**
     * Relasi ke User (Satu dokumen dimiliki oleh satu user)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}