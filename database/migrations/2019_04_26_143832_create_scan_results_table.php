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
            $table->bigInteger('scan_id')->unsigned();
            $table->string('scanner_code');
            $table->json('result')->nullable();
            $table->boolean('is_failed')->default(false);
            $table->timestamps();

            $table->foreign('scan_id')->references('id')->on('scans')->onDelete('cascade');
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
