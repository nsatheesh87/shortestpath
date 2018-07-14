<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTokenTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('token', function (Blueprint $table) {
            $table->increments('id');
            $table->char('token', 100)->unique()->nullable();
            $table->text('request')->nullable();
            $table->enum('status', ['success', 'in progress', 'failure'])->default('in progress');
            $table->text('path')->nullable();
            $table->integer('total_distance')->nullable();
            $table->integer('total_time')->nullable();
            $table->text('error')->nullable();
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
        Schema::dropIfExists('token');
    }
}
