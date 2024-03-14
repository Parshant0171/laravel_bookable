<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCompletelyOpenEndedBookingFlagToConfigTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bookable_configurations', function (Blueprint $table) {
            $table->tinyInteger('completely_open_ended')->default(0)->after('uses_approvals'); //for make my trip type hotel bookings
            $table->double('tax_percentage')->default(0)->after('unit_price');
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
            $table->dropColumn('completely_open_ended');
            $table->dropColumn('tax_percentage');
        });
    }
}
