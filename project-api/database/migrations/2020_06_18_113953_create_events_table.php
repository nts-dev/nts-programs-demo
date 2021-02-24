<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {

            $table->increments('id');
            $table->string('title', 255);
            $table->text('details')->nullable();
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->unsignedInteger('parent_id')->default(0);
            $table->unsignedInteger('frequency')->default(0);
            $table->unsignedInteger('is_variable')->default(0);
            $table->unsignedTinyInteger('is_visible')->default(1);
            $table->unsignedTinyInteger('status')->default(0);
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('project_id')->nullable()->index();
            $table->unsignedInteger('document_id')->nullable()->index();
            $table->unsignedInteger('chapter_id')->nullable()->index();
            $table->text('comments')->nullable();
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
        Schema::dropIfExists('events');
    }
}
