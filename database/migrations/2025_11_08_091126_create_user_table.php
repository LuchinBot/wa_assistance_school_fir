<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserTable extends Migration
{
    public function up()
    {
        Schema::create('security.user', function (Blueprint $table) {

            $table->bigIncrements('coduser');

            $table->unsignedBigInteger('codprofile');
            $table->unsignedBigInteger('codperson');

            $table->string('username', 50)->unique();
            $table->string('password');

            $table->char('is_active', 1)->default('N');
            $table->char('is_super',1)->default('N');
            $table->boolean('must_change_password')->default(true);

            $table->integer('login_attempts')->default(0);
            $table->timestampTz('locked_until')->nullable();
            $table->timestampTz('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();

            $table->timestampsTz();
            $table->softDeletesTz();

            /*
            |--------------------------------------------------------------
            | FOREIGN KEYS (Nombradas para PostgreSQL)
            |--------------------------------------------------------------
            */

            $table->foreign('codprofile', 'fk_user_profile')
                ->references('codprofile')
                ->on('security.profile')
                ->onDelete('restrict');

            $table->foreign('codperson', 'fk_user_person')
                ->references('codperson')
                ->on('main.person')
                ->onDelete('restrict');

            $table->foreign('created_by', 'fk_user_created_by')
                ->references('coduser')
                ->on('security.user')
                ->onDelete('set null');

            $table->foreign('updated_by', 'fk_user_updated_by')
                ->references('coduser')
                ->on('security.user')
                ->onDelete('set null');

            $table->foreign('deleted_by', 'fk_user_deleted_by')
                ->references('coduser')
                ->on('security.user')
                ->onDelete('set null');

            /*
            |--------------------------------------------------------------
            | ÍNDICES
            |--------------------------------------------------------------
            */

            $table->index('codprofile', 'idx_user_codprofile');
            $table->index('codperson', 'idx_user_codperson');
            $table->index('is_active', 'idx_user_is_active');
        });
    }

    public function down()
    {
        Schema::dropIfExists('security.user');
    }
}
