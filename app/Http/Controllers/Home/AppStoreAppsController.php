<?php

namespace App\Http\Controllers\Home;

use App\Models\AdwordsApp;
use App\Models\AppleStoreReviewApps;
use App\Models\AppStore;
use App\Models\BaseModel;
use App\Models\GooglePlayReviewApps;
use App\Models\MyLog;
use Illuminate\Http\Request;

use Validator;
use Input;
use Storage;

class AppStoreAppsController extends HomeController
{
    protected $modelName = 'AppStore应用';
    protected $uri = 'appStoreApps';

    public function index(Request $request){

        $this->filter['app_store'] = Input::get('app_store',AppStore::APPLE_STORE);
        $this->filter['status'] = Input::get('status','all');

        $appList = $this->getAppStoreAppList($this->filter['app_store']);
        $tableTitleList = $this->getTableTitleList($this->filter['app_store']);
        $appStoreList = AppStore::getAppStoreList();

//        dd($appList);
        return $this->render(
            'admin.' . $this->uri . '_index',
            [
                'pageInfo'         => $this->pageInfo,
                'filter'         => $this->filter,
                'list'         => $appList,
                'tableTitles'         => $tableTitleList,
                'appStoreList'         => $appStoreList,
            ]
        );
    }

    public function getAppInfoFromStore($id,$appStore){
        if(AppStore::GOOGLE_PLAY == $appStore){
            return GooglePlayReviewApps::getAppInfoFromStore($id);
        }elseif(AppStore::APPLE_STORE == $appStore){
            return AppleStoreReviewApps::getAppInfoFromStore($id);
        }else{
            return [];
        }
    }

    public function getAppInfo($id,$appStore){
        if(AppStore::GOOGLE_PLAY == $appStore){
            return GooglePlayReviewApps::getAppInfo($id);
        }elseif(AppStore::APPLE_STORE == $appStore){
            return AppleStoreReviewApps::getAppInfo($id);
        }else{
            return [];
        }
    }

    public function create(Request $request){
        $this->pageInfo['title'] = "添加".$this->modelName;
        $this->pageInfo['post_link'] = url('admin/'.$this->uri);

        $this->filter['app_store'] = Input::get('app_store','');
        $this->filter['app_store_link_or_id'] = Input::get('app_store_link_or_id','');

        if(!empty($this->filter['app_store_link_or_id'])){
            if(0 === stripos($this->filter['app_store_link_or_id'],'http')){
                if(AppStore::APPLE_STORE == $this->filter['app_store']){
                    $idIndex = stripos($this->filter['app_store_link_or_id'],'/id') + 3;
                    $appleIdStr = substr($this->filter['app_store_link_or_id'],$idIndex);
                    $this->filter['app_store_id'] = intval($appleIdStr);
                }elseif(AppStore::GOOGLE_PLAY == $this->filter['app_store']){
                    $idIndex = stripos($this->filter['app_store_link_or_id'],'id=') + 3;
//                dd($idIndex);
                    $endParamIndex = stripos($this->filter['app_store_link_or_id'],'&',$idIndex);
                    if($endParamIndex > $idIndex){
                        $this->filter['app_store_id'] = substr($this->filter['app_store_link_or_id'],$idIndex,$endParamIndex - $idIndex);
                    }else{
                        $this->filter['app_store_id'] = substr($this->filter['app_store_link_or_id'],$idIndex);
                    }
                }
            }else{
                if(AppStore::APPLE_STORE == $this->filter['app_store']){
                    $this->filter['app_store_id'] = intval($this->filter['app_store_link_or_id']);
                }else{
                    $this->filter['app_store_id'] = trim($this->filter['app_store_link_or_id']);
                }

            }
            $appInfo = $this->getAppInfoFromStore($this->filter['app_store_id'],$this->filter['app_store']);

            $this->filter = array_merge($appInfo,$this->filter);
        }

        $this->filter['app'] = Input::get('app','');
        $this->filter['status'] = Input::get('status','0');

        return $this->render(
            'admin.' . $this->uri .'_'.__FUNCTION__,
            [
                'pageInfo'         => $this->pageInfo,
                'filter'         => $this->filter,
                'appStoreList'         => AppStore::getAppStoreList(),
            ]
        );
    }

    public function store(){
        $appStore = Input::get('app_store','');
        $appStoreId = Input::get('app_store_id','');
        if(AppStore::APPLE_STORE == $appStore){
            $model = AppleStoreReviewApps::onWriteConnection()->where('apple_id',$appStoreId)->first();
            if(empty($model)){
                $model = new AppleStoreReviewApps();
                $model->apple_id = $appStoreId;
            }
        }elseif(AppStore::GOOGLE_PLAY == $appStore){
            $model = GooglePlayReviewApps::onWriteConnection()->where('package',$appStoreId)->first();
            if(empty($model)){
                $model = new GooglePlayReviewApps();
                $model->package = $appStoreId;
            }
        }else{
            return redirect('admin/'.$this->uri.'/create')->withErrors("请选择正确的商店!")->withInput();
        }
        $model->app = Input::get('app','');
        if(empty($model->app)){
            return redirect('admin/'.$this->uri.'/create')->withErrors("请填写app!")->withInput();
        }
        $model->status = Input::get('status',0);
        $model->full_name = Input::get('full_name','');
        $model->img = Input::get('app_img','');
        $this->filter['status'] = Input::get('status','0');
        $model->save();
        return redirect('admin/'.$this->uri.'?app_store='.$appStore)->with('status', '添加成功!');
    }

    public function edit($id){
        $this->pageInfo['title'] = "编辑".$this->modelName;
        $this->pageInfo['post_link'] = url('admin/'.$this->uri.'/'.$id);
        $appStore = Input::get('app_store',AppStore::APPLE_STORE);
        if(AppStore::APPLE_STORE == $appStore){
            $appInfo = AppleStoreReviewApps::onWriteConnection()->find($id)->toArray();
            $this->filter['app_store_id'] = $appInfo['apple_id'];
        }elseif(AppStore::GOOGLE_PLAY == $appStore){
            $appInfo = GooglePlayReviewApps::onWriteConnection()->find($id)->toArray();
            $this->filter['app_store_id'] = $appInfo['package'];
        }
        $this->filter = array_merge($this->filter,$appInfo);
        $this->filter['app_store'] = $appStore;
        $this->filter['app_img_src'] = $appInfo['img'];

        return $this->render(
            'admin.' . $this->uri .'_create',
            [
                'pageInfo'         => $this->pageInfo,
                'filter'         => $this->filter,
                'appStoreList'         => AppStore::getAppStoreList(),
                'appInfo' => $appInfo,
            ]
        );
    }

    public function update($id){


        $appStore = Input::get('app_store','');
        $appStoreId = Input::get('app_store_id','');
        if(AppStore::APPLE_STORE == $appStore){
            $model = AppleStoreReviewApps::onWriteConnection()->find($id);
            $model->apple_id = $appStoreId;
        }elseif(AppStore::GOOGLE_PLAY == $appStore){
            $model = GooglePlayReviewApps::onWriteConnection()->find($id);
            $model->package = $appStoreId;
        }else{
            return back()->withErrors("请选择正确的商店!")->withInput();
        }
        $model->app = Input::get('app','');
        if(empty($model->app)){
            return back()->withErrors("请填写app!")->withInput();
        }
        $model->status = Input::get('status',0);
        $model->full_name = Input::get('full_name','');
        $model->img = Input::get('app_img','');
        $this->filter['status'] = Input::get('status','0');
        $model->save();
        return redirect('admin/'.$this->uri.'?app_store='.$appStore)->with('status', '修改成功!');
    }

    public function getAppStoreAppList($appStore){
        if(AppStore::APPLE_STORE == $appStore){
            return AppleStoreReviewApps::getPageQuery($this->filter);
        }elseif(AppStore::GOOGLE_PLAY == $appStore){
            return GooglePlayReviewApps::getPageQuery($this->filter);
        }else{
            return [];
        }
    }

    /**
     * @param $appStore
     * @return array
     */
    public function getTableTitleList($appStore){
        $baseTableTitle = [
            BaseModel::ID => 'id',
            BaseModel::APP => '应用名',
            AppStore::FULL_NAME => '应用全名',
            BaseModel::STATUS => '应用状态',
            BaseModel::CREATED_AT => '创建时间',
        ];
        if(AppStore::APPLE_STORE == $appStore){
            return addFieldToArr($baseTableTitle,BaseModel::APPLE_ID,'apple id 唯一',BaseModel::APP);
        }elseif(AppStore::GOOGLE_PLAY == $appStore){
            return addFieldToArr($baseTableTitle,AppStore::PACKAGE,'应用包名',BaseModel::APP);
        }
        return [];
    }

}



















