<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableOkrTaskweeks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('okr_taskweeks', function (Blueprint $table) {
            $table->increments('id')->comment('id');
            $table->tinyInteger('pid')->default(0)->comment('上级id');
            $table->string('starttime',200)->default('')->comment('本周开始时间');
            $table->string('endtime',200)->default('')->comment('本周结束时间');
            $table->string('weekplan',200)->default('')->comment('本周计划');
            $table->string('progress',200)->default('')->comment('完成进度');
            $table->string('completionrate',100)->default('')->comment('完成率');
            $table->string('actualtime',200)->default('')->comment('实际完成时间');
            $table->tinyInteger('state')->default(0)->comment('当前状态，1进行中、2已完成、3逾期未完成');
            $table->string('file',500)->default('')->comment('附件');
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
        //
    }
}
