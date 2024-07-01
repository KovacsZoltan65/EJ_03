<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PersonsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \DB::table('persons')->truncate();
        
        $this->command->warn(PHP_EOL . 'Creating persons...');
        
        $count = 1000;
        
        $this->command->getOutput()->progressStart($count);
        
        for( $i = 0; $i < $count; $i++ )
        {
            \App\Models\Person::factory(1)->create();
            
            $this->command->getOutput()->progressAdvance();
        }
        
        $this->command->info(PHP_EOL . 'Persons created');
    }
}
