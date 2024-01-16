<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePocCustomerBehaviorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('poc_customer_behavior', function (Blueprint $table) {
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

            $table->integer('new_visit')->nullable(false)->unsigned()->default(0);
            $table->integer('member')->nullable(false)->unsigned()->default(0);
            $table->integer('none_member')->nullable(false)->unsigned()->default(0);
            $table->integer('sales_person')->nullable(false)->unsigned()->default(0);
            $table->integer('repeated_visitor')->nullable(false)->unsigned()->default(0);
            $table->integer('effective_traffic')->nullable(false)->unsigned()->default(0);
            $table->integer('male')->nullable(false)->unsigned()->default(0);
            $table->integer('female')->nullable(false)->unsigned()->default(0);
            $table->integer('age')->nullable(false)->unsigned()->default(0);


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('poc_customer_behavior');
    }
}
