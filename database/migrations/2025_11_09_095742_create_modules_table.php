<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateModulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('security.modules', function (Blueprint $table) {
            $table->bigIncrements('codmodule');
            $table->integer('codmodule_parent')->nullable();
            $table->integer('cod_system')->nullable();
            $table->string('name_large')->nullable();
            $table->string('name_short')->nullable();
            $table->integer('order')->nullable();
            $table->string('route')->nullable();
            $table->string('icon')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // FK self reference
            $table->foreign('codmodule_parent', 'fk_modules_parent')
                ->references('codmodule')
                ->on('security.modules')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('modules');
    }
}
