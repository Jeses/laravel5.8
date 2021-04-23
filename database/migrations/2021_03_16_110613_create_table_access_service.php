<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableAccessService extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('access_service', function (Blueprint $table) {
            $table->bigIncrements('id')->unique()->comment('主键');
            $table->string('access_key',100)->comment('AK');
            $table->string('access_key_secret',100)->comment('SK');
            $table->tinyInteger('status')->default(0)->comment('秘钥使用情况 0正常 1停用');
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
        Schema::dropIfExists('access_service');
    }
}
