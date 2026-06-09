<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipment_assignment_validations', function (Blueprint $table) {
            $table->longText('signature')->nullable()->after('comment');
        });

        Schema::table('intervention_sheet_validations', function (Blueprint $table) {
            $table->longText('signature')->nullable()->after('comment');
        });
    }

    public function down(): void
    {
        Schema::table('equipment_assignment_validations', function (Blueprint $table) {
            $table->dropColumn('signature');
        });
        Schema::table('intervention_sheet_validations', function (Blueprint $table) {
            $table->dropColumn('signature');
        });
    }
};
