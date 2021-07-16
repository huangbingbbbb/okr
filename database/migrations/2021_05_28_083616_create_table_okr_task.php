<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableOkrTask extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('okr_task', function (Blueprint $table) {
            $table->increments('id')->comment('id');
            $table->string('taskname',200)->default('')->comment('任务名称');
            $table->string('describe',500)->default('')->comment('任务描述');
            $table->string('weight',100)->default('')->comment('任务权重');
            $table->tinyInteger('type')->default(0)->comment('任务类型，1系统实施、2系统优化、3数据巡检及治理、4培训');
            $table->string('translate',500)->default('')->comment('完成标准');
            $table->string('starttime',200)->default('')->comment('计划开始时间');
            $table->string('endtime',200)->default('')->comment('计划结束时间');
            $table->string('remarks',500)->default('')->comment('备注');
            $table->tinyInteger('state')->default(0)->comment('当前状态，1进行中、2已完成、3逾期未完成');
            $table->string('completionrate',100)->default('')->comment('完成率');
            $table->string('undertakeUsers',300)->default('')->comment('承办人，用逗号隔开');
            $table->string('CreateUser',100)->default('')->comment('任务创建人');
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
