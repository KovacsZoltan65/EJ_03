<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Person extends Model
{
    use HasFactory,
        SoftDeletes,
        LogsActivity;
    
    /**
     * The name of the table associated with the model.
     *
     * @var string
     */
    protected $table = 'persons';
    
    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    //protected $primaryKey = 'id';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        // The name of the person
        'name',

        // The email address of the person
        'email',

        // The password of the person
        'password',

        // The language preference of the person
        'language',

        // The birthdate of the person
        'birthdate',
    ];
    
    /**
     * The model's default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        // The name of the person
        'name' => '', 
        
        // The email address of the person
        'email' => '', 
        
        // The password of the person
        'password' => '', 
        
        // The preferred language of the person.
        // Defaults to 'hu' (Hungarian).
        'language' => 'hu', 
        
        // The birthdate of the person.
        // Defaults to an empty string.
        'birthdate' => '',
    ];
    
    /**
     * The attributes that should be logged.
     *
     * @var array<string>
     */
    protected static $logAttributes = [
        // The name of the person
        'name',

        // The email address of the person
        'email',

        // The password of the person
        'password',

        // The language preference of the person
        'language',

        // The birthdate of the person
        'birthdate',
    ];
    
    /**
     * A tevékenységnaplózást kiváltó esemény(ek).
     *
     * @var array<string>
     */
    protected static $recordEvents = [
        // Log when a person is created
        'created',

        // Log when a person is updated
        'updated',

        // Log when a person is deleted
        'deleted',
    ];
    
    /**
     * A naplózási tevékenységek nevének megadása.
     *
     * @var string
     */
    protected static $logName = 'Persons';
    
    /**
     * Csak a megváltozott attribútumokat naplózza.
     *
     * @var bool
     */
    protected static $logOnlyDirty = true;
    
    /**
     * A getActivitylogOptions metódus felülbírálása.
     *
     * Ezzel a módszerrel konfigurálhatóak a tevékenységnapló naplózási beállításai.
     * A tevékenységi napló létrehozásakor vagy frissítésekor hívják meg.
     *
     * @return LogOptions
     */
    #[\Override]
    public function getActivitylogOptions(): LogOptions {
        // Állítsa be az alapértelmezett naplózási beállításokat
        return LogOptions::defaults()
            // Naplózza az összes kitölthető attribútumot
            ->logAll();
    }
}
