<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSignatureImgToChangeOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      if (Schema::hasTable('change_orders')) {
        Schema::table('change_orders', function (Blueprint $table) {
          if (!Schema::hasColumn('change_orders', 'signature_img')) {
                $table->text('signature_img')->nullable()->after('signature');
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
