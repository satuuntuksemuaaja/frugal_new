<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSignatureImgToFfts extends Migration
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
            if (!Schema::hasColumn('ffts', 'signature_img')) {
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
