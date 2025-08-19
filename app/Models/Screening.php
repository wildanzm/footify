<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Screening extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'patient_id',
        'blood_sugar_type',
        'blood_sugar_value',
        'blood_sugar_status',
        'left_skin_score',
        'right_skin_score',
        'left_nails_score',
        'right_nails_score',
        'left_sensation_score',
        'right_sensation_score',
        'left_pain_score',
        'right_pain_score',
        'left_rubor_score',
        'right_rubor_score',
        'left_temperature_score',
        'right_temperature_score',
        'left_pedal_pulse_score',
        'right_pedal_pulse_score',
        'left_deformity_score',
        'right_deformity_score',
        'left_rom_score',
        'right_rom_score',
        'footwear_score',
        'total_score',
        'risk_classification',
        'recommendation',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     * Ini akan secara otomatis mengubah kolom 'recommendation' dari JSON di database
     * menjadi array di dalam kode PHP kita, dan sebaliknya.
     */
    protected $casts = [
        'recommendation' => 'array',
    ];

    /**
     * Mendefinisikan relasi: Setiap data skrining milik satu pasien.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }
}
