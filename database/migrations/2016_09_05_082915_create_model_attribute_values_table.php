<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateModelAttributeValuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $modelAttributeValue = new \Gregoriohc\Attributum\ModelAttributeValue();
        $modelAttribute = new \Gregoriohc\Attributum\ModelAttribute();

        Schema::create($modelAttributeValue->getTable(), function (Blueprint $table) use ($modelAttribute) {
            $table->increments('id');
            $table->timestamps();
            $table->softDeletes();
            $table->morphs('attributable');
            $table->unsignedInteger('model_attribute_id');
            $table->longText('value')->nullable();
            $table->foreign('model_attribute_id')->references('id')->on($modelAttribute->getTable());
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $modelAttributeValue = new \Gregoriohc\Attributum\ModelAttributeValue();

        Schema::drop($modelAttributeValue->getTable());
    }
}
