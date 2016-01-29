<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFranchisorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('franchisors', function (Blueprint $table) {
            $table->integer('id')->unsigned()->nullable()->unique();
            $table->string('nama', 30);
            $table->string('telepon', 20);
            $table->string('alamat', 50);
            $table->timestamps();
            $table->foreign('id')
                  ->references('id')
                  ->on('users')
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
        Schema::drop('franchisors');
    }
}
