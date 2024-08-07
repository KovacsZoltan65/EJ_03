<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;
    
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        \DB::unprepared('SET @need_log = 1;  -- Naplózás engedélyezése');
        
        $this->call([
            UserSeeder::class,              // OK
            BookSeeder::class,              // OK
            PostSeeder::class,              // OK
            
            RoleSeeder::class,              // OK
            PermissionSeeder::class,        // OK
            ModelHasRolesTableSeeder::class,
            ModelHasPermissionTableSeeder::class,   // OK
            RoleHasPermissionsTableSeeder::class,   // OK
            
            ComponentsTableSeeder::class,   // OK
            HqSettingsTableSeeder::class,   // OK
            LogsTableSeeder::class,
            MirrorStatusesTableSeeder::class,
            ScheduledShutdownsTableSeeder::class,
            SeedsTableSeeder::class,
            SubdomainComponentsTableSeeder::class,
            SubdomainsTableSeeder::class,   // OK

            //RegionsTableSeeder::class,
            //CountriesTableSeeder::class
            //CitiesTableSeeder::class,
            PersonsSeeder::class,
        ]);
        
        //\DB::unprepared('SET @need_log = 0;  -- Naplózás letiltása');
    }
}
