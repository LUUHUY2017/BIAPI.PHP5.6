<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('locations', function(Blueprint $table){
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
            
            $table->integer('site_id')->unsigned()->nullable(false)->default(0);
            $table->string('location_name', 150)->nullable(false);
            $table->string('location_description', 250);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('locations');
    }
}
