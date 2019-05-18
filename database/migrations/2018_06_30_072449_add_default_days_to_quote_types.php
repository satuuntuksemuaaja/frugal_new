<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDefaultDaysToQuoteTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('quote_types')) {
          Schema::table('quote_types', function (Blueprint $table) {
            if (!Schema::hasColumn('quote_types', 'default_days')) {
                  $table->integer('default_days')->nullable()->default(0);
            }
          });
        }
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
