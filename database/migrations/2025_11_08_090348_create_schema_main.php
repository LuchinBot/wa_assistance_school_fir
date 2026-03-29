<?php

use Illuminate\Database\Migrations\Migration;
use App\Actions\PGSchema;

class CreateSchemaMain extends Migration
{
    public function up()
    {
        if (!(new PGSchema)->schemaExists('main')) {
            (new PGSchema)->create('main');
        }
    }

    public function down()
    {
        (new PGSchema)->drop('main');
    }
}