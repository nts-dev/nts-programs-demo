<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('projects', function (Blueprint $table) {

            $table->increments('id');
            $table->unsignedInteger('parent_id')->default(0);
            $table->string('title', 255);
            $table->text('goal')->nullable();
            $table->text('input')->nullable();
            $table->text('output')->nullable();
            $table->text('scope')->nullable();
            $table->unsignedTinyInteger('is_published')->default(0);
            $table->unsignedTinyInteger('is_visible')->default(1);
            $table->unsignedInteger('user_id');
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
        Schema::dropIfExists('projects');
    }
}
