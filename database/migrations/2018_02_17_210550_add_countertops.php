<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use FK3\Models\Countertop;

class AddCountertops extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('countertops', function ($t) {
            $t->increments('id');
            $t->timestamps();
            $t->string('name');
            $t->double('price');
            $t->double('removal_price')->default(0);
            $t->boolean('active')->default(1);
            $t->integer('type_id');

            $t->index('type_id');
        });

        // Next change quote_types granite references to countertop.
        DB::statement(
            "ALTER TABLE quote_types CHANGE granite countertops TINYINT(1) NOT NULL DEFAULT '0'"
        );

        Schema::drop('granites');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Rename back to granite.
        Schema::table('quote_types', function ($t) {
            $t->renameColumn('countertops', 'granite');
        });

        //$this->command->alert("Recreating Granite Table");
        $this->recreateGraniteTable();
        //$this->command->alert("Reseeding Granite Table");
        $this->reSeedGraniteTable();
        //$this->command->alert("Removing Countertops Table");
        Schema::drop('countertops');
    }

    protected function recreateGraniteTable()
    {
        Schema::create('granites', function($t)
        {
           $t->increments('id');
           $t->timestamps();
           $t->string('name');
           $t->double('price');
           $t->double('removal_price')->default(0);
           $t->boolean('active')->default(1);
        });
    }

    protected function reseedGraniteTable()
    {
        $countertopTable = new Countertop;
        $graniteTable = new Granite;
        \Illuminate\Support\Facades\DB::statement('truncate table granites');
        foreach ($countertopTable::get() as $countertop)
        {
            $graniteTable->create([
                'id'            => $countertop->id,
                'name'          => $countertop->name,
                'price'         => $countertop->price,
                'removal_price' => $countertop->removal_price,
                'active'        => $countertop->active
            ]);
        }
    }
}
