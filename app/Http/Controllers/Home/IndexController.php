<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class IndexController extends Controller
{


    protected $app;
    protected $uri = 'index';
    protected $modelName = 'base';
    protected $pageInfo = [];
    protected $filter = [];
    protected $backData = [];


    public function __construct()
    {

    }

    public function index(){

        $url = route('profile');
//        h？
        dd($url);

        $days = ['30','28','23','22','21','16','15','12','10','09','07','05','03','02'];
        for ($i = count($days) -1; $i >= 0; $i--){
            echo $days[$i]."<br>";
        }

        die();

//        $r = Log::debug('An informational message.');
//        Log::emergency('The system is down!');
        Log::info('hahah',['ni' => "hao",'wo' => 'henhao!']);
//        dd($r);

        die();

        $arr = [
            'a' => 'aa',
            'b' => 'bb',
        ];

        $t = array_shift($arr);
        dd($t);


        return view(
            'Home.'.$this->uri.".".__FUNCTION__,
            [


            ]
        );
    }

    /**
     * 一个基础功能的测试。
     *
     * @return void
     */
    public function testBasicExample()
    {
        Cache::shouldReceive('get')
            ->with('key')
            ->andReturn('value');

//        $this->visit('/cache')
//            ->see('value');
    }

}
