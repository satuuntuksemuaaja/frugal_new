<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRemovalPriceToCabinets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      if (Schema::hasTable('cabinets')) {
        Schema::table('cabinets', function (Blueprint $table) {
          if (!Schema::hasColumn('cabinets', 'removal_price')) {
                $table->double('removal_price', 10, 2)->nullable()->default(0);
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
