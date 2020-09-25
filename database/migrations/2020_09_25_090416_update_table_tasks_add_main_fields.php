<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateTableTasksAddMainFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dateTime("due_date")->nullable();
            $table->string("status", 20);
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tasks', function (Blueprint $table) {
            //
        });
    }
}
