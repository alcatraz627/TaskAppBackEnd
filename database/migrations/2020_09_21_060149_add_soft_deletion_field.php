<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSoftDeletionField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function(Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('roles', function(Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('tasks', function(Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('register_verif', function(Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('forgot_pass', function(Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function(Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('roles', function(Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('tasks', function(Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('register_verif', function(Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('users', function(Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('forgot_pass', function(Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
}
