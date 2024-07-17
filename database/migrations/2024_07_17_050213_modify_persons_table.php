<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add 'note' column to the 'persons' table if it does not exist.
     */
    public function up(): void
    {
        if( Schema::hasTable('persons') && 
                !Schema::hasColumn('persons', 'note') 
        ){
            Schema::table('persons', function(Blueprint $table){
                $table->json('note')->nullable()
                    ->comment('Extra adatok')
                    ->after('birthdate');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * Drop 'note' column from the 'persons' table if it exists.
     */
    public function down(): void
    {
        if( Schema::hasTable('persons') && 
                Schema::hasColumn('persons', 'note') 
        ){
            // Drop 'note' column from the 'persons' table
            Schema::table('persons', function(Blueprint $table){
                $table->dropColumn('note');
            });
        }
    }
};
