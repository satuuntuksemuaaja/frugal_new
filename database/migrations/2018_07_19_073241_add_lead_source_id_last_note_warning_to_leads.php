<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLeadSourceIdLastNoteWarningToLeads extends Migration
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
            $table->integer('lead_source_id')->nullable();
            $table->datetime('last_note')->nullable();
            $table->string('warning', 255)->nullable();
            $table->softDeletes();
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
