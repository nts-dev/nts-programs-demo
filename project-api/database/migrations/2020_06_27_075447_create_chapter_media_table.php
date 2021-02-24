<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChapterMediaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chapter_media', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('chapter_id')->index();
            $table->unsignedInteger('media_id')->index();
            $table->timestamps();

            $table->unique(['chapter_id','media_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chapter_media');
    }
}
