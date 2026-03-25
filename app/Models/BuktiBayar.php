<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BuktiBayar extends Model
{
    use HasFactory;

    protected $table = 'bukti_bayar';

    protected $fillable = [
        'transaksi_id',
        'file_path',
        'file_type',
        'uploaded_by',
        'uploaded_at',
        'verified_status',
        'verified_notes',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
    ];

    /**
     * Get the transaction
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaksi::class, 'transaksi_id');
    }

    /**
     * Get the user who uploaded
     */
    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
