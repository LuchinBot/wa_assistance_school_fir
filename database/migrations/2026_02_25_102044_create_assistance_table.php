<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssistanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('system.assistance', function (Blueprint $table) {
            $table->bigIncrements('codassistance');
            $table->unsignedBigInteger('codassistance_session');
            $table->unsignedBigInteger('coduser_responsible')->nullable();
            $table->unsignedBigInteger('codenrollment');
            $table->timestamp('time_entry')->nullable();
            $table->enum('status', ['present', 'absent', 'late', 'justified'])->nullable();
            $table->text('observation')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign Keys
            $table->foreign('codassistance_session')
                ->references('codassistance_session')
                ->on('system.assistance_session')
                ->onDelete('set null')
                ->onUpdate('cascade');

            // Foreign Keys
            $table->foreign('coduser_responsible')
                ->references('coduser')
                ->on('security.user')
                ->onDelete('set null')
                ->onUpdate('cascade');

            $table->foreign('codenrollment')
                ->references('codenrollment')
                ->on('system.enrollment')
                ->onDelete('set null')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('system.assistance');
    }
}
