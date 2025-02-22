<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImageableTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'imageables',
            function (Blueprint $table) {
                $table->string('path', 64)
                    ->primary();
                $table->string('hash', 32)
                    ->index();
                $table->string('disk', 16)
                    ->default(config('imageable.disk'));
                $table->string('mime_type');
                $table->string('collection', 32)
                    ->nullable()
                    ->index();
                $table->integer('width')
                    ->nullable();
                $table->integer('height')
                    ->nullable();
                $table->nullableMorphs('owner');
                $table->timestamps();
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('imageables');
    }
}
