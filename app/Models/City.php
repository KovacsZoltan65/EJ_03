<?php

/**
 * Városok
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Class City
 * 
 * @property int $id
 * @property int $region_id
 * @property int $country_id
 * @property float $latitude
 * @property float $longitude
 * @property string $name
 *
 * @package App\Models
 */
class City extends Model
{
    use HasFactory,
        LogsActivity;

    protected $table = 'cities';
    public $timestamps = false;

    protected $casts = [
        'region_id' => 'int',
        'country_id' => 'int',
        'latitude' => 'float',
        'longitude' => 'float'
    ];

    protected $fillable = [
        'region_id',
        'country_id',
        'latitude',
        'longitude',
        'name'
    ];

    protected static $logAttributes = [
        'region_id',
        'country_id',
        'latitude',
        'longitude',
        'name'
    ];

    /**
     * A tevékenységnaplózást kiváltó esemény(ek).
     *
     * @var array<string>
     */
    protected static $recordEvents = [
        'created',
        'updated',
        'deleted',
    ];

    /**
     * A getActivitylogOptions metódus felülbírálása
     * 
     * Ezzel a módszerrel konfigurálhatóak a tevékenységnapló naplózási beállításai.
     * A tevékenységi napló létrehozásakor vagy frissítésekor hívják meg.
     *
     * @return LogOptions
     */
    #[\Override]
    public function getActivitylogOptions(): LogOptions
    {
        // Állítsa be az alapértelmezett naplózási beállításokat
        return LogOptions::defaults()
            // Naplózza az összes kitölthető attribútumot
            ->logFillable()
            // Minden esemény naplózása
            ->logAllEvents();
    }
}
