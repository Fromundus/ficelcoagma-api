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
        Schema::table('members', function (Blueprint $table) {
            $table->string("occupant_2")->after('occupant')->nullable();
            $table->string("id_presented_2")->after('id_presented')->nullable();
            $table->string("id_number_2")->after('id_number')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn(['occupant_2', 'id_presented_2', 'id_number_2']);
        });
    }
};
