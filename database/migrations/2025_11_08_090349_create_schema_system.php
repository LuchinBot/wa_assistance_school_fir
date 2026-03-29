<?php

use Illuminate\Database\Migrations\Migration;
use App\Actions\PGSchema;

class CreateSchemaSystem extends Migration
{
    public function up()
    {
        if (!(new PGSchema)->schemaExists('system')) {
            (new PGSchema)->create('system');
        }
    }

    public function down()
    {
        (new PGSchema)->drop('system');
    }
}