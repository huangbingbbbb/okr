<?php

namespace App\Http\Controllers\Api;

use App\Http\Models\Taskcycle;
use App\Http\Models\Taskweeks;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Models\Task;
use App\Http\Models\ZnzjUsers;
use App\Http\Models\ZnzjOrg;
use App\Http\Models\Znzjleader;
use App\Http\Models\Znzjadminuser;

class IndexController extends Controller
{
    public function __construct()
    {
        date_default_timezone_set("Asia/Shanghai");
    }

    public function Index(Request $request){
//        echo '111';
    }

    // 一个时间区间分解为多少个星期
    function getSchemeDate($begin_date = '', $end_date = '')
    {
        if (!$begin_date || !$end_date) return false;
        $diff_time   = strtotime($end_date) + 24 * 60 * 60 - strtotime($begin_date);
        $diff_week   = round($diff_time/(7 * 24 * 60 * 60));
        $times_arr   = [];
        foreach (range(0, $diff_week - 1) as $item) {
            $times_arr[$item]['begin_date'] = date('Y-m-d', strtotime($begin_date) + (7 * 24 * 60 * 60) * $item);
            $times_arr[$item]['end_date']   = date('Y-m-d', strtotime($times_arr[$item]['begin_date']) + (6 * 24 * 60 * 60));
            if(strtotime($times_arr[$item]['end_date']) > strtotime($end_date) ){
                unset($times_arr[$item]);
            }
        }
        return $times_arr;
    }

    // 获取季度的开始结束时间
    public function actionQuarter ($time)
    {
        $season = ceil((date('n', $time))/3);//当月是第几季度
        $startTime = strtotime(date('Y-m-d H:i:s', mktime(0, 0, 0,$season*3-3+1,1,date('Y'))));
        $endTime = strtotime(date('Y-m-d H:i:s', mktime(23,59,59,$season*3,date('t',mktime(0, 0 , 0,$season*3,1,date("Y"))),date('Y'))));
        $arr = [date('Y-m-d',$startTime),date('Y-m-d',$endTime)];
        return $arr;
    }

    public function AddTask(Request $request){
//        $begin_date="2018-2-7";
//        $end_date="2018-6-1";
//        print_r($this->getSchemeDate($begin_date,$end_date));
        //指定时间的月初
//        $firstday = date("Y-m-01",strtotime($begin_date));
        //指定时间的月末
//        $lastday = date("Y-m-d",strtotime("$firstday +1 month -1 day"));  //月末
//        print_r($firstday); echo PHP_EOL;  print_r($lastday);

//        $time = strtotime($begin_date);
//        print_r($this->actionQuarter($time));

        $status = $request->input('status'); //1添加 2编辑
        $id = $request->input('id'); //本级id，编辑时传递
        $pid = $request->input('pid'); //上级id,编辑时不用传

        $taskname = $request->input('taskname');
        $describe = $request->input('describe');
        $weight = $request->input('weight');
        $type = $request->input('type');
        $translate = $request->input('translate');
        $starttime = $request->input('starttime');
        $endtime = $request->input('endtime');
        $remarks = $request->input('remarks');

        $undertakeUsers = $request->input('undertakeUsers'); //数组
        $undertakeUsers = ['0B58F12B-BC42-40E2-95D8-AA1527732677'];
        $undertakeUsers = implode(',',$undertakeUsers);

        $CreateUser = $request->input('CreateUser');

        $cycle = $request->input('cycle');
        $state = $request->input('state');

        $data = $request->input('data');
        $data =  [
            ['name'=>'1季度','target'=>'目标','standard'=>'目标标准111','date'=>'2021-01'],
            ['name'=>'2季度','target'=>'目标','standard'=>'目标标准222','date'=>'2021-04'],
            ['name'=>'3季度','target'=>'目标','standard'=>'目标标准333','date'=>'2021-07'],
            ['name'=>'4季度','target'=>'目标','standard'=>'目标标准444','date'=>'2021-10'],
        ];

        $created_at = date('Y-m-d H:i:s', time());
        if ($status ==1){
            $edit = Task::insertGetId([
                'taskname'=>$taskname,
                'describe'=>$describe,
                'weight'=>$weight,
                'type'=>$type,
                'translate'=>$translate,
                'starttime'=>$starttime,
                'endtime'=>$endtime,
                'state'=>$state,
                'remarks'=>$remarks,
                'undertakeUsers'=>$undertakeUsers,
                'CreateUser'=>$CreateUser,
                'created_at'=>$created_at,
                'cycle'=>$cycle,
                'pid'=>$pid  // 等于0 时母任务，大于0 时 子任务
            ]);
            if ($edit && $pid > 0 && !empty($data)){  // 添加子任务 及其 周期性任务
                foreach ($data as $k=>$v){
                    $add = Taskcycle::insertGetId([
                        'date'=>$v['date'],
                        'pid'=>$edit,
                        'taskcyclename'=>$v['name'],
                        'target'=>$v['target'],
                        'standard'=>$v['standard'],
                        'state'=>$state,
                        'cycle'=>$cycle,
                        'created_at'=>$created_at
                    ]);
                    if ($cycle == 1){
                        $time = strtotime($v['date']);
                        $date = $this->actionQuarter($time);
                        $weeks = $this->getSchemeDate($date[0],$date[1]); //分别是季初和季末
                    }else{
                        $firstday = date("Y-m-01",strtotime($v['date']));
                        $lastday = date("Y-m-d",strtotime("$firstday +1 month -1 day"));
                        $weeks = $this->getSchemeDate($firstday,$lastday); //分别是月初和月末
                    }
                    foreach ($weeks as $k2=>$v2){
                        Taskweeks::insert([
                            'pid'=>$add,
                            'starttime'=>$v2['begin_date'],
                            'endtime'=>$v2['end_date'],
                            'created_at'=>$created_at,
                        ]);
                    }
                }
            }
        }else{
            $edit = Task::where('id',$id)->update([
                'taskname'=>$taskname,
                'describe'=>$describe,
                'weight'=>$weight,
                'type'=>$type,
                'translate'=>$translate,
                'starttime'=>$starttime,
                'endtime'=>$endtime,
                'state'=>$state,
                'remarks'=>$remarks,
                'undertakeUsers'=>$undertakeUsers,
                'CreateUser'=>$CreateUser,
                'cycle'=>$cycle,
            ]);
            if ($edit && !empty($data)){  // 编辑子任务 及其 周期性任务
                $del = Taskcycle::where('pid',$id)->delete();
                if ($del){
                    foreach ($data as $k=>$v){
                        $add = Taskcycle::insert([
                            'date'=>$v['date'],
                            'pid'=>$id,
                            'taskcyclename'=>$v['name'],
                            'target'=>$v['target'],
                            'standard'=>$v['standard'],
                            'state'=>$state,
                            'cycle'=>$cycle,
                            'created_at'=>$created_at
                        ]);
                    }
                }
            }
        }

        if ($edit){
            $newnum = ['code'=>200,'msg'=>'操作成功'];
            return json_encode($newnum,JSON_UNESCAPED_UNICODE);
        }else{
            $newnum = ['code'=>400,'msg'=>'操作失败'];
            return json_encode($newnum,JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * 分解
     */
    public function Decompose(Request $request){
        $status = $request->input('status'); // 1分解 2编辑
        $pid = $request->input('pid'); //当前母任务id
        $id = $request->input('id'); //当前子任务id，编辑时传递

        $taskname = $request->input('taskname');
        $describe = $request->input('describe');
        $weight = $request->input('weight');
        $type = $request->input('type');
        $translate = $request->input('translate');
        $starttime = $request->input('starttime');
        $endtime = $request->input('endtime');
        $remarks = $request->input('remarks');

        $undertakeUsers = $request->input('undertakeUsers'); //数组
        $undertakeUsers = ['0B58F12B-BC42-40E2-95D8-AA1527732677'];
        $undertakeUsers = implode(',',$undertakeUsers);

        $CreateUser = $request->input('CreateUser');

        $cycle = $request->input('cycle');

        $state = $request->input('state');

        $data = $request->input('data');
        $data =  [
            ['name'=>'1季度','target'=>'目标','standard'=>'目标标准','date'=>'2021-01'],
            ['name'=>'2季度','target'=>'目标','standard'=>'目标标准','date'=>'2021-04'],
            ['name'=>'3季度','target'=>'目标','standard'=>'目标标准','date'=>'2021-07'],
            ['name'=>'4季度','target'=>'目标','standard'=>'目标标准','date'=>'2021-10'],
        ];

        $created_at = date('Y-m-d H:i:s', time());

        foreach ($data as $k=>$v){
            $edit = Taskcycle::insert([
                'date'=>$v['date'],
                'pid'=>$pid,
                'taskcyclename'=>$v['name'],
                'target'=>$v['target'],
                'standard'=>$v['standard'],
                'taskname'=>$taskname,
                'describe'=>$describe,
                'weight'=>$weight,
                'type'=>$type,
                'translate'=>$translate,
                'starttime'=>$starttime,
                'endtime'=>$endtime,
                'remarks'=>$remarks,
                'state'=>$state,
                'undertakeUsers'=>$undertakeUsers,
                'CreateUser'=>$CreateUser,
                'cycle'=>$cycle,
                'created_at'=>$created_at
            ]);
        }
    }

    /**
     * 任务类型接口
     */
    public function TypeList(){
        $list = [
            ['id'=>1,'name'=>'系统实施'],
            ['id'=>2,'name'=>'系统优化'],
            ['id'=>3,'name'=>'数据巡检及治理'],
            ['id'=>4,'name'=>'培训']
        ];
        $newnum = ['code'=>200,'msg'=>'请求成功','list'=>$list];
        return json_encode($newnum,JSON_UNESCAPED_UNICODE);
    }

    /**
     * 信息部人员下拉框
     */
    public function Userlist(){
        $list = ZnzjUsers::select('UserGUID','UserCode','UserName','BUGUID')->where('BUGUID','8ECC4156-D37F-EB11-80CC-801844EDC021')->get()->toArray();
        foreach ($list as $k=>$v){
            $org = ZnzjOrg::select('BUName')->where('BUGUID',$v['BUGUID'])->first();
            $list[$k]['BUName'] = $org['BUName'];
        }
        $newnum = ['code'=>200,'msg'=>'请求成功','list'=>$list];
        return json_encode($newnum,JSON_UNESCAPED_UNICODE);
    }

    private function checkWuyeUrl($data){
        $url='http://hr.zhongnangroup.cn/PSIGW/RESTListeningConnector/PSFT_HR/C_SERV_DEPT_POST.v1/';
        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
        curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包
        curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
        curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
//        curl_setopt($curl, CURLOPT_HTTPHEADER,   $header);
        $res = curl_exec($curl); // 执行操作
        if (curl_errno($curl)) {
            echo 'Errno'.curl_error($curl);//捕抓异常
        }
        curl_close($curl); // 关闭CURL会话
        return $res;
    }

    /**
     * 同步领导
     */
    public function Leader(){
        $json = '{"c_token_key":"RIXIE","c_token_value":"2F35B3C0-73E2-4590-A430-029B5F387C10","start_dttm":"2009-01-01 9:00:00","end_dttm":"2021-04-01 9:00:00"}';
        $list = $this->checkWuyeUrl($json);
        $list = json_decode($list,true);
        foreach ($list['depts'] as $k=>$v){
            Znzjleader::insert([
                'dept_descr'=>$v['dept_descr'],
                'dept_person'=>$v['dept_person'],
            ]);
        }
    }

    /**
     * 任务列表(领导)
     */
    public function TaskList(Request $request){
        $token = $request->header('token');
        $info = Znzjadminuser::select('UserName')->where('UserGUID',$token)->first();
        $org = Znzjleader::where('dept_person',$info['UserName'])->first();
        if (empty($org)){
            $newnum = ['code'=>401,'msg'=>'当前用户无权限查看任务列表（领导）'];
            return json_encode($newnum,JSON_UNESCAPED_UNICODE);
        }

        $UserGUID = $request->input('UserGUID'); //数组
//        $UserGUID = ['B3AD9629-5D72-EA11-80CF-005056AA7C75','97D59053-2673-EA11-80CF-005056AA7C75'];
        if (!empty($UserGUID)){
            foreach ($UserGUID as $k=>$v){
                $total = Task::where('undertakeUsers','LIKE','%'.$v.'%')->count();
                $totaltwo = Task::where('undertakeUsers','LIKE','%'.$v.'%')->where('state',2)->count();
                $strtwo=round($totaltwo/$total, 2);

                $arr[] = Task::where('pid','=',0)->where('undertakeUsers','LIKE','%'.$v.'%')->first();
            }
            $arr = json_decode(json_encode($arr,true),true);
            $list = [];
            $list['data'] = [];
            foreach ($arr as $key=>$value){
                if (isset($value['id'])){
                    $list['data'][] = $value;
                }
            }
            $list['total'] = count($list['data']);
        }else{
            $list = Task::where('pid','=',0)->orderBy('id','desc')->paginate($request->input('pagesize'))->toArray();

            $total = Task::count();
            $totaltwo = Task::where('state',2)->count();
            $strtwo=round($totaltwo/$total, 2);
        }

        $listTwo = array();
        foreach ($list['data'] as $k=>$v){
            $listTwo[$k]['taskname'] = $v['taskname'];
            $listTwo[$k]['describe'] = $v['describe'];
            $listTwo[$k]['weight'] = $v['weight'];
            $listTwo[$k]['type'] = $v['type'];
            $listTwo[$k]['translate'] = $v['translate'];
            $listTwo[$k]['starttime'] = $v['starttime'];
            $listTwo[$k]['endtime'] = $v['endtime'];
            $listTwo[$k]['remarks'] = $v['remarks'];
            $listTwo[$k]['state'] = $v['state'];
            $listTwo[$k]['status'] = 1;
            $status = 1;

            if ($v['pid'] > 0){
                $listTwo[$k]['status'] = 2;
                $status = 2;
            }

            if ($status == 1){
                $count = Task::where('pid',$v['id'])->count();
                $counttwo = Task::where('pid',$v['id'])->where('state',2)->count();
                if ($count !== 0){
                    $str=round($counttwo/$count, 2);
                    $listTwo[$k]['completionrate'] = $str."%";
                }else{
                    $listTwo[$k]['completionrate'] = "0%";
                }

            }else{
                $listTwo[$k]['completionrate'] = "0%";
            }
            $user = explode(',',$v['undertakeUsers']);
            if (!empty($user)){
                $listTwo[$k]['undertakeUsers'] = ZnzjUsers::select('UserName','UserGUID')->whereIn('UserGUID',$user)->get()->toArray();
            }else{
                $listTwo[$k]['undertakeUsers'] = [];
            }
            $listTwo[$k]['id'] = $v['id'];

            $son = Task::where('pid',$v['id'])->get()->toArray();
            if (!empty($son)){
                foreach ($son as $k2=>$v2){
                    $listTwo[$k]['son'][$k2]['taskname'] = $v2['taskname'];
                    $listTwo[$k]['son'][$k2]['describe'] = $v2['describe'];
                    $listTwo[$k]['son'][$k2]['weight'] = $v2['weight'];
                    $listTwo[$k]['son'][$k2]['type'] = $v2['type'];
                    $listTwo[$k]['son'][$k2]['translate'] = $v2['translate'];
                    $listTwo[$k]['son'][$k2]['starttime'] = $v2['starttime'];
                    $listTwo[$k]['son'][$k2]['endtime'] = $v2['endtime'];
                    $listTwo[$k]['son'][$k2]['remarks'] = $v2['remarks'];
                    $listTwo[$k]['son'][$k2]['state'] = $v2['state'];

                    $listTwo[$k]['son'][$k2]['status'] = 2; //子任务
                    $status = 2;

                    $listTwo[$k]['son'][$k2]['completionrate'] = "0%";

                    $user = explode(',',$v2['undertakeUsers']);
                    if (!empty($user)){
                        $listTwo[$k]['son'][$k2]['undertakeUsers'] = ZnzjUsers::select('UserName','UserGUID')->whereIn('UserGUID',$user)->get()->toArray();
                    }else{
                        $listTwo[$k]['son'][$k2]['undertakeUsers'] = [];
                    }
                    $listTwo[$k]['son'][$k2]['id'] = $v2['id'];
                }
            }else{
                $listTwo[$k]['son'] = [];
            }

        }
        if (!empty($listTwo)){
            $newnum = ['code'=>200,'msg'=>'请求成功','list'=>$listTwo,'total'=>$list['total'],'completionrate'=>$strtwo.'%'];
            return json_encode($newnum,JSON_UNESCAPED_UNICODE);
        }else{
            $newnum = ['code'=>400,'msg'=>'暂无数据'];
            return json_encode($newnum,JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * 成员任务列表
     */
    public function Member(Request $request){
        $type = $request->input('type'); // 1全部  2分配给我的

        $token = $request->header('token'); //当前登录人id

        if ($type == 1){
            $arr = Task::where('pid',0)->orderBy('id','desc')->paginate($request->input('pagesize'))->toArray();
            $total = Task::count();
            $totaltwo = Task::where('state',2)->count();
            $strtwo=round($totaltwo/$total, 2);
        }else{
            $arr = Task::where('undertakeUsers','LIKE','%'.$token.'%')->where('pid',0)->orderBy('id','desc')->paginate($request->input('pagesize'))->toArray();
            $total = Task::where('undertakeUsers','LIKE','%'.$token.'%')->count();
            $totaltwo = Task::where('undertakeUsers','LIKE','%'.$token.'%')->where('state',2)->count();
            $strtwo=round($totaltwo/$total, 2);
        }
        $list = array();
        if (!empty($arr['data'])){
            foreach ($arr['data'] as $k=>$v){
                $list[$k]['taskname'] = $v['taskname'];
                $list[$k]['describe'] = $v['describe'];
                $list[$k]['weight'] = $v['weight'];
                $list[$k]['type'] = $v['type'];
                $list[$k]['achievements'] = 1; //绩效，1是 2否
                $list[$k]['translate'] = $v['translate'];
                $list[$k]['starttime'] = $v['starttime'];
                $list[$k]['endtime'] = $v['endtime'];
                $list[$k]['remarks'] = $v['remarks'];
                $list[$k]['state'] = $v['state'];
                $list[$k]['status'] = 1;
                $status = 1;
                if ($status == 1){
                    $count = Task::where('pid',$v['id'])->count();
                    $counttwo = Task::where('pid',$v['id'])->where('state',2)->count();
                    if ($count !== 0){
                        $str=round($counttwo/$count, 2);
                        $list[$k]['completionrate'] = $str."%";
                    }else{
                        $list[$k]['completionrate'] = "0%";
                    }

                }else{
                    $list[$k]['completionrate'] = "0%";
                }
                $user = explode(',',$v['undertakeUsers']);
                if (!empty($user)){
                    $list[$k]['undertakeUsers'] = ZnzjUsers::select('UserName','UserGUID')->whereIn('UserGUID',$user)->get()->toArray();
                }else{
                    $list[$k]['undertakeUsers'] = [];
                }
                $list[$k]['id'] = $v['id'];

                $son = Task::where('pid',$v['id'])->get()->toArray();
                if (!empty($son)){
                    foreach ($son as $k2=>$v2){
                        $list[$k]['son'][$k2]['taskname'] = $v2['taskname'];
                        $list[$k]['son'][$k2]['describe'] = $v2['describe'];
                        $list[$k]['son'][$k2]['weight'] = $v2['weight'];
                        $list[$k]['son'][$k2]['type'] = $v2['type'];
                        $list[$k]['son'][$k2]['achievements'] = 2; //绩效，1是 2否
                        $list[$k]['son'][$k2]['translate'] = $v2['translate'];
                        $list[$k]['son'][$k2]['starttime'] = $v2['starttime'];
                        $list[$k]['son'][$k2]['endtime'] = $v2['endtime'];
                        $list[$k]['son'][$k2]['remarks'] = $v2['remarks'];
                        $list[$k]['son'][$k2]['state'] = $v2['state'];
                        $list[$k]['son'][$k2]['status'] = 2;
                        $status = 2;
                        if ($status == 1){
                            $count = Task::where('pid',$v2['id'])->count();
                            $counttwo = Task::where('pid',$v2['id'])->where('state',2)->count();
                            if ($count !== 0){
                                $str=round($counttwo/$count, 2);
                                $list[$k]['son'][$k2]['completionrate'] = $str."%";
                            }else{
                                $list[$k]['son'][$k2]['completionrate'] = "0%";
                            }

                        }else{
                            $list[$k]['son'][$k2]['completionrate'] = "0%";
                        }
                        $user = explode(',',$v2['undertakeUsers']);
                        if (!empty($user)){
                            $list[$k]['son'][$k2]['undertakeUsers'] = ZnzjUsers::select('UserName','UserGUID')->whereIn('UserGUID',$user)->get()->toArray();
                        }else{
                            $list[$k]['son'][$k2]['undertakeUsers'] = [];
                        }
                        $list[$k]['son'][$k2]['id'] = $v2['id'];
                    }
                }else{
                    $list[$k]['son'] = [];
                }
            }
        }

        if (!empty($list)){
            $newnum = ['code'=>200,'msg'=>'请求成功','list'=>$list,'total'=>$arr['total'],'completionrate'=>$strtwo.'%'];
            return json_encode($newnum,JSON_UNESCAPED_UNICODE);
        }else{
            $newnum = ['code'=>400,'msg'=>'暂无数据'];
            return json_encode($newnum,JSON_UNESCAPED_UNICODE);
        }

    }

    /**
     * 个人任务列表
     */
    public function Personal(Request $request){
        $type = $request->input('type'); // 1全部 2部门任务(分配给我的) 3个人创建

        $token = $request->header('token'); //当前登录人id

        if ($type == 1){
            $arr = Task::where('pid',0)->orderBy('id','desc')->paginate($request->input('pagesize'))->toArray();
            $total = Task::count();
            $totaltwo = Task::where('state',2)->count();
            if ($total !== 0){
                $strtwo=round($totaltwo/$total, 2);
            }

        }else if ($type ==2){
            $arr = Task::where('undertakeUsers','LIKE','%'.$token.'%')->where('pid',0)->orderBy('id','desc')->paginate($request->input('pagesize'))->toArray();
            $total = Task::where('undertakeUsers','LIKE','%'.$token.'%')->count();
            $totaltwo = Task::where('undertakeUsers','LIKE','%'.$token.'%')->where('state',2)->count();
            if ($total !== 0){
                $strtwo=round($totaltwo/$total, 2);
            }

        }else if ($type ==3){
            $arr = Task::where('CreateUser',$token)->where('pid',0)->orderBy('id','desc')->paginate($request->input('pagesize'))->toArray();
            $total = Task::where('CreateUser',$token)->count();
            $totaltwo = Task::where('CreateUser',$token)->where('state',2)->count();
            if ($total !== 0){
                $strtwo=round($totaltwo/$total, 2);
            }

        }
        $list = array();
        if (!empty($arr['data'])){
            foreach ($arr['data'] as $k=>$v){
                $list[$k]['taskname'] = $v['taskname'];
                $list[$k]['describe'] = $v['describe'];
                $list[$k]['weight'] = $v['weight'];
                $list[$k]['type'] = $v['type'];
                $list[$k]['achievements'] = 1; //绩效，1是 2否
                $list[$k]['translate'] = $v['translate'];
                $list[$k]['starttime'] = $v['starttime'];
                $list[$k]['endtime'] = $v['endtime'];
                $list[$k]['remarks'] = $v['remarks'];
                $list[$k]['state'] = $v['state'];
                $list[$k]['status'] = 1;
                $status = 1;
                if ($status == 1){
                    $count = Task::where('pid',$v['id'])->count();
                    $counttwo = Task::where('pid',$v['id'])->where('state',2)->count();
                    if ($count !== 0){
                        $str=round($counttwo/$count, 2);
                        $list[$k]['completionrate'] = $str."%";
                    }else{
                        $list[$k]['completionrate'] = "0%";
                    }

                }else{
                    $list[$k]['completionrate'] = "0%";
                }
                $user = explode(',',$v['undertakeUsers']);
                if (!empty($user)){
                    $list[$k]['undertakeUsers'] = ZnzjUsers::select('UserName','UserGUID')->whereIn('UserGUID',$user)->get()->toArray();
                }else{
                    $list[$k]['undertakeUsers'] = [];
                }
                $list[$k]['id'] = $v['id'];

                $son = Task::where('pid',$v['id'])->get()->toArray();
                if (!empty($son)){
                    foreach ($son as $k2=>$v2){
                        $list[$k]['son'][$k2]['taskname'] = $v2['taskname'];
                        $list[$k]['son'][$k2]['describe'] = $v2['describe'];
                        $list[$k]['son'][$k2]['weight'] = $v2['weight'];
                        $list[$k]['son'][$k2]['type'] = $v2['type'];
                        $list[$k]['son'][$k2]['achievements'] = 2; //绩效，1是 2否
                        $list[$k]['son'][$k2]['translate'] = $v2['translate'];
                        $list[$k]['son'][$k2]['starttime'] = $v2['starttime'];
                        $list[$k]['son'][$k2]['endtime'] = $v2['endtime'];
                        $list[$k]['son'][$k2]['remarks'] = $v2['remarks'];
                        $list[$k]['son'][$k2]['state'] = $v2['state'];
                        $list[$k]['son'][$k2]['status'] = 2;
                        $status = 2;
                        if ($status == 1){
                            $count = Task::where('pid',$v2['id'])->count();
                            $counttwo = Task::where('pid',$v2['id'])->where('state',2)->count();
                            if ($count !== 0){
                                $str=round($counttwo/$count, 2);
                                $list[$k]['son'][$k2]['completionrate'] = $str."%";
                            }else{
                                $list[$k]['son'][$k2]['completionrate'] = "0%";
                            }

                        }else{
                            $list[$k]['son'][$k2]['completionrate'] = "0%";
                        }
                        $user = explode(',',$v2['undertakeUsers']);
                        if (!empty($user)){
                            $list[$k]['son'][$k2]['undertakeUsers'] = ZnzjUsers::select('UserName','UserGUID')->whereIn('UserGUID',$user)->get()->toArray();
                        }else{
                            $list[$k]['son'][$k2]['undertakeUsers'] = [];
                        }
                        $list[$k]['son'][$k2]['id'] = $v2['id'];
                    }
                }else{
                    $list[$k]['son'] = [];
                }
            }
        }

        if (!empty($list)){
            $newnum = ['code'=>200,'msg'=>'请求成功','list'=>$list,'total'=>$arr['total'],'completionrate'=>$strtwo.'%'];
            return json_encode($newnum,JSON_UNESCAPED_UNICODE);
        }else{
            $newnum = ['code'=>400,'msg'=>'暂无数据'];
            return json_encode($newnum,JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * 任务详情
     */
    public function Taskdetail(Request $request){

    }

    /**
     * 删除任务
     */
    public function Taskdelete(Request $request){
        $id = $request->input('id');

        $del = Task::where('id',$id)->delete();
        if ($del){
            Taskcycle::where('pid',$id)->delete();
        }

        $newnum = ['code'=>200,'msg'=>'删除成功'];
        return json_encode($newnum,JSON_UNESCAPED_UNICODE);
    }

    /**
     * 周报列表
     */
    public function Weekly(Request $request){
        $created_at = date('Y-m-d', time());
        print_r($created_at);
    }
}
