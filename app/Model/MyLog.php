<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class MyLog extends BaseModel
{
    protected $table = 'my_log';
    protected $tableColumns = [
        'func' => 'func',
        'info' => 'info',
        'type' => 'type',
        'params' => 'params',
        'user_id' => 'user_id',
    ];

    const LINE_LONG = '####################################################################################################';
    const LINE_SHORT_1 = '##########';
    const LINE_SHORT_2 = '####################';
    const LINE_SHORT_3 = '##############################';
    const LINE_SHORT_4 = '########################################';
    const LINE_SHORT_5 = '##################################################';
    const LINE_SHORT = self::LINE_SHORT_5;
    const LINE_SHORT_LEFT = '<<---------=== ';
    const LINE_SHORT_RIGHT = ' ===--------->>';
    const ENTER = "\n";

    const FACE_TANSHOU = "┑(￣Д ￣)┍";
    const FACE_GANGA = '⊙﹏⊙‖∣°';
    const FACE_HENGANGA = '>_<|||';
    const FACE_BISHI = '→_→';
    const FACE_PIG = '≡ (^(OO)^) ≡';
    const FACE_KUSI = '(;´༎ຶД༎ຶ`)';
    const FACE_MEIWEI = 'Ψ(￣∀￣)Ψ';

    const SYMBOL_ARROW_RIGHT = '➤';
    const SYMBOL_SAN = '☂';
    const SYMBOL_SUN = '☀';
    const SYMBOL_CLOUD = '☁';
    const SYMBOL_SNOW = '❆';
    const SYMBOL_TAIJI = '☯';
    const SYMBOL_MOON = '☾';
    const SYMBOL_PLANE = '✈';
    const SYMBOL_YE = '✌';

    public static $keyToTitle = array(
        'insertNum' => '插入数量',
        'updateNum' => '更新数量',
    );

    /**
     *  ####################################################################################################
     *  <<---------=== 开始时间:2019-06-05 18:52:59 ===--------->>
     *
     * @return false|string
     */
    public static function startTime(){
        $startTime_log = date('Y-m-d H:i:s');

        $msg = '';
        $msg .= self::LINE_LONG.self::ENTER;
        $msg .= self::LINE_SHORT_LEFT;
        $msg .= "开始时间:";
        $msg .= $startTime_log;
        $msg .= self::LINE_SHORT_RIGHT.self::ENTER;

        echo $msg;
        return $startTime_log;
    }

    /**
     *  <<---------=== 结束时间:2019-06-05 18:54:30 用时：91秒 ===--------->>
     *  ####################################################################################################
     *
     * @param $startTime_log
     */
    public static function endTime($startTime_log){
        $endTime_log = date('Y-m-d H:i:s');
        $times = strtotime($endTime_log) - strtotime($startTime_log);

        $msg = '';

        $msg .= self::LINE_SHORT_LEFT;
        $msg .= "结束时间:";
        $msg .= $endTime_log;
        $msg .= " 用时：".$times."秒";

        $msg .= self::LINE_SHORT_RIGHT.self::ENTER;


        $msg .= self::LINE_LONG.self::ENTER;

        echo $msg;
    }

    /**
     *
     * ################################################## 开始执行index ##################################################
     * ################################################## 时间跨度：2019-06-05-->>2019-07-01 ##################################################
     *
     *
     * @param $commandFunc
     * @param string $startDate
     * @param string $endDate
     */
    public static function startFunc($commandFunc,$startDate = '',$endDate = ''){
        $msg = '';

        $msg .= self::LINE_SHORT;
        $msg .= ' 开始执行'.$commandFunc.' ';
        $msg .= self::LINE_SHORT.self::ENTER;
        if(!empty($startDate)){
            $msg .= self::LINE_SHORT;
            $msg .= ' 时间跨度：'.$startDate." -->> ".$endDate." ";
            $msg .= self::LINE_SHORT.self::ENTER;
        }
        echo $msg;
    }

    /**
     *
     *  ########################### 这是消息啊！！！ ###########################
     *
     * @param $message
     * @param string $line
     */
    public static function msgWithLine($message,$line = ''){
        $msg = '';
        if(empty($line)){
            $line = self::LINE_SHORT;
        }

        $msg .= $line;
        $msg .= ' '.$message.' ';
        $msg .= $line.self::ENTER;

        echo $msg;
    }

    /**
     * ===--------->> 0 : index
     * ===--------->> 1 : 2019-06-05
     * ===--------->> 2 : 2019-07-01
     * @param $array
     * @return string
     */
    public static function arrayInfo($array){
        $msg = '';
        if(-1 == $array){
            $msg .= self::FACE_TANSHOU.''.self::ENTER;
        }
        if(is_array($array)){
            foreach ($array as $key => $value){
                if(!is_array($value)){
                    $msg .= self::LINE_SHORT_RIGHT.' '.self::getTitle($key)." : ".$value.self::ENTER;
                }
            }
        }

        echo $msg;
        return $msg;
    }


    /**
     *  ➤➤➤ 进入队列：fbqueue
     *
     * @param $message
     * @param string $line
     */
    public static function msgWithLeftLine($message,$line = ''){
        $msg = '';
        if(empty($line)){
            $line = '';
            for($i = 0;$i < 3;$i++){
                $line .= self::SYMBOL_ARROW_RIGHT;
            }
        }

        $msg .= $line;
        $msg .= ' '.$message.' ';
        $msg .= self::ENTER;

        echo $msg;
    }


    /**
     *
     *  ☂☂☂ 进入队列：fbqueue
     *
     * @param $message
     * @param string $symbol
     * @param int $times
     */
    public static function info($message,$symbol = '',$times = 3){
        $msg = '';
        if(empty($symbol)){
            $symbol = self::SYMBOL_SAN;
        }
        $line = '';
        for($i = 0;$i < $times;$i++){
            $line .= ' '.$symbol;
        }

        $msg .= $line;
        $msg .= ' '.$message.' ';
        $msg .= self::ENTER;

        echo $msg;
    }

    /**
     * @param $key
     * @return mixed
     */
    public static function getTitle($key){
        if(isset(self::$keyToTitle[$key])){
            return self::$keyToTitle[$key];
        }
        return $key;
    }

    public static function getThisModel(){
        return new MyLog();
    }


    public static function getMyLogs($filter = []){
        $selectData = [
                \DB::raw("func"),
                \DB::raw("info"),
                \DB::raw("type"),
                \DB::raw("params"),
                \DB::raw("user_id"),
                \DB::raw("created_at"),
            ];
        $query = self::onWriteConnection()->select($selectData);
        if($filter['func'] != 'all'){
            $query->where('func',$filter['func']);
        }
        if($filter['type'] != 'all'){
            $query->where('type',$filter['type']);
        }
        if($filter['user_id'] != 'all'){
            $query->where('user_id',$filter['user_id']);
        }

        $query->where('created_at',">=",$filter['start_date']);
        $filter['end_date'] = date('Y-m-d',strtotime($filter['end_date']) + 24 * 60 * 60);
        $query->where('created_at',"<=",$filter['end_date']);

        $query->orderBy('created_at','desc');

        return $query->get()->toArray();
    }

    public static function getFuncList(){
        return [
            'downloadVideoFromNas' => '从Nas下载视频',
            'deleteFilesFromNas' => '从Nas删除web目录下视频',
        ];
    }

    public static function getTypeList(){
        return [
            'info','danger','warning'
        ];
    }
}






















