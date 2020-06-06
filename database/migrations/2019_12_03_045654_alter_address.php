<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterAddress extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('address', function(Blueprint $table)
        {
            $table->integer('add_cit_id')->default(0)->index();
            $table->integer('add_dis_id')->default(0)->index();
            $table->integer('add_ward_id')->default(0)->index();
            $table->integer('add_street_id')->default(0)->index();
            $table->index(['add_cit_id','add_dis_id','add_ward_id','add_street_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
