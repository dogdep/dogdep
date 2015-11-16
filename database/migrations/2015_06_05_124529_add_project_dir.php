<?php

use Illuminate\Database\Migrations\Migration;

class AddProjectDir extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::table('repos', function($table)
        {
            $table->string('group')->nullable();
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::table('repos', function($table)
        {
            $table->dropColumn('group');
        });
	}

}
