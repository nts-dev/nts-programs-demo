<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMediaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('media', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('parent_id')->index()->default(0);
            $table->unsignedInteger('sort_id')->index()->nullable();
            $table->string('file_name',255);
            $table->string('extension',10);
            $table->unsignedInteger('size')->nullable();
            $table->string('type',50);
            $table->text('path');
            $table->string('start_time',80)->nullable();
            $table->string('end_time',80)->nullable();
            $table->unsignedInteger('user_id');
            $table->unsignedTinyInteger('is_new')->default(1);
            $table->unsignedInteger('project_id')->index();
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
        Schema::dropIfExists('media');
    }
}
