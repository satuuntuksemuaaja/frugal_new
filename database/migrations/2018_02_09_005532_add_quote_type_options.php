<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddQuoteTypeOptions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('quote_types', function ($t) {
            $t->boolean('cabinets')->default(0);
            $t->boolean('granite')->default(0);
            $t->boolean('sinks')->default(0);
            $t->boolean('appliances')->default(0);
            $t->boolean('accessories')->default(0);
            $t->boolean('hardware')->default(0);
            $t->boolean('led')->default(0);
            $t->boolean('tile')->default(0);
            $t->boolean('addons')->default(0);
            $t->boolean('responsibilities')->default(0);
            $t->boolean('questionaire')->default(0);
            $t->boolean('buildup')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('quote_types', function ($t) {
            $t->dropColumn('cabinets');
            $t->dropColumn('granite');
            $t->dropColumn('sinks');
            $t->dropColumn('appliances');
            $t->dropColumn('accessories');
            $t->dropColumn('hardware');
            $t->dropColumn('led');
            $t->dropColumn('tile');
            $t->dropColumn('addons');
            $t->dropColumn('responsibilities');
            $t->dropColumn('questionaire');
            $t->dropColumn('buildup');
        });
    }
}
