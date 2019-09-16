<?php


namespace App\Http\Controllers\Home;

use App\Models\AppleStoreDailyCountryReview;
use App\Models\AppleStoreReviewApps;
use App\Models\AppleStoreReviewCountries;
use App\Models\AppStore;
use App\Models\BaseModel;
use App\Models\DbTable;
use App\Models\GooglePlayDailyCountryReview;
use App\Models\GooglePlayReviewApps;
use App\Models\ReportCountry;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\App;
use Validator;
use Input;
use Storage;
use Excel;

class AppStoreReviewController extends HomeController
{
    protected $modelName = 'AppStore评论';
    protected $uri = 'appStoreReview';

    public function index(Request $request){

        $this->pageInfo['title'] = $this->modelName;

        $isExport = Input::get('is_export',0);

        $this->filter['select_app'] = Input::get('select_app','');
        $this->filter['status'] = Input::get('status','all');

        $this->filter['start_date'] = Input::get('start_date',date('Y-m-d',strtotime('-1 month')));
        $this->filter['end_date'] = Input::get('end_date',date('Y-m-d'));
        $this->filter['language'] = Input::get('language','all');
        $this->filter['country'] = Input::get('country','all');
        $this->filter['version'] = Input::get('version','all');
        $this->filter['rating'] = Input::get('rating','all');
        $this->filter['paginate'] = Input::get('paginate','100');
        $this->filter['is_open_word_cloud'] = Input::get('is_open_word_cloud','0');

        $this->filter['selected_appStore_id'] = Input::get('selected_appStore_id',AppStore::APPLE_STORE.':_: ');
        $appStore_idArr = explode(':_:',$this->filter['selected_appStore_id']);
        $this->filter['app_store'] = $appStore_idArr[0];
        $this->filter['app_store_id'] = isset($appStore_idArr[1]) ? $appStore_idArr[1] : '';

        $appInfo = (new AppStoreAppsController())->getAppInfo($this->filter['app_store_id'],$this->filter['app_store']);

        $googlePlayAppList = GooglePlayReviewApps::getAppListForSelect();
        $appleStoreAppList = AppleStoreReviewApps::getAppListForSelect();
        $appListForSelect = array_merge(json_decode($googlePlayAppList,true),json_decode($appleStoreAppList,true));

        if(AppStore::APPLE_STORE == $this->filter['app_store']){
            $model = new AppleStoreDailyCountryReview();
        }elseif(AppStore::GOOGLE_PLAY == $this->filter['app_store']){
            $model = new GooglePlayDailyCountryReview();
        }else{
//            return back()->withErrors('请选择正确的应用商店！');
        }

        $reviewData = $model->getReviewData($this->filter);
        $tableTitles = $model->getShowReviewTitle();
        $reviewDataArr = $reviewData->toArray();

        $wordCloud = [];
        if(AppStore::APPLE_STORE == $this->filter['app_store'] && $this->filter['is_open_word_cloud']){
            $wordCloud = $this->getWordCloud(array_column($reviewDataArr['data'],AppStore::TITLE));
        }

        $starData = $this->getFormatDataForRating(array_column($reviewDataArr['data'],AppStore::RATING));
        $ratingData = $this->getRatingData($starData,'当前筛选评分');
        $allRatingGroupData = $model->getAllRatingGroupData($this->filter['app_store_id']);
        $allRatingData = $this->getRatingData($allRatingGroupData,'全部评分');

        $countryList = ReportCountry::getShowCountryList_justCountry();

        $showData = [];
        $showData['title'] = $tableTitles;
        if(!empty($reviewDataArr['data'])){
            foreach ($reviewDataArr['data'] as $k => $v){
                $temp = [];
                foreach ($tableTitles as $title => $desc){
                    if(isset($v[$title])){
                        if(AppStore::DATE == $title){
                            $v[$title] = date('Y-m-d',strtotime($v[$title]));
                        }
                        if($isExport == false){
                            if(AppStore::RATING == $title){
                                $v[$title] = $this->getRatingHtml($v[$title]);
                            }elseif(AppStore::COUNTRY == $title){
                                $v[$title] = $countryList[$v[$title]];
                            }
                        }
                        $temp[] = $v[$title];
                    }else {
                        $temp[] = '';
                    }
                }
                $showData['data'][] = $temp;
            }
        }
        if(1 == $isExport){
            $exportDataList = $this->getExportData($showData);
            Excel::create(trim($this->filter['select_app']).'_review_'.$this->filter['start_date']."~".$this->filter['end_date'],function($excel) use ($exportDataList){
                $excel->sheet('account', function($sheet) use ($exportDataList){
                    $sheet->rows($exportDataList);
                });
            })->export('xls');
        }

        return $this->render(
            'admin.' . $this->uri . '_index',
            [

                'pageInfo'         => $this->pageInfo,
                'appInfo'         => $appInfo,
                'wordCloud'         => json_encode($wordCloud),
                'showData'         => $showData,
                'ratingData'         => $ratingData,
                'allRatingData'         => $allRatingData,
                'list'         => $reviewData,
                'filter'         => $this->filter,
                'ratingList'         => $this->getRatingList(),
                'appListForSelect'         => json_encode($appListForSelect),
                'appleStoreCountries'         => AppleStoreReviewCountries::getCountryList(),
                'googlePlayLanguages'         => GooglePlayDailyCountryReview::getLanguageList(),
            ]
        );
    }

    public function getFormatReviewData($reviews,$titles,$countryList,$isExport = false){
        $showData = [];
        if(!empty($reviews)){
            foreach ($reviews as $k => $v){
                $temp = [];
                foreach ($titles as $title => $desc){
                    if(isset($v[$title])){
                        if(AppStore::DATE == $title){
                            $v[$title] = date('Y-m-d',strtotime($v[$title]));
                        }
                        if($isExport == false){
                            if(AppStore::RATING == $title){
                                $v[$title] = $this->getRatingHtml($v[$title]);
                            }elseif(AppStore::COUNTRY == $title){
                                $v[$title] = $countryList[$v[$title]];
                            }
                        }
                        $temp[] = $v[$title];
                    }else {
                        $temp[] = '';
                    }
                }
                $showData[] = $temp;
            }
        }
        return $showData;
    }

    public function getCheckReviewTableTitle(){
        return [
            AppStore::REVIEW => [
                'value' => AppStore::REVIEW,
                'attr' => [
                    'class' => "text-center col-xs-4",
                    'data-toggle' => "tooltip" ,
                    'data-placement' => "top",
                    'title' => '评论内容',
                ],
            ],
            AppStore::APP_STORE => [
                'value' => AppStore::APP_STORE,
                'attr' => [
                    'class' => "text-center",
                    'data-toggle' => "tooltip" ,
                    'data-placement' => "top",
                    'title' => '应用商店',
                ],
            ],
            AppStore::DATE => [
                'value' => AppStore::DATE,
                'attr' => [
                    'class' => "text-center",
                    'data-toggle' => "tooltip" ,
                    'data-placement' => "top",
                    'title' => '评论日期',
                ],
            ],
            AppStore::APP => [
                'value' => AppStore::APP,
                'attr' => [
                    'class' => "text-center",
                    'data-toggle' => "tooltip" ,
                    'data-placement' => "top",
                    'title' => '应用',
                ],
            ],
            AppStore::APP_STORE_ID => [
                'value' => AppStore::APP_STORE_ID,
                'attr' => [
                    'class' => "text-center",
                    'data-toggle' => "tooltip" ,
                    'data-placement' => "top",
                    'title' => '应用ID',
                ],
            ],
            AppStore::COUNTRY => [
                'value' => AppStore::COUNTRY,
                'attr' => [
                    'class' => "text-center",
                    'data-toggle' => "tooltip" ,
                    'data-placement' => "top",
                    'title' => '国家',
                ],
            ],
            AppStore::LANGUAGE => [
                'value' => AppStore::LANGUAGE,
                'attr' => [
                    'class' => " text-center",
                    'data-toggle' => "tooltip" ,
                    'data-placement' => "top",
                    'title' => '语言',
                ],
            ],
            AppStore::RATING =>  [
                'value' => AppStore::RATING,
                'attr' => [
                    'class' => " text-center",
                    'data-toggle' => "tooltip" ,
                    'data-placement' => "top",
                    'title' => '评论星级',
                ],
            ],
            AppStore::VERSION =>  [
                'value' => AppStore::VERSION,
                'attr' => [
                    'class' => " text-center",
                    'data-toggle' => "tooltip" ,
                    'data-placement' => "top",
                    'title' => '应用版本',
                ],
            ],

            AppStore::AUTHOR =>  [
                'value' => AppStore::AUTHER,
                'attr' => [
                    'class' => " text-center",
                    'data-toggle' => "tooltip" ,
                    'data-placement' => "top",
                    'title' => '用户',
                ],
            ],

        ];
    }
    public function checkReview(){

        $this->pageInfo['title'] = '评论查重';
        $this->pageInfo['post_link'] = url('admin/'.__FUNCTION__);

        $this->filter['reviews'] = Input::get('reviews','');
        $reviews = explode("\r\n",$this->filter['reviews']);
        foreach ($reviews as $k => $v){
            $reviews[$k] = trim($v);
            if(empty($reviews[$k])){
                unset($reviews[$k]);
            }
        }

        $tableTitle = $this->getCheckReviewTableTitle();
        $reviewData = $this->getAllSameReviews($reviews);

        $unSameReviews = [];
        foreach ($reviews as $k => $v){
            if(!isset($reviewData[$v])){
                $unSameReviews[] = $v;
            }
        }

        $tableData[] = $tableTitle;
        foreach ($reviewData as $review => $reviewList){
            foreach ($reviewList as $reviewIndex => $vv){
                $temp = [];
                foreach ($tableTitle as $title => $titleData){
                    $temp[$title]['attr']['class'] = 'text-center';
                    if($title == AppStore::REVIEW){
                        if(0 == $reviewIndex){
                            $temp[$title]['attr']['class'] .= ' col-sm-4';
                            $temp[$title]['attr']['rowspan'] = count($reviewList);
                        }else{
                            unset($temp[$title]);
                            continue;
                        }
                    }
                    if($title == AppStore::APP_STORE){

                        if(AppStore::APPLE_STORE == $vv[$title]){
                            $temp[$title]['attr']['class'] .= ' text-info';
                        }else{
                            $temp[$title]['attr']['class'] .= ' text-warning';
                        }
                    }
                    if(isset($vv[$title])){
                        $temp[$title]['value'] = $vv[$title];
                    }else{
                        $temp[$title]['value'] = '--';
                    }

                }
                $tableData[] = $temp;
            }
        }
//        dd($tableData);
        return $this->render(
            'admin.' . __FUNCTION__ . '_index',
            [
                'pageInfo'         => $this->pageInfo,
                'filter'         => $this->filter,
                'reviewData'        => $reviewData,
                'tableData'        => $tableData,
                'unSameReviews'        => $unSameReviews,
            ]
        );
    }

    public function getAllSameReviews($reviews,$isExport = false){
        $data = [];
        $model = new AppleStoreDailyCountryReview();
        $appleStoreReviews = $model->getAllSameReviews($reviews);
//        dd($appleStoreReviews);
        foreach ($appleStoreReviews as $k => $v) {
            $v[AppStore::APP_STORE] = AppStore::APPLE_STORE;
            $v[AppStore::RATING] = $this->getRatingHtml($v[AppStore::RATING]);
            $v[AppStore::DATE] = substr($v[AppStore::DATE],0,10);

            $data[$v[AppStore::REVIEW]][] = $v;
        }

        $model = new GooglePlayDailyCountryReview();
        $googlePlayReviews = $model->getAllSameReviews($reviews);
        foreach ($googlePlayReviews as $k => $v) {
            $v[AppStore::APP_STORE] = AppStore::GOOGLE_PLAY;
            $v[AppStore::RATING] = $this->getRatingHtml($v[AppStore::RATING]);

            $data[$v[AppStore::REVIEW]][] = $v;
        }
        return $data;
    }

    public function getRatingData($starData,$title){
        $color = [
           1 => 'progress-bar-striped',
           2 => 'progress-bar-success progress-bar-striped',
           3 => 'progress-bar-info progress-bar-striped',
           4 => 'progress-bar-warning progress-bar-striped',
           5 => 'progress-bar-danger progress-bar-striped',
        ];
        $stars = 0;

        $ratingData['title'] = $title;
        $ratingData['count'] = $starData['count'];
        for($i = 5 ; $i >= 1 ; $i--){
            if(isset($starData['data'][$i]) && $ratingData['count'] > 0){
                $ratingData['data'][$i]['num'] = $starData['data'][$i];
                $ratingData['data'][$i]['rate'] = get_format_divide($starData['data'][$i] * 100, $ratingData['count'],0);

                $stars += $i * $starData['data'][$i];
            }else{
                $ratingData['data'][$i]['num'] = 0;
                $ratingData['data'][$i]['rate'] = '0' ;
            }
            $ratingData['data'][$i]['color'] = $color[$i];
        }
        $ratingData['average'] = get_format_divide($stars,$ratingData['count'],1);
        $ratingData['average_star'] = $this->getRatingHtml($ratingData['average']);
//        dd($ratingData);
        return $ratingData;
    }

    public function getFormatDataForRating($data){
        $starData['count'] = 0;
        foreach ($data as $star) {
            if($star > 0){
                if(isset($starData['data'][$star])){
                    $starData['data'][$star]++;
                }else{
                    $starData['data'][$star] = 1;
                }
                $starData['count']++;
            }
        }
        return $starData ;
    }

    public function getWordCloud($data){

        $titleData = [];
        $allCount = 0;
        foreach ($data as $k => $str){

            $char = "。、！？：；﹑•＂…‘’“”〝〞∕¦‖—　〈〉﹞﹝「」‹›〖〗】【»«』『〕〔》《﹐¸﹕︰﹔！¡？¿﹖﹌﹏﹋＇´ˊˋ―﹫︳︴¯＿￣﹢﹦﹤‐­˜﹟﹩﹠﹪﹡﹨﹍﹉﹎﹊ˇ︵︶︷︸︹︿﹀︺︽︾ˉ﹁﹂﹃﹄︻︼（）";

            $pattern = array(
                "/[[:punct:]]/i", //英文标点符号
                '/['.$char.']/u', //中文标点符号
                '/[ ]{2,}/'
            );
            $str = preg_replace($pattern, ' ', $str);
            $str = trim($str);
            if(preg_match("/^[a-zA-Z\s]+$/",$str)){
                $str = strtolower($str);
                $str = ucfirst($str);
            }

            if($str == null){
                continue;
            }else{
                $allCount++;
            }

            if(isset($titleData[$str])){
                $titleData[$str]++;
            }else{
                $titleData[$str] = 1;
            }
        }
        if($allCount == 0){
            return [];
        }

        $wordCould = [];

        foreach ($titleData as $title => $count){
            $wordCould[] = [
                'text' => $title,
                'weight' => $count / $allCount * 100,
                'html' => [
                    'title' =>  $count." times",
                    'class' =>  "custom-class"
                    ],
            ];
        }
        return $wordCould;
    }

    public function getRatingList(){
        $ratingList = [];
        for($i = 1 ; $i <= 5 ; $i++){
            $ratingList[$i] = $this->getRatingHtml($i);
        }
        return $ratingList;
    }

    public function getRatingHtml($num,$allNum = 5){
        $stars = '';
        $numInt = intval($num);
        for($i = 1 ; $i <= $allNum ; $i++){
            if($i <= $numInt){
                $stars .= '<i class="fa fa-star fa-lg text-yellow"></i>';
            }else{
                if(($i - 1) < $num){
                    $stars .= '<i class="fa fa-star-half-o fa-lg text-yellow"></i>';
                }else{
                    $stars .= '<i class="fa fa-star-o fa-lg text-yellow"></i>';
                }
            }
        }
        return $stars;
    }

}



















