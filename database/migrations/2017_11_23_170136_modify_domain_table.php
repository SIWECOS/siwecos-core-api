<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyDomainTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('domains', function (Blueprint $table) {
            $table->string('domain', 191)->nullable(false)->unique();
            $table->string('domain_token', 191)->nullable(false)->unique();
            $table->integer('token_id')->unsigned()->nullable(false);
            $table->foreign('token_id')->references('id')->on('tokens')->onDelete('cascade');
            $table->boolean('verified')->default(false);

        }
        );
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
