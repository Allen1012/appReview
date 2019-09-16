<?php
namespace App\Models;

use App\Services\HttpService;

class GooglePlayReviewApps extends BaseModel
{
    protected $table = DbTable::GOOGLE_PLAY_REVIEW_APPS;

    protected $tableColumns = [
        'app' => 'app',
        'package' => 'package',
    ];

    public static function getAppList($app = 'all'){
        $query = self::onWriteConnection()->select('app','package','full_name')->where('status',0);
        if('all' != $app){
            $query->where('app',$app);
        }
        return $query->get()->toArray();
    }

    public static function getPageQuery($filter = array())
    {
        $perPage = DbTable::PAGINATE;

        $query = self::onWriteConnection()->select('id','app',\DB::raw('package as app_store_id'),'full_name','status','created_at');

        if(isset($filter['status']) && $filter['status'] != 'all'){
            $query->where('status',$filter['status']);
        }

        $query->orderBy('id', 'desc');

        if(isset($filter['paginate']) && $filter['paginate'] > 0){
            $perPage = $filter['paginate'];
        }
        return $query->paginate($perPage);
    }

    public static function getAppInfoFromStore($id){

        $url = 'https://play.google.com/store/apps/details?hl=en&id='.$id;
        $html = HttpService::curl_($url,[],'get');
        $firstIndex = stripos($html,'<img src=');
        $endIndex = strripos($html,'<img src=');

        $subHtml = substr($html,$firstIndex,($endIndex - $firstIndex));
        $src = substr($subHtml,10,(stripos($subHtml,'" srcset=')-10));

        $firstSpanIndex = stripos($subHtml,'<span >');
        $firstEndSpanIndex = stripos($subHtml,'</span>');
        $appName = substr($subHtml,($firstSpanIndex + 7), $firstEndSpanIndex - $firstSpanIndex - 7);

        $appInfo['full_name'] = $appName;
        $appInfo['app_img_src'] = $src;
        return $appInfo;
    }


    public static function getAppListForSelect( ){
        $appList = self::getAppList();
        $data = [];
        foreach ($appList as $k => $v) {
            $temp['value'] = $v['app']." ".AppStore::GOOGLE_PLAY;
            $temp['id'] = AppStore::GOOGLE_PLAY.":_:".$v['package'];
            $data[] = $temp;
        }
        return json_encode($data);
    }

    public static function getAppInfo($appStoreId){
        $model = self::onWriteConnection()->select('app','img','full_name')->where('package',$appStoreId)->first();
        if(empty($model)){
            return [];
        }
        return $model->toArray();
    }


}