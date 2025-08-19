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
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('date_of_birth');
            $table->tinyInteger('age');
            $table->enum('gender', ['Laki-laki', 'Perempuan']);
            $table->enum('last_education', [
                'Tidak Sekolah',
                'SD',
                'SMP',
                'SMA',
                'Diploma',
                'S1',
                'S2',
                'S3'
            ])->nullable();
            $table->string('occupation')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
