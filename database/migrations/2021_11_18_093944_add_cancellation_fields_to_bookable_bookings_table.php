<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCancellationFieldsToBookableBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bookable_bookings', function (Blueprint $table) {
            $table->dateTime('cancelled_at')->after('options')->nullable();
            $table->nullableMorphs('cancellable');
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
            $table->dropColumn('cancelled_at');
            $table->dropMorphs('cancellable');
        });
    }
}
