<?php
namespace App\Models;


use App\Services\HttpService;

class GooglePlayDailyCountryReview extends BaseModel
{
    protected $table = DbTable::GOOGLE_PLAY_DAILY_COUNTRY_REVIEW;

    protected $appStoreId = 'package';

    protected $tableColumns = [
        'app' => 'app',
        'date' => 'date',
        'author' => 'author',
        'rating' => 'rating',
        'review' => 'review',
        'language' => 'language',
    ];

    public function getSelectColumns(){
        return [
            \DB::raw('date as '.AppStore::DATE),
            \DB::raw('rating as '.AppStore::RATING),
            \DB::raw('language as '.AppStore::LANGUAGE),
            \DB::raw('author as '.AppStore::AUTHOR),
            \DB::raw('review as '.AppStore::REVIEW),
        ];
    }

    public function getSelectAllRatingColumns(){
        return [
            \DB::raw('count(*) as '.AppStore::COUNT),
            \DB::raw('rating as '.AppStore::RATING),
        ];
    }

    public function getShowReviewTitle(){
        return [
            AppStore::DATE => '评论日期',
            AppStore::RATING =>  '评论星级',
            AppStore::LANGUAGE =>  '评论语言',
            AppStore::AUTHOR =>  '用户',
            AppStore::REVIEW => '评论内容',
        ];
    }

    public static function getLanguageList(){
        return [
            'en',  //英语
            'es',  //西班牙语
            'zh',  //汉语
            'ru',  //俄语
            'de',  //德语
        ];
    }


    public static function downloadReviews($app = 'all'){

        $appList = GooglePlayReviewApps::getAppList($app);

        $languageList = self::getLanguageList();

        $params['reviewType'] = 1;
        $params['xhr'] = 1;
        $params['pageNum'] = 0;

        foreach ($appList as $k => $appInfo){
            foreach ($languageList as $kk => $language){
                MyLog::info('开始获取'.$appInfo['app'].' '.$language.'数据：',MyLog::SYMBOL_ARROW_RIGHT);
                $params['id'] = $appInfo['package'];
                $params['app'] = $appInfo['app'];
                $params['hl'] = $language;

                $reviewData = self::getReview($params);
                self::insterGooglePlayReviewDataToDb($reviewData,$params);
            }
        }
    }

    public static function insterGooglePlayReviewDataToDb($reviewData,$params){

        if(empty($reviewData)){
            MyLog::info('未获取到数据');
            return;
        }
        $data = [];
        foreach ($reviewData as $k => $v){
            if(!empty($v)){
                $v['app'] = $params['app'];
                $v['package'] = $params['id'];
                $v['language'] = $params['hl'];
                $v['md5'] = md5($v['package']."_".$v['date']."_".$v['author']."_".$v['language']."_".$v['review']);
                $data[] = $v;
            }
        }

        $googlePlayDailyCountryReview = new GooglePlayDailyCountryReview();
        $ret = $googlePlayDailyCountryReview->batchInsertOrUpdate($data);
        MyLog::arrayInfo($ret);

        $params['pageNum'] += 1;
        $nextReviewData = self::getReview($params);
        if(!empty($nextReviewData)){
            self::insterGooglePlayReviewDataToDb($nextReviewData,$params);
        }
    }

    public static function getReview($params){

        $url = 'https://play.google.com/store/getreviews';

        $ret = HttpService::curl_($url,$params,'post');
        $ret = str_replace(')]}\'','',$ret);
        $arr = json_decode($ret);
        if(!isset($arr[0][2])){
            MyLog::info('获取数据异常：',MyLog::FACE_GANGA);

            var_dump($ret);
            return null;
        }

        $reviewStr = $arr[0][2];
        if(empty($reviewStr)){
            return null;
        }

        $reviewStr = str_replace('<div class="single-review" tabindex="0"> ','<div class="single-review" tabindex="0">  review_start ',$reviewStr);
        $reviewStr = str_replace('<div class="tiny-star star-rating-non-editable-container" aria-label="','<div class="tiny-star star-rating-non-editable-container" aria-label=""> ',$reviewStr);
        $reviewStr = str_replace('<div class="review-info-star-rating"> ','<div class="review-info-star-rating">  review_return stars:_review_:',$reviewStr);
        $reviewStr = str_replace('<span class="author-name">','<span class="author-name">  author:_review_:',$reviewStr);
        $reviewStr = str_replace('<span class="review-date">','<span class="review-date"> review_return date:_review_:',$reviewStr);
        $reviewStr = str_replace('<span class="review-title">','<span class="review-title"></span>  review_return review:_review_:',$reviewStr);

        $reviewStr = strip_tags($reviewStr);

        $reviewStr = self::getEnFullReview($reviewStr,$params['hl']);

        $arr = explode('review_start',$reviewStr);

        $reviews = [];
        foreach ($arr as $k => $v){
            $reviews[] = explode('review_return',$v);
        }

        $reviewData = [];
        foreach ($reviews as $k => $reviewInfo){
            if(!empty($reviewInfo)){
                $temp = [];
                foreach ($reviewInfo as $kk => $val){
                    $val = trim($val);

                    if($kk < 5 && !empty($val)){

                        $info = explode(':_review_:',$val);
                        if(empty($info[1])){
                            MyLog::info('数据异常：val',MyLog::FACE_HENGANGA);
                            var_dump($params);
                            var_dump($val);
                            continue;
                        }
                        if('stars' == $info[0]){
                            $info[0] = 'rating';
                            $info[1] = self::getEnRated($info[1],$params['hl']);
                        }
                        if('date' == $info[0]){
                            $info[1] = self::getEnDate($info[1],$params['hl']);
                        }
                        $temp[$info[0]] = trim($info[1]);

                    }
                }
                $reviewData[] = $temp;
            }
        }
        return $reviewData;
    }

    public static function getEnRated($str,$language = 'en'){
        $str = trim($str);
        switch ($language){
            case "en":
                $str = str_replace('Rated ','',$str);
                break;
            case "zh":
                $str = str_replace('评了','',$str);
                break;
            case 'es':
                $str = str_replace('Valoración:','',$str);
                break;
            case 'ru':
                $str = str_replace('Средняя оценка:','',$str);
                break;
            case 'de':
                $str = str_replace('Mit','',$str);
                break;
        }
        return $str;
    }

    public static function getEnFullReview($reviewStr,$language = 'en'){
        switch ($language){
            case "en":
                $reviewStr = str_replace('Full Review',' review_return ',$reviewStr);
                $reviewStr = str_replace('stars out of five stars "> ','review_return',$reviewStr);
                break;
            case "zh":
                $reviewStr = str_replace('全文',' review_return ',$reviewStr);
                $reviewStr = str_replace('颗星（最高5颗星）"> ','review_return',$reviewStr);
                break;
            case 'es':
                $reviewStr = str_replace('Opinión completa',' review_return ',$reviewStr);
                $reviewStr = str_replace('estrellas de cinco"> ','review_return',$reviewStr);
                break;
            case 'ru':
                $reviewStr = str_replace('Читать дальше',' review_return ',$reviewStr);
                $reviewStr = str_replace(' из 5"> ','review_return',$reviewStr);
                break;
            case 'de':
                $reviewStr = str_replace('Vollständige Rezension',' review_return ',$reviewStr);
                $reviewStr = str_replace('von fünf Sternen bewertet"> ','review_return',$reviewStr);
                break;
        }
        return $reviewStr;
    }
    public static function getEnDate($str,$language = 'en',$dateTpl = 'Y-m-d'){
        switch ($language){
            case 'es':
                $str = str_replace('enero',1,$str);
                $str = str_replace('febrero',2,$str);
                $str = str_replace('marzo',3,$str);
                $str = str_replace('abril',4,$str);
                $str = str_replace('mayo',5,$str);
                $str = str_replace('junio',6,$str);
                $str = str_replace('julio',7,$str);
                $str = str_replace('agosto',8,$str);
                $str = str_replace('septiembre',9,$str);
                $str = str_replace('octubre',10,$str);
                $str = str_replace('noviembre',11,$str);
                $str = str_replace('diciembre',12,$str);
                $str = str_replace(' ','',$str);
                $arr = explode('de',$str);
                $str = $arr[2].'-'.$arr[1].'-'.$arr[0];
                break;
            case 'zh':
                $str = str_replace('年','-',$str);
                $str = str_replace('月','-',$str);
                $str = str_replace('日','',$str);
                break;
            case 'ru':
                $str = str_replace('января','-1-',$str);
                $str = str_replace('февраля','-2-',$str);
                $str = str_replace('марта','-3-',$str);
                $str = str_replace('апреля','-4-',$str);
                $str = str_replace('мая','-5-',$str);
                $str = str_replace('июня','-6-',$str);
                $str = str_replace('июля','-7-',$str);
                $str = str_replace('августа','-8-',$str);
                $str = str_replace('сентября','-9-',$str);
                $str = str_replace('октября','-10-',$str);
                $str = str_replace('ноября','-11-',$str);
                $str = str_replace('декабря','-12-',$str);
                $str = str_replace(' ','',$str);
                $str = str_replace('г.','',$str);
                $arr = explode('-',$str);
//                dd($arr);
                $str = $arr[2].'-'.$arr[1].'-'.$arr[0];
                break;
            case 'de':
                $str = str_replace('. Januar','-1-',$str);
                $str = str_replace('. Februar','-2-',$str);
                $str = str_replace('. März','-3-',$str);
                $str = str_replace('. April','-4-',$str);
                $str = str_replace('. Mai','-5-',$str);
                $str = str_replace('. Juni','-6-',$str);
                $str = str_replace('. Juli','-7-',$str);
                $str = str_replace('. August','-8-',$str);
                $str = str_replace('. September','-9-',$str);
                $str = str_replace('. Oktober','-10-',$str);
                $str = str_replace('. November','-11-',$str);
                $str = str_replace('. Dezember','-12-',$str);
                $str = str_replace(' ','',$str);
                $arr = explode('-',$str);
//                dd($arr);
                $str = $arr[2].'-'.$arr[1].'-'.$arr[0];
                break;
        }
        $date = date($dateTpl,strtotime($str));
        return $date;
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

        if(isset($filter['rating']) && $filter['rating'] != 'all'){
            $query->where('rating',$filter['rating']);
        }
        if(isset($filter['language']) && $filter['language'] != 'all'){
            $query->where('language',$filter['language']);
        }
        $query->where('package',$filter['app_store_id']);
        $query->orderBy('date','desc');

        return  $query->paginate($paginate);
    }


}