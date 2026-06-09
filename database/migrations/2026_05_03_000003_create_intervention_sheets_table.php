<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('intervention_sheets', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->string('site');
            $table->string('building');
            $table->string('agent_name');
            $table->text('reported_fault');
            $table->text('observation')->nullable();
            $table->enum('incidence', ['critique', 'majeur', 'mineur'])->default('mineur');
            $table->json('concerned_services')->nullable();
            $table->text('work_done')->nullable();
            $table->text('supplies_used')->nullable();
            $table->datetime('start_date');
            $table->datetime('end_date')->nullable();
            $table->json('epi_used')->nullable();
            $table->tinyInteger('client_satisfaction')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['brouillon', 'valide', 'signe'])->default('brouillon');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('incidence');
            $table->index('created_by');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intervention_sheets');
    }
};
