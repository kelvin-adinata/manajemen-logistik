<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OaPayment extends Model
{
    protected $table = 'oa_payments';

    protected $fillable = [
        'po_item_id', 'po_penerima_id', 'po_kendaraan_id', 'supplier_id',
        'tipe_pembayaran', 'jumlah_tagihan', 'jumlah_bayar', 'tanggal_bayar',
        'metode_bayar', 'bukti_bayar', 'keterangan', 'status',
    ];

    protected $casts = [
        'tanggal_bayar' => 'date',
        'jumlah_tagihan' => 'decimal:2',
        'jumlah_bayar' => 'decimal:2',
    ];

    const TIPE = [
        'oa' => 'Pembayaran OA',
        'dp_supplier' => 'Down Payment Supplier',
        'pt_sum' => 'Pembayaran PT Sum',
    ];

    const METODE = [
        'transfer' => 'Transfer Bank',
        'tunai' => 'Tunai',
        'cek' => 'Cek / Giro',
        'giro' => 'Giro',
    ];

    const STATUS = [
        'pending' => 'Belum Bayar',
        'partial' => 'Bayar Sebagian',
        'lunas' => 'Lunas',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderItem::class, 'po_item_id');
    }

    public function penerima(): BelongsTo
    {
        return $this->belongsTo(PoPenerima::class, 'po_penerima_id');
    }

    public function kendaraan(): BelongsTo
    {
        return $this->belongsTo(PoKendaraan::class, 'po_kendaraan_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function getSisaTagihanAttribute(): float
    {
        if (! $this->po_item_id) {
            return max(0, (float) $this->jumlah_tagihan - (float) $this->jumlah_bayar);
        }

        $payments = static::query()->where('po_item_id', $this->po_item_id);
        $jumlahTagihan = (float) ((clone $payments)->orderBy('id')->value('jumlah_tagihan') ?? 0);
        $jumlahBayar = (float) $payments->sum('jumlah_bayar');

        return max(0, $jumlahTagihan - $jumlahBayar);
    }
}
