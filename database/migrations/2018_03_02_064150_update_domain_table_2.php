<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateDomainTable2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
 public function up() {
		Schema::table( 'domains', function ( Blueprint $table ) {
			$table->string( 'domain_token' )->nullable()->change();
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
