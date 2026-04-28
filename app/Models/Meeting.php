<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Meeting extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'meeting_date',
        'minutes',
        'division_role',
        'created_by',
        'edited_by',
    ];

    // Fungsi ini yang kita panggil di Blade: $m->user->name
    public function user()
    {
        // Cukup tulis 'created_by' saja
        return $this->belongsTo(User::class, 'created_by'); 
    }

    // Fungsi alternatif (opsional, biarkan saja jika sewaktu-waktu dipakai)
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'edited_by');
    }
}