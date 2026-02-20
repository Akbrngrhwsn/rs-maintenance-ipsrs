<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'kepala_ruang_id'];

    public function kepala_ruang()
    {
        return $this->belongsTo(User::class, 'kepala_ruang_id');
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }
}
