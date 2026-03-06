<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('tb_info_kgb_kp', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('nama');
            $table->string('nip');
            $table->string('pangkat')->nullable();
            $table->string('golongan')->nullable();
            $table->date('tmt_cpns')->nullable();
            
            // Bagian KGB
            $table->date('tmt_kgb_terakhir')->nullable();
            $table->date('tmt_kgb_selanjutnya')->nullable();
            $table->date('deadline_kgb')->nullable();
            $table->string('status_kgb')->nullable();

            // Bagian KP
            $table->date('tmt_kp_terakhir')->nullable();
            $table->date('tmt_kp_selanjutnya')->nullable();
            $table->date('deadline_kp')->nullable();
            $table->string('status_kp')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_info_kgb_kp');
    }
};
