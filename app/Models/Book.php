<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Book extends Model
{
    use HasFactory, 
        SoftDeletes,
        LogsActivity;

    /**
     * A tömegesen hozzárendelhető attribútumok.
     *
     * @var array<string>
     */
    protected $fillable = [
        'title',  // A könyv címe
        'author', // A könyv szerzője
        'image',  // A könyv képe
    ];
    
    /**
     * A tevékenység naplózásakor naplózott attribútumok.
     *
     * @var array<string>
     */
    protected static $logAttributes = [
        'title',  // A könyv címe
        'author', // A könyv szerzője
        'image',  // A könyv képe
    ];
    
    /**
     * A tevékenységnaplózást kiváltó esemény(ek).
     *
     * @var array<string>
     */
    protected static $recordEvents = [
        'created', // Új könyv beillesztésekor
        'updated',  // Amikor egy meglévő könyvet frissítenek
        'deleted',  // Amikor egy könyvet törölnek
    ];

    /**
     * A naplózási tevékenységek nevének megadása.
     *
     * @var string
     */
    protected static $logName = 'Books';

    /**
     * A getActivitylogOptions metódus felülbírálása
     * 
     * Ezzel a módszerrel konfigurálhatóak a tevékenységnapló naplózási beállításai.
     * A tevékenységi napló létrehozásakor vagy frissítésekor hívják meg.
     *
     * @return LogOptions
     */
    public function getActivitylogOptions(): LogOptions
    {
        // Állítsa be az alapértelmezett naplózási beállításokat
        return LogOptions::defaults()
            ->useLogName(static::$logName)
            // Naplózza az összes kitölthető attribútumot
            ->logAll();
    }
}