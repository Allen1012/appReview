<?php
namespace App\Models;

use App\Model\BaseModel;
use App\Services\HttpService;

class AppleStoreDailyCountryReview extends BaseModel
{
    protected $table = DbTable::APPLE_STORE_DAILY_COUNTRY_REVIEW;

    protected $appStoreId = 'apple_id';

    protected $tableColumns = [
        'app' => 'app',
        'date' => 'date',
        'country'  => 'country',
        'apple_id'  =>  'apple_id',
        'review_id' =>  'review_id',
        'author'  =>  'author',
        'title'  =>  'title',
        'review'  => 'review',
        'vote_sum' => 'vote_sum',
        'vote_count' =>  'vote_count',
        'rating' =>  'rating',
        'version'  =>  'version',
    ];

    public function getSelectColumns(){
        return [
            \DB::raw('date as '.AppStore::DATE),
            \DB::raw('country as '.AppStore::COUNTRY),
            \DB::raw('rating as '.AppStore::RATING),
            \DB::raw('version as '.AppStore::VERSION),
            \DB::raw('title as '.AppStore::TITLE),
            \DB::raw('author as '.AppStore::AUTHOR),
            \DB::raw('review as '.AppStore::REVIEW),
        ];
    }

    public function getShowReviewTitle(){
        return [
            AppStore::DATE => '评论日期',
            AppStore::COUNTRY => '国家',
            AppStore::RATING =>  '评论星级',
            AppStore::VERSION =>  '应用版本',
            AppStore::TITLE =>  '评论标题',
            AppStore::AUTHOR =>  '用户',
            AppStore::REVIEW => '评论内容',
        ];
    }

    public static function downloadAllReviews($app = 'all'){

        $appList = AppleStoreReviewApps::getAppList($app);
        $countryList = AppleStoreReviewCountries::getCountryList();

//        $params['appVersion'] = 'current';
        $params['displayable-kind'] = '11';
        $params['startIndex'] = '0';
        $params['endIndex'] = '100';
        $params['sort'] = '1';

        foreach ($appList as $k => $appInfo){
            foreach ($countryList as $code_2 => $country){
                MyLog::info('开始获取'.$appInfo['app'].' '.$country.'数据：',MyLog::SYMBOL_ARROW_RIGHT);
                $params['id'] = $appInfo['apple_id'];
                $params['app'] = $appInfo['app'];
                $params['cc'] = $code_2;

                $reviewData = self::getAllReview($params);

                self::insterAppleStoreAllReviewDataToDb($reviewData,$params);
            }
        }
    }

    public static function insterAppleStoreAllReviewDataToDb($reviewData,$params){

        if(!empty($reviewData)){
            $data = [];
            foreach ($reviewData as $k => $v){
                if(!empty($v)){
                    $v['app'] = $params['app'];
                    $v['language'] = 'en';
                    $v['country'] = strtoupper($params['cc']);
                    $v['apple_id'] = $params['id'];
                    $data[] = $v;
                }
            }
            $appleStoreDailyCountryReview = new AppleStoreDailyCountryReview();

            $ret = $appleStoreDailyCountryReview->batchInsertOrUpdate($data);
            MyLog::arrayInfo($ret);

            $params['startIndex'] += 100;
            $params['endIndex'] += 100;
            $nextReviewData = self::getAllReview($params);

            if(!empty($nextReviewData)){
                self::insterAppleStoreAllReviewDataToDb($nextReviewData,$params);
            }

        }
    }

    public static function getAllReview($params){

        $url = 'https://itunes.apple.com/WebObjects/MZStore.woa/wa/userReviewsRow';
        $headers = [
            'User-Agent' => 'AppStore/2.0 iOS/9.3.2 model/iPhone7,2 hwp/t7000 build/13G34 (6; dt:106)',
        ];
        $ret = HttpService::curl_($url,$params,'get',0,$headers);

        $arr =  json_decode($ret,true);

        $reviewData = [];
        if(isset($arr['userReviewList'])){
            foreach ($arr['userReviewList'] as $k => $v){
                $temp = [];
                $temp['date'] = date('Y-m-d',strtotime($v['date']));
                $temp['review_id'] = $v['userReviewId'];
                $temp['title'] = $v['title'];
                $temp['review'] = $v['body'];
                $temp['author'] = $v['name'];
                $temp['vote_sum'] = $v['voteSum'];
                $temp['vote_count'] = $v['voteCount'];
                $temp['rating'] = $v['rating'];
                $reviewData[] = $temp;
            }
        }
//        dd($reviewData);
        return $reviewData;
    }


    public static function downloadReviews($app = 'all'){

        $appList = AppleStoreReviewApps::getAppList($app);
        $countryList = AppleStoreReviewCountries::getCountryList();

        $params['language'] = 'en';
        $params['dataType'] = 'xml';
        $params['page'] = 1;

        foreach ($appList as $k => $appInfo){
            foreach ($countryList as $code_2 => $country){
                MyLog::info('开始获取'.$appInfo['app'].' '.$country.'数据：',MyLog::SYMBOL_ARROW_RIGHT);
                $params['apple_id'] = $appInfo['apple_id'];
                $params['app'] = $appInfo['app'];
                $params['country'] = $code_2;

                $reviewData = self::getReview($params);
                self::insterAppleStoreReviewDataToDb($reviewData,$params);
            }
        }
    }

    public static function insterAppleStoreReviewDataToDb($reviewData,$params){

        if(!empty($reviewData['data'])){
            $data = [];
            foreach ($reviewData['data'] as $k => $v){
                if(!empty($v)){
                    $v['app'] = $params['app'];
                    $v['language'] = $params['language'];
                    $v['country'] = strtoupper($params['country']);
                    $v['apple_id'] = $params['apple_id'];
                    $data[] = $v;
                }
            }
            $appleStoreDailyCountryReview = new AppleStoreDailyCountryReview();

            $ret = $appleStoreDailyCountryReview->batchInsertOrUpdate($data);
            MyLog::arrayInfo($ret);

            if($reviewData['link']['last'] != $reviewData['link']['next']){
                $params['page'] += 1;
                $nextReviewData = self::getReview($params);
                if(!empty($nextReviewData)){
                    self::insterAppleStoreReviewDataToDb($nextReviewData,$params);
                }
            }
        }
    }


    public static function getReview($params){
        $params['urlDesc'] = '/customerreviews/page='.$params['page'].'/id='.$params['apple_id'].'/sortby=mostrecent/'.$params['dataType'];
        $url = 'https://itunes.apple.com/';
        $url .= $params['country'].'/rss'.$params['urlDesc'].'?l='.$params['language']."&urlDesc=".$params['urlDesc'];

        $ret = HttpService::curl_($url,[],'get');
        $ret = str_replace('im:','',$ret);

        $arr =  (json_decode(json_encode(simplexml_load_string($ret)),true));

        $reviewData = [];
        $reviewData['link'] = [];
        if(!empty($arr['link'])){
            foreach ($arr['link'] as $k => $linkInfo){
                $reviewData['link'][$linkInfo['@attributes']['rel']] = $linkInfo['@attributes']['href'];
            }
        }
//dd($arr);
        if(isset($arr['entry'])){
            foreach ($arr['entry'] as $k => $v){
                $temp = [];
                if(!isset($v['updated'])){
                    MyLog::info('数据异常！：',MyLog::FACE_HENGANGA);
                    var_dump($v);
                    continue;
                }
                $temp['date'] = date('Y-m-d',strtotime($v['updated']));
                $temp['review_id'] = $v['id'];
                $temp['title'] = $v['title'];
                $temp['review'] = $v['content'][0];
                $temp['author'] = $v['author']['name'];
                $temp['vote_sum'] = $v['voteSum'];
                $temp['vote_count'] = $v['voteCount'];
                $temp['rating'] = $v['rating'];
                $temp['version'] = $v['version'];
                $reviewData['data'][] = $temp;
            }
        }
//        dd($reviewData);
        return $reviewData;
    }

    public function getReviewData($filter = []){
//        dd($filter);
        $paginate = DbTable::PAGINATE;
        if(isset($filter['paginate']) && $filter['paginate'] > 0){
            $paginate = $filter['paginate'];
        }

        $query = self::onWriteConnection()->select($this->getSelectColumns())
            ->where('date',">=" , $filter['start_date'])
            ->where('date',"<=" , $filter['end_date']);
        if(isset($filter['version']) && $filter['version'] != 'all'){
            $query->where('version',$filter['version']);
        }
        if(isset($filter['rating']) && $filter['rating'] != 'all'){
            $query->where('rating',$filter['rating']);
        }
        if(isset($filter['country']) && $filter['country'] != 'all'){
            $query->where('country',$filter['country']);
        }
        $query->where('apple_id',$filter['app_store_id']);
        $query->orderBy('date','desc');

        return  $query->paginate($paginate);
    }

}