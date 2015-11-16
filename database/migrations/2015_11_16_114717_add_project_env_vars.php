<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddProjectEnvVars extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('repos', function(Blueprint $table)
        {
            $table->json('env')->nullable();
        });
    }

    /**r
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('repos', function(Blueprint $table)
        {
            $table->dropColumn('env');
        });
    }
}
