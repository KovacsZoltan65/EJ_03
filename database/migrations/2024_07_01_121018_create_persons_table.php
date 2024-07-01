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
        Schema::create('persons', function (Blueprint $table) {
            $table->id()->comment('Rekord azonosító');

            $table->string('name')->comment('Név');
            $table->string('email')->comment('E-mail cím');
            $table->string('password')->comment('Jelszó');
            $table->string('language', 5)->default('hu')->comment('Nyelv');
            $table->date('birthdate')->nullable()->comment('Születési dátum');
            

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('persons');
    }
};
