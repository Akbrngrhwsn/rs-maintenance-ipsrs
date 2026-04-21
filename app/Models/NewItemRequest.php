<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewItemRequest extends Model
{
    protected $fillable = [
        'user_id', 'room_id', 'purpose', 'items', 'status', 'reject_note',
        'qr_admin', 'qr_management', 'qr_bendahara', 'qr_direktur'
    ];

    protected $casts = [
        'items' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function getStatusLabelAttribute()
    {
        $map = [
            'pending_admin'      => 'Menunggu Validasi Admin IT',
            'pending_management' => 'Menunggu Konfirmasi Management',
            'pending_bendahara'  => 'Menunggu Cek Bendahara',
            'pending_director'   => 'Menunggu ACC Direktur',
            'approved'           => 'Disetujui',
            'rejected'           => 'Ditolak',
            'completed'          => 'Selesai',
        ];

        return $map[$this->status] ?? ucfirst(str_replace('_', ' ', $this->status));
    }
}
