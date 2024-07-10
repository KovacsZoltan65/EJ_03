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
        \DB::unprepared('DROP TRIGGER IF EXISTS books_after_insert' );
        
        \DB::unprepared("
            CREATE DEFINER = 'root'@'localhost'
            TRIGGER ej_03.books_after_insert AFTER INSERT ON ej_03.books FOR EACH ROW
            BEGIN
                IF @need_log = 1 THEN
                    INSERT INTO change_log(id, table_name, operation, record_id, old_data, new_data, change_date) 
                    VALUES(NULL, 'books', 'INSERT', NEW.id, 
                        NULL, 
                        JSON_OBJECT('id', NEW.id, 'title', NEW.title, 'author', NEW.author, 'image', NEW.image), NOW()
                    );
                END IF;
            END
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \DB::unprepared('DROP TRIGGER IF EXISTS books_after_insert' );
    }
};
