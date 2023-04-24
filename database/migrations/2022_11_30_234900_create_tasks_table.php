<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->integer('points');
            $table->text('answer');
            $table->text('link')->nullable();
            $table->integer('level');
            $table->text('description');
            $table->text('wirte_up_title');
            $table->text('wirte_up_content');
            $table->text('wirte_up_thumbnail');
            $table->text('attachment');
            $table->unsignedBigInteger('category');
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
        Schema::dropIfExists('tasks');
    }
};
