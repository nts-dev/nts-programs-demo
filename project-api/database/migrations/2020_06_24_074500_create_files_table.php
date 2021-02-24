<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('files', function (Blueprint $table) {

            $table->increments('id');
            $table->string('title',255);
            $table->unsignedInteger('size');
            $table->string('type',80);
            $table->text('path');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('document_id')->nullable()->index();
            $table->unsignedInteger('chapter_id')->nullable()->index();
            $table->unsignedTinyInteger('is_visible')->default(1);
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
        Schema::dropIfExists('files');
    }
}
