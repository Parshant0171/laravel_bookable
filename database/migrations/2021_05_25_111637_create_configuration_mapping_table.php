<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConfigurationMappingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bookable_configuration_maps', function (Blueprint $table) {
            $table->id();
            if(config('bookable.useTenants') == 1){
                $table->foreignId('tenant_id');            
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            }

            $table->foreignId('bookable_configuration_id');            
            $table->foreign('bookable_configuration_id')->references('id')->on('bookable_configurations')->onDelete('cascade');            

            $table->morphs('mappable');
            
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
        Schema::dropIfExists('bookable_configuration_maps');
    }
}
