<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropRecurringColumnsFromBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bookable_bookings', function (Blueprint $table) {            
            $table->dropColumn('is_recurring')->default(0);            
            
            $table->dropColumn('recurring_start_time')->nullable();
            $table->dropColumn('recurring_end_time')->nullable();

            $table->dropColumn('reccurs_monday')->default(0);
            $table->dropColumn('reccurs_tuesday')->default(0);
            $table->dropColumn('reccurs_wednesday')->default(0);
            $table->dropColumn('reccurs_thursday')->default(0);
            $table->dropColumn('reccurs_friday')->default(0);
            $table->dropColumn('reccurs_saturday')->default(0);
            $table->dropColumn('reccurs_sunday')->default(0);
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
            $table->tinyInteger('is_recurring')->default(0);            
            
            $table->timeTz('recurring_start_time')->nullable();
            $table->timeTz('recurring_end_time')->nullable();

            $table->tinyInteger('reccurs_monday')->default(0);
            $table->tinyInteger('reccurs_tuesday')->default(0);
            $table->tinyInteger('reccurs_wednesday')->default(0);
            $table->tinyInteger('reccurs_thursday')->default(0);
            $table->tinyInteger('reccurs_friday')->default(0);
            $table->tinyInteger('reccurs_saturday')->default(0);
            $table->tinyInteger('reccurs_sunday')->default(0);
        });
    }
}
