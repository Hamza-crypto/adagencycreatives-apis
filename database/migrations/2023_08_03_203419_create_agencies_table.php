<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('agencies', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->unsignedBigInteger('user_id');
            $table->string('name');
            $table->string('size');
            $table->text('about');
            $table->text('industry_experience')->nullable();
            $table->text('media_experience')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_remote')->default(0);
            $table->boolean('is_hybrid')->default(0);
            $table->boolean('is_onsite')->default(0);
            $table->boolean('is_urgent')->default(0);
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
        Schema::dropIfExists('agencies');
    }
};
