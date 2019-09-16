<?php

namespace App\Http\Controllers;

use App\Models\MyLog;
use Illuminate\Http\Request;

use Validator;
use Input;
use Storage;

class MyLogController extends Controller
{
    protected $modelName = 'Log';
    protected $uri = 'myLog';

    public function index(Request $request){
        date_default_timezone_set('PRC');
        
        $this->filter['func'] = Input::get('func','all');
        $this->filter['start_date'] = Input::get('start_date',date('Y-m-d',strtotime('-7 days')));
        $this->filter['end_date'] = Input::get('end_date',date('Y-m-d'));
        $this->filter['type'] = Input::get('type','all');
        $this->filter['user_id'] = Input::get('user_id','all');

        $logList = MyLog::getMyLogs($this->filter);
        $titles = $this->getTitles();

        $exportData = array();
        $exportData['title'] = $titles;
        if(isset($logList)){
            foreach ($logList as $k => $v) {
                $temp = [];
                foreach ($titles as $title => $desc) {
                    if (isset($v[$title])) {
                        $temp[$title] = $v[$title];
                    } else {
                        $temp[$title] = '';
                    }
                }
                $exportData['data'][] = $temp;
            }
        }

        $pageData = [
            'pageInfo' => $this->pageInfo,
            'filter'=>$this->filter,
            'list' => $exportData,
            'funcList' => MyLog::getFuncList(),
            'typeList' => MyLog::getTypeList(),
        ];

        return $this->render(
            'admin.myLog_index',$pageData
        );
    }

    public function getTitles(){
        return [
            'created_at' => '执行方法',
            'func' => '执行方法',
            'type' => '执行方法',
            'info' => '执行方法',
            'user_id' => '执行方法',
        ];
    }

}



















