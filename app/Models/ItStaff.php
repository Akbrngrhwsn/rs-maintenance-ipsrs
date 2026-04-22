<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItStaff extends Model
{
    protected $table = 'it_staffs';
    protected $fillable = ['nama', 'is_on_duty'];
    
    public function reports()
    {
        return $this->hasMany(Report::class);
    }
}
