<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAllowBookingBeforeXHoursFieldToBookableConfigurationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bookable_configurations', function (Blueprint $table) {
            $table->integer('open_booking_before_start_time_minutes')->after('allow_booking_before_start_time_minutes')->nullable();
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
            $table->dropColumn('open_booking_before_start_time_minutes');
        });
    }
}
