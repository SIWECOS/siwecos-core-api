<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateScanResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('scan_results', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('scan_id');
            $table->string('scanner_code');
            $table->json('result');
            $table->timestamps();

            $table->foreign('scan_id')->references('id')->on('scans');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('scan_results');
    }
}
