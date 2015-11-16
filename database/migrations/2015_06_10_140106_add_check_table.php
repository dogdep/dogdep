<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddCheckTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('checks', function(Blueprint $table)
        {
            $table->increments('id');
            $table->unsignedInteger('repo_id');
            $table->string('container');
            $table->string('type');
            $table->string('params');
            $table->timestamps();

            $table->foreign('repo_id')->references('id')->on('repos');
        });
    }

    /**r
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('checks');
    }

}
