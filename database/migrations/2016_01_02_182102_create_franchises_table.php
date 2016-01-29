<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFranchisesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('franchises', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('franchisor_id')->unsigned()->nullable();
            $table->integer('jenis')->unsigned()->nullable();
            $table->string('logo');
            $table->string('namausaha', 30);
            $table->timestamps();
            $table->foreign('franchisor_id')
                  ->references('id')
                  ->on('franchisors')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->foreign('jenis')
                  ->references('id')
                  ->on('jenises')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('franchises');
    }
}
