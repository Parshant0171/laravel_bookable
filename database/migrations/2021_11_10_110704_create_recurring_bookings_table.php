<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRecurringBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bookable_recurring_bookings', function (Blueprint $table) {
            $table->id();

            if(config('bookable.useTenants') == 1){
                $table->foreignId('tenant_id');            
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            }

            $table->morphs('bookable');

            $table->morphs('customer');

            $table->integer('no_of_seats')->default(1);

            $table->dateTimeTz('starts');            
            $table->dateTimeTz('ends');            

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

            $table->json('options')->nullable();

            $table->auditableWithDeletes();

            $table->timestampTz('created_at', $precision = 0)->useCurrent();
            $table->timestampTz('updated_at', $precision = 0)->useCurrent()->useCurrentOnUpdate();
            $table->softDeletes($column = 'deleted_at', $precision = 0);
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bookable_recurring_bookings');
    }
}
