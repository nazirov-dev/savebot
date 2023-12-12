<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDownloadedMediaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('downloaded_media', function (Blueprint $table) {
            $table->id();
            $table->string('url');
            $table->bigInteger('media_id');
            $table->bigInteger('user_id');
            $table->bigInteger('platform_id');
            $table->bigInteger('media_group_id')->nullable();
            $table->string('type');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('downloaded_media');
    }
}
