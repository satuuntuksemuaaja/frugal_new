<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSignoffImgToFfts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('ffts')) {
          Schema::table('ffts', function (Blueprint $table) {
            if (!Schema::hasColumn('ffts', 'signoff_img')) {
                  $table->text('signoff_img')->nullable()->after('signoff');
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
