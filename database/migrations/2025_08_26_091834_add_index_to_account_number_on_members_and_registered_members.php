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
            $table->index('account_number');
        });

        Schema::table('registered_members', function (Blueprint $table) {
            $table->index('account_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropIndex(['account_number']);
        });

        Schema::table('registered_members', function (Blueprint $table) {
            $table->dropIndex(['account_number']);
        });
    }
};
