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
        DB::unprepared('DROP TRIGGER IF EXISTS books_after_delete' );
        
        \DB::unprepared("
            CREATE DEFINER = 'root'@'localhost'
            TRIGGER ej_03.books_after_delete AFTER DELETE ON ej_03.books FOR EACH ROW
            BEGIN
                IF @need_log = 1 THEN
                    INSERT INTO change_log
                    (  id, table_name, operation, record_id, old_data, new_data, change_date) VALUES
                    (NULL, 'books', 'UPDATE', OLD.id, JSON_OBJECT('id', OLD.id, 'title', OLD.title, 'author', OLD.author, 'image', OLD.image), NULL, NOW());
                END IF;
            END
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS books_after_delete' );
    }
};
