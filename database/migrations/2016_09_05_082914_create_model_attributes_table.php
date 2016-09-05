<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateModelAttributesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $modelAttribute = new \Gregoriohc\Attributum\ModelAttribute();

        Schema::create($modelAttribute->getTable(), function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->softDeletes();
            $table->string('attributable_type');
            $table->string('name');
            $table->string('type', 64);
            $table->text('options')->nullable();
            $table->unique(['attributable_type', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $modelAttribute = new \Gregoriohc\Attributum\ModelAttribute();

        Schema::drop($modelAttribute->getTable());
    }
}
