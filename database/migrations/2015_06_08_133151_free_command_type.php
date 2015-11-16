<?php

use Illuminate\Database\Migrations\Migration;

class FreeCommandType extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::table('commands', function($table)
        {
            $table->dropColumn('type');
        });
        Schema::table('commands', function($table)
        {
            $table->string('type', 50)->default('post-release');
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		//
	}

}
