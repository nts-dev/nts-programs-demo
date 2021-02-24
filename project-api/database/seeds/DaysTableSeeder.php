<?php

use Illuminate\Database\Seeder;

class DaysTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('days')->insert([
            [
                'id' => 1,
                'name' => 'Mon'
            ],
            [
                'id' => 2,
                'name' => 'Tue'
            ],
            [
                'id' => 3,
                'name' => 'Wed'
            ],
            [
                'id' => 4,
                'name' => 'Thu'
            ],
            [
                'id' => 5,
                'name' => 'Fri'
            ],
            [
                'id' => 6,
                'name' => 'Sat'
            ],
            [
                'id' => 7,
                'name' => 'Sun'
            ],
        ]);
    }
}
