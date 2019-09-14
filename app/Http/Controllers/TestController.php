<?php

namespace App\Http\Controllers;



use App\Model\AdsDailyCampaignReport;
use App\Model\AdsDailyCountryCampaignReport;
use App\Model\BaseModel;

class TestController extends Controller
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

    public static function getLeftTopSteps($steps){
        $leftTopStep = [];
        $count = count($steps);
        for($i = 0 ; $i < $count ; $i++){
            if(!isset($steps[$i-1])){
                $leftTopStep[$i] = 0;
            }else{
                if($steps[$i-1] > $leftTopStep[$i-1]){
                    $leftTopStep[$i] = $steps[$i-1];
                }else{
                    $leftTopStep[$i] = $leftTopStep[$i-1];
                }
            }
        }
        return $leftTopStep;
    }

    public function index(){

        date_default_timezone_set("PRC");
//        $json = '{"173933459864604":[{"type":"ads_insights","call_count":1,"total_cputime":3,"total_time":4,"estimated_time_to_regain_access":0}]}';
//        $ret = json_decode($json,true);
//        foreach ($ret as $accountId => $value){
//            foreach ($value as $index => $vv){
//                dd($vv);
//                foreach ($vv as $key => $val){
//                    echo $accountId."_".$index."_".$key."_".$val."<br>";
//                }
//            }
//
//        }
//        die();
//        $json = '{"week":{"all":{"Start Introductory Offer":{"\u5468\u8ba2\u96052.99(\u6709\u8bd5\u7528)":7,"\u5468\u8ba2\u96053.99(\u6709\u8bd5\u7528)":2},"realIncome":3.8224706295002,"Subscribe":{"\u5468\u8ba2\u96052.99(\u65e0\u8bd5\u7528)":3},"roiIncome":19.112353147501,"Reactivate":{"\u5468\u8ba2\u96052.99(\u6709\u8bd5\u7528)":1}},"ARE":{"Start Introductory Offer":{"\u5468\u8ba2\u96052.99(\u6709\u8bd5\u7528)":1},"realIncome":0},"CAN":{"Start Introductory Offer":{"\u5468\u8ba2\u96052.99(\u6709\u8bd5\u7528)":1},"realIncome":0},"ARG":{"Start Introductory Offer":{"\u5468\u8ba2\u96052.99(\u6709\u8bd5\u7528)":1},"realIncome":0},"RUS":{"Subscribe":{"\u5468\u8ba2\u96052.99(\u65e0\u8bd5\u7528)":3},"realIncome":0.92433275469192,"roiIncome":4.6216637734596,"Start Introductory Offer":{"\u5468\u8ba2\u96053.99(\u6709\u8bd5\u7528)":1}},"PHL":{"Start Introductory Offer":{"\u5468\u8ba2\u96052.99(\u6709\u8bd5\u7528)":1},"realIncome":0},"BRA":{"Reactivate":{"\u5468\u8ba2\u96052.99(\u6709\u8bd5\u7528)":1},"realIncome":2.8981378748083,"roiIncome":14.490689374041},"GBR":{"Start Introductory Offer":{"\u5468\u8ba2\u96052.99(\u6709\u8bd5\u7528)":1},"realIncome":0},"ESP":{"Start Introductory Offer":{"\u5468\u8ba2\u96053.99(\u6709\u8bd5\u7528)":1},"realIncome":0},"USA":{"Start Introductory Offer":{"\u5468\u8ba2\u96052.99(\u6709\u8bd5\u7528)":2},"realIncome":0}}}';
//        echo $json;
//        die();

        $steps = [0,1,0,2,5,0,1,3,2,4,0,2,0,1];
        $steps = [0,1,0,2,1,0,1,3,2,1,2,1];
//        $reverseSteps = array_reverse($steps);
//        dd($reverseSteps);

        $leftTopStep = self::getLeftTopSteps($steps);
        $rightTopStep = self::getLeftTopSteps(array_reverse($steps));
        $rightTopStep = array_reverse($rightTopStep);

        $data = [
            'steps' => $steps,
            'leftTopStep' => $leftTopStep,
            'rightTopStep' => $rightTopStep,
        ];
//        dd($data);
        $sum = 0;
        foreach ($steps as $k => $high){
            $weater = 0;
            if($steps[$k] < $leftTopStep[$k] && $steps[$k] < $rightTopStep[$k]){
                $weater = $leftTopStep[$k] - $steps[$k];
                if($weater > ($rightTopStep[$k] - $steps[$k])){
                    $weater = $rightTopStep[$k] - $steps[$k];
                }
                $sum += $weater;
            }
            $weaterStep[$k] = $weater;
        }
        $data['waterStep'] = $weaterStep;
        $data['waterSum'] = $sum;
        dd($data);
        dd($rightTopStep);
        $RightTopStep = [];
        $count = count($steps);
        for($i = 0 ; $i < $count ; $i++){
            if(!isset($steps[$i-1])){
                $leftTopStep[$i] = 0;
            }else{
                if($steps[$i-1] > $leftTopStep[$i-1]){
                    $leftTopStep[$i] = $steps[$i-1];
                }else{
                    $leftTopStep[$i] = $leftTopStep[$i-1];
                }
            }
        }
        dd($leftTopStep);

//        $model = new AdsDailyCampaignReport();
//        $model->apple_id = date('mdHis');
//        $model->date = date('Y-m-d H:i:s');
//        $model->campaign_id = date('dHis');
//        $model->campaign = 'hahah';
//        $model->installs = 125;
//        $model->spent = 234;
//        $model->save();
//
//        dd($model->id);
        die();

        $data = [
            ['apple_id' => '591888','date' => '2019-11-12','country' => 'US','campaign_id' => '123456','campaign' => 'campaign_name_1','installs' => '12', 'spent' => '34'],
            ['apple_id' => '591888','date' => '2019-11-11','country' => 'CN','campaign_id' => '123457','campaign' => 'campaign_name_2','installs' => '1231', 'spent' => '3441'],
        ];

        $adsDailyCountryCampaign = new AdsDailyCountryCampaignReport();
        $ret = $adsDailyCountryCampaign->batchInsertOrUpdate($data);
        dd($ret);


        $data = [
            ['apple_id' => '591888','date' => '2019-11-12','campaign_id' => '123456','campaign' => 'campaign_name_1','installs' => '12', 'spent' => '34'],
            ['apple_id' => '591888','date' => '2019-11-11','campaign_id' => '123457','campaign' => 'campaign_name_2','installs' => '1231', 'spent' => '3441'],
        ];

        $adsDailyCampaign = new AdsDailyCampaignReport();
        $ret = $adsDailyCampaign->batchInsertOrUpdate($data);
        dd($ret);



        $data = [
            ['apple_id' => '591888','date' => '2019-11-11','campaign_id' => '123456','campaign' => 'campaign_name_1','installs' => '12', 'spent' => '34'],
            ['apple_id' => '591888','date' => '2019-11-11','campaign_id' => '123457','campaign' => 'campaign_name_2','installs' => '123', 'spent' => '344'],
        ];
        $table = 'ads_daily_campaign_report';
        $columns = ['apple_id','date','campaign_id','campaign','installs','spent','updated_at'];
        $baseModel = new BaseModel();
        $ret = $baseModel->batchInsertOrUpdate($data,$table,$columns);
        dd($ret);




        $url = route('profile');
        dd($url);




        return view(
            'Home.'.$this->uri.".".__FUNCTION__,
            [


            ]
        );
    }


}
