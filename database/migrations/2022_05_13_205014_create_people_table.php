<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePeopleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('people', function (Blueprint $table) {
            $table->id();
            $table->string('name',30);
            $table->string('lastNameP',30);
            $table->string('lastNameM',30);
            $table->string('gender',9);
            $table->date('birthDate');
            $table->string('phone',15)->unique();
            $table->string('curp',18)->unique();;
            $table->string('rfc',13)->unique();;
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('people');
    }
}
