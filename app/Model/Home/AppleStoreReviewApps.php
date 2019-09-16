<?php
namespace App\Models;

use App\Services\HttpService;

class AppleStoreReviewApps extends BaseModel
{
    protected $table = DbTable::APPLE_STORE_REVIEW_APPS;

    protected $tableColumns = [
        'app' => 'app',
        'apple_id' => 'apple_id',
        'full_name' => 'full_name',
    ];

    public static function getAppList($app = 'all'){
        $query = self::onWriteConnection()->select('app','apple_id','full_name')->where('status',0);
        if('all' != $app){
            $query->where('app',$app);
        }
        return $query->get()->toArray();
    }

    public static function getPageQuery($filter = array())
    {
        $perPage = DbTable::PAGINATE;

        $query = self::onWriteConnection()->select('id','app', \DB::raw('apple_id as app_store_id'),'full_name','status','created_at');

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
        $url = 'https://itunes.apple.com/lookup?id='.$id;
        $resJson = HttpService::curl_($url,[],'get');
        $appInfoArr = json_decode($resJson,true);
        $appInfo = [];
        if(isset($appInfoArr['results'][0]['artworkUrl512'])){
            $appInfo['app_img_src'] = $appInfoArr['results'][0]['artworkUrl512'];
            $appInfo['full_name'] = $appInfoArr['results'][0]['trackCensoredName'];
            $appInfo['artistId'] = $appInfoArr['results'][0]['artistId'];
            $appInfo['artistName'] = $appInfoArr['results'][0]['artistName'];
            $appInfo['description'] = $appInfoArr['results'][0]['description'];
            $appInfo['userRatingCount'] = $appInfoArr['results'][0]['userRatingCount'];
        }
        return $appInfo;
    }


    public static function getAppListForSelect( ){
        $appList = self::getAppList();
        $data = [];
        foreach ($appList as $k => $v) {
            $temp['value'] = $v['app']." ".AppStore::APPLE_STORE;
            $temp['id'] = AppStore::APPLE_STORE.":_:".$v['apple_id'];
            $data[] = $temp;
        }
        return json_encode($data);
    }

    public static function getAppInfo($appStoreId){
        $model = self::onWriteConnection()->select('app','img','full_name')->where('apple_id',$appStoreId)->first();
        if(empty($model)){
            return [];
        }
        return $model->toArray();
    }

}