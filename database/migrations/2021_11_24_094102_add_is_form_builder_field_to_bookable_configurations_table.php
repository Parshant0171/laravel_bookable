<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsFormBuilderFieldToBookableConfigurationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bookable_configurations', function (Blueprint $table) {
            $table->tinyInteger('is_form_builder')->after('uses_slots')->default(0);
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
            $table->dropColumn('is_form_builder');
        });
    }
}
