<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class VolumesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('volumes', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('repo_id');
			$table->string('container');
			$table->string('volume');
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
		Schema::drop('volumes');
	}

}
