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
        Schema::create('change_log', function(Blueprint $table){
            $table->id('id')->comment('Rekord azonosító');
            $table->string('table_name', 255)->comment('tábla neve');
            $table->string('operation', 255)->comment('Művelet');
            $table->integer('record_id')->comment('Módosított rekord azonosítója');
            $table->json('old_data')->nullable()->comment('Régi adat');
            $table->json('new_data')->nullable()->comment('Új adat');
            $table->timestamp('change_date')->comment('Módosítás dátuma');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('change_log');
    }
};
