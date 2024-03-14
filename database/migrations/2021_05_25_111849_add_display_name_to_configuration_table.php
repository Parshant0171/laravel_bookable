<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDisplayNameToConfigurationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bookable_configurations', function (Blueprint $table) {
            //
            $table->string('name')->after('id');
            $table->string('display_name')->after('name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bookable_configurations', function (Blueprint $table) {
            //
            $table->dropColumn('name');
            $table->dropColumn('display_name');
        });
    }
}
