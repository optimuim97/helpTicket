<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment_assignments', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->string('equipment_type');
            $table->string('equipment_model');
            $table->string('equipment_serial');
            $table->string('equipment_mac')->nullable();
            $table->string('agent_matricule');
            $table->string('agent_name');
            $table->string('agent_direction');
            $table->string('agent_department');
            $table->enum('operation_type', ['affectation', 'remplacement'])->default('affectation');
            $table->string('old_equipment_serial')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['brouillon', 'valide', 'signe'])->default('brouillon');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('created_by');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_assignments');
    }
};
