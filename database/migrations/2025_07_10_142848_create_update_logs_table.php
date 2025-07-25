<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUpdateLogsTable extends Migration
{
    public function up()
    {
        Schema::create('update_logs', function (Blueprint $table) {
            $table->id();
            $table->string('package_name');
            $table->string('package_type');
            $table->string('current_version');
            $table->string('latest_version');
            $table->boolean('has_update');
            $table->json('metadata')->nullable();
            $table->timestamp('checked_at');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('update_logs');
    }
}