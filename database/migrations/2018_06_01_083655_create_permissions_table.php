<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('permissions', function(Blueprint $table){
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';

            //$table->increments('id');
            $table->integer('id')->nullable(false)->unsigned()->default(0);;
            $table->primary('id');
            //$table->timestamps();
            $table->timestamp('created_at')->nullable(false)->useCurrent();
            $table->timestamp('updated_at')->nullable(false)->useCurrent();
            $table->timestamp('recerviced_time')->nullable(false)->useCurrent();
            $table->integer('deleted')->nullable(false)->unsigned()->default(0);
            $table->integer('status')->nullable(false)->unsigned()->default(0);
            $table->boolean('actived')->default(true);
            $table->integer('created_by')->nullable(false)->unsigned()->default(0);
            $table->integer('updated_by')->nullable(false)->unsigned()->default(0);
            
            $table->string('permission_name', 150)->unique();
            $table->string('permission_detail');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('permissions');
    }
}
