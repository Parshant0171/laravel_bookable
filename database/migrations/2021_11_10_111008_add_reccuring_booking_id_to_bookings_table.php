<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReccuringBookingIdToBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bookable_bookings', function (Blueprint $table) {            
            $table->foreignId('bookable_recurring_booking_id')->after('bookable_configuration_slot_id')->nullable();            
            $table->foreign('bookable_recurring_booking_id')->references('id')->on('bookable_recurring_bookings')->onDelete('cascade');            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bookable_bookings', function (Blueprint $table) {
            $table->dropColumn('bookable_recurring_booking_id');
        });
    }
}
