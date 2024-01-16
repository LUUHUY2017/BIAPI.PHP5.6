<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePocDataInOutTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('poc_data_in_out', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';

            $table->increments('id');
            //$table->timestamps();
            $table->timestamp('created_at')->nullable(false)->useCurrent();
            $table->timestamp('updated_at')->nullable(false)->useCurrent();
            $table->integer('created_by')->nullable(false)->unsigned()->default(0);
            $table->integer('updated_by')->nullable(false)->unsigned()->default(0);
            $table->timestamp('recerviced_time')->nullable(false)->useCurrent();
            $table->integer('deleted')->nullable(false)->unsigned()->default(0);
            $table->integer('status')->nullable(false)->unsigned()->default(0);
            $table->boolean('actived')->default(true);

            $table->integer('organization_id')->unsigned()->nullable(false)->default(0);
            //$table->integer('company_id')->unsigned()->nullable(false)->default(0);
            $table->integer('location_id')->unsigned()->nullable(false)->default(0);

            $table->timestamp('start_time')->nullable(false)->useCurrent();
            $table->timestamp('end_time')->nullable(false)->useCurrent();
            
            $table->integer('num_to_enter')->nullable(false)->unsigned()->default(0);
            $table->integer('num_to_exit')->nullable(false)->unsigned()->default(0);
            $table->integer('traffic')->nullable(false)->unsigned()->default(0);

            $table->integer('num_adult_to_enter')->nullable(false)->unsigned()->default(0);
            $table->integer('num_adult_to_exit')->nullable(false)->unsigned()->default(0);
            $table->integer('adult_trafic')->nullable(false)->unsigned()->default(0);

            $table->integer('num_child_to_enter')->nullable(false)->unsigned()->default(0);
            $table->integer('num_child_to_exit')->nullable(false)->unsigned()->default(0);
            $table->integer('child_trafic')->nullable(false)->unsigned()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('poc_data_in_out');
    }
}
