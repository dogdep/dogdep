<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCommandsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('commands', function(Blueprint $table)
		{
			$table->increments('id');
            $table->unsignedInteger('repo_id');
            $table->unsignedInteger('order')->default(0);
			$table->enum('type', [
                'post-release',
                'pre-start',
                'post-start',
                'pre-stop',
                'post-stop',
                'pre-destroy',
            ]);
            $table->string('container');
            $table->string('command');
			$table->timestamps();

            $table->foreign('repo_id')->references('id')->on('repos');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('commands');
	}

}
