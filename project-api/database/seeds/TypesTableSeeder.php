<?php

use Illuminate\Database\Seeder;

class TypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('types')->insert([
            [
                'id' => 1,
                'name' => 'Video'
            ],
            [
                'id' => 2,
                'name' => 'Audio'
            ],
            [
                'id' => 3,
                'name' => 'Moodle'
            ]
        ]);
    }
}
