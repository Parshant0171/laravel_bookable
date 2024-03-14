<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMappableToBookableConfigurationslotsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bookable_configuration_slots', function (Blueprint $table) {
            $table->morphs('mappable');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bookable_configuration_slots', function (Blueprint $table) {
            $table->dropMorphs('mappable');	
        });
    }
}
