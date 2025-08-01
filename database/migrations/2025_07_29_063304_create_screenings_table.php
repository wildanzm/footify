<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('screenings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();

            // Data Gula Darah
            $table->enum('blood_sugar_type', ['gds', 'gdp', 'hba1c']);
            $table->float('blood_sugar_value');
            $table->string('blood_sugar_status');

            // --- Skor Inlow’s Screen (Lengkap & Sesuai Form) ---

            // Tab 2: Kulit & Kuku
            $table->tinyInteger('left_skin_score');
            $table->tinyInteger('right_skin_score');
            $table->tinyInteger('left_nails_score');
            $table->tinyInteger('right_nails_score');

            // Tab 3: Sensasi
            $table->tinyInteger('left_sensation_score');
            $table->tinyInteger('right_sensation_score');

            // Tab 4: Arteri Perifer
            $table->tinyInteger('left_pain_score');
            $table->tinyInteger('right_pain_score');
            $table->tinyInteger('left_rubor_score');
            $table->tinyInteger('right_rubor_score');
            $table->tinyInteger('left_temperature_score');
            $table->tinyInteger('right_temperature_score');
            $table->tinyInteger('left_pedal_pulse_score');
            $table->tinyInteger('right_pedal_pulse_score');

            // Tab 5: Kelainan Bentuk & Alas Kaki
            $table->tinyInteger('left_deformity_score');
            $table->tinyInteger('right_deformity_score');
            $table->tinyInteger('left_rom_score');
            $table->tinyInteger('right_rom_score');
            $table->tinyInteger('footwear_score');

            // Hasil Akhir & Rekomendasi
            $table->integer('total_score');
            $table->string('risk_classification');
            $table->json('recommendation');
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('screenings');
    }
};
