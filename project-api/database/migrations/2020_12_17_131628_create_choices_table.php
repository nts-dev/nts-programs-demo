<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('choices', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('question_id')->nullable();
            $table->bigInteger('moodle_id')->nullable();
            $table->text('text')->nullable();
            $table->bigInteger('jumpto')->default(-1);
            $table->smallInteger('grade')->default(0);
            $table->bigInteger('score')->default(0);
            $table->longText('response')->nullable();
            $table->tinyInteger('responseformat')->default(1);
            $table->unsignedTinyInteger('is_updated')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('choices');
    }
}
