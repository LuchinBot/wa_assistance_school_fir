<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateSchemaSecurity extends Migration
{
    public function up()
    {
        if (!(new \App\Actions\PGSchema)->schemaExists('security')) {
            (new \App\Actions\PGSchema)->create('security');
        }
    }

    public function down()
    {
        DB::statement('DROP SCHEMA IF EXISTS "security" CASCADE');
    }
}