<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AppRequest extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $casts = [
        'requested_items' => 'array',
    ];

    // Relasi
    public function user() { return $this->belongsTo(User::class); }
    public function features() { return $this->hasMany(AppFeature::class); }

    // Hitung Progress
    public function getProgressAttribute() {
        $total = $this->features()->count();
        if ($total == 0) return 0;
        $done = $this->features()->where('is_done', true)->count();
        return round(($done / $total) * 100);
    }

    // Status label untuk app request approval
    public function getStatusLabelAttribute() {
        $map = [
            'submitted_to_admin' => 'Menunggu Konfirmasi Admin IT',
            'submitted_to_management' => 'Menunggu Konfirmasi Management',
            'submitted_to_bendahara' => 'Menunggu Konfirmasi Bendahara',
            'submitted_to_director' => 'Menunggu Persetujuan Direktur',
            'pending_director' => 'Menunggu Direktur',
            'approved' => 'Disetujui',
            'in_progress' => 'Sedang Dikerjakan',
            'completed' => 'Selesai',
            'rejected' => 'Ditolak',
            'draft' => 'Draft',
        ];
        return $map[$this->status] ?? ucfirst(str_replace('_', ' ', $this->status));
    }

    // Status label untuk procurement approval (jika ada pengadaan)
    public function getProcurementApprovalStatusLabelAttribute() {
        $map = [
            'pending' => 'Belum Diajukan',
            'submitted_to_management' => 'Menunggu Management',
            'submitted_to_bendahara' => 'Menunggu Bendahara',
            'submitted_to_director' => 'Menunggu Direktur',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
        ];
        return $map[$this->procurement_approval_status] ?? ucfirst(str_replace('_', ' ', $this->procurement_approval_status));
    }

    // === TAMBAHAN: Auto Generate Ticket ===
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $dateCode = date('Ymd');
            // Format: APP-20260109-01 (Urut harian)
            $countToday = static::whereDate('created_at', now())->count();
            $sequence = str_pad($countToday + 1, 2, '0', STR_PAD_LEFT);
            $model->ticket_number = "APP-{$dateCode}-{$sequence}";
        });
    }
}