<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateTotalscoreRecurrentsccan extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table( 'scans', function ( Blueprint $table ) {
            $table->boolean('recurrentscan')->default(false);
        } );

        Schema::table( 'scan_results', function ( Blueprint $table ) {
            $table->integer('total_score')->default(0);
        } );
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
