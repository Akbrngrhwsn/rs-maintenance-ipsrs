<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppFeature extends Model
{
    protected $fillable = ['app_request_id', 'nama_fitur', 'is_done', 'completed_at'];
    
    // Casting agar completed_at dibaca sebagai Carbon date
    protected $casts = [
        'completed_at' => 'datetime',
        'is_done' => 'boolean',
    ];
}