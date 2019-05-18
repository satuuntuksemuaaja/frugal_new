<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddShowroomMeasuresClosingToLeads extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('leads')) {
          Schema::table('leads', function (Blueprint $table) {

            //Showroom
            if (!Schema::hasColumn('leads', 'showroom_user_id')) {
                  $table->integer('showroom_user_id')->unsigned()->after('status_id')->default('0');
            }
            if (!Schema::hasColumn('leads', 'showroom_scheduled')) {
                  $table->datetime('showroom_scheduled')->nullable()->after('showroom_user_id');
            }
            if (!Schema::hasColumn('leads', 'showroom_location_id')) {
                  $table->integer('showroom_location_id')->nullable()->after('showroom_scheduled');
            }

            //Closing
            if (!Schema::hasColumn('leads', 'closing_user_id')) {
                  $table->integer('closing_user_id')->unsigned()->after('showroom_location_id')->default('0');
            }
            if (!Schema::hasColumn('leads', 'closing_scheduled')) {
                  $table->datetime('closing_scheduled')->nullable()->after('closing_user_id');
            }

            //Digital
            if (!Schema::hasColumn('leads', 'digital_user_id')) {
                  $table->integer('digital_user_id')->unsigned()->after('closing_scheduled')->default('0');
            }
            if (!Schema::hasColumn('leads', 'digital_scheduled')) {
                  $table->datetime('digital_scheduled')->nullable()->after('digital_user_id');
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
