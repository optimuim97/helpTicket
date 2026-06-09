<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment_assignment_validations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_assignment_id')
                ->constrained('equipment_assignments')
                ->cascadeOnDelete();
            $table->enum('validator_role', ['chef_atelier', 'utilisateur', 'chef_service']);
            $table->string('validator_name')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->unique(['equipment_assignment_id', 'validator_role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_assignment_validations');
    }
};
