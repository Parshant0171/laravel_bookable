<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConfigurationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bookable_configurations', function (Blueprint $table) {
            $table->id();

            if(config('bookable.useTenants') == 1){
                $table->foreignId('tenant_id');            
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            }

            $table->string('model_path');
            
            $table->tinyInteger('uses_slots')->default(0);

            $table->tinyInteger('max_ongoing_bookings')->default(1)->comment('how many active/ongoing bookings can a user have');
            $table->tinyInteger('max_parallel_bookings')->default(1)->comment('how many bookings can a user do in one go');

            $table->tinyInteger('uses_payment')->default(0);

            $table->float('unit_price')->default(0.00);

            $table->tinyInteger('uses_approvals')->default(0);

            $table->tinyInteger('allow_seatwise_booking')->default(0);

            $table->tinyInteger('allow_reccuring_booking')->default(0);

            $table->integer('allow_booking_before_start_time_minutes')->nullable();
            $table->integer('allow_cancellation_before_start_time_minutes')->nullable();

            $table->integer('minimum_booking_time_minutes')->nullable();
            $table->integer('maximum_booking_time_minutes')->nullable();

            $table->tinyInteger('allow_bookings')->default(0);
            $table->tinyInteger('allow_cancellation')->default(0);

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
        Schema::dropIfExists('bookable_configurations');
    }
}
