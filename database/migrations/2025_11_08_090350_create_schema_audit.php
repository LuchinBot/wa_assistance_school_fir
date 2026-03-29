<?php

use Illuminate\Database\Migrations\Migration;
use App\Actions\PGSchema;

class CreateSchemaAudit extends Migration
{
    public function up()
    {
        if (!(new PGSchema)->schemaExists('audit')) {
            (new PGSchema)->create('audit');
        }
    }

    public function down()
    {
        (new PGSchema)->drop('audit');
    }
}