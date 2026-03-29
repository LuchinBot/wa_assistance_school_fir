<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProfilePermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('security.profile_permissions', function (Blueprint $table) {
            $table->bigIncrements('codprofile_permission');
            $table->integer('codprofile');
            $table->integer('codpermission');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('codprofile', 'fk_profile_permissions_profile')
                ->references('codprofile')
                ->on('security.profile')
                ->onDelete('restrict');

            $table->foreign('codpermission', 'fk_profile_permissions_permission')
                ->references('codpermission')
                ->on('security.permissions')
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
        Schema::dropIfExists('profile_permissions');
    }
}
