<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsFinalApprovedToBookableBookings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bookable_bookings', function (Blueprint $table) {
            $table->tinyInteger('is_finally_approved')->default(0)->after('cancelled_at');
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
            $table->dropColumn('is_finally_approved');
        });
    }
}
