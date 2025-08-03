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
        Schema::create('registered_members', function (Blueprint $table) {
            $table->id();
            $table->string("account_number");
            $table->string("book");
            $table->string("name");
            $table->string("address");
            $table->string("occupant")->nullable();
            $table->string("id_presented")->nullable();
            $table->string("id_number")->nullable();
            $table->string("phone_number")->nullable();
            $table->string("email")->nullable();
            $table->string("created_by")->nullable();
            $table->string("status")->nullable();
            $table->string("reference_number")->nullable();
            $table->string("registration_method")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registered_members');
    }
};
