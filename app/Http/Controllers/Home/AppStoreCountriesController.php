<?php

namespace App\Http\Controllers\Home;

use App\Models\AppleStoreReviewCountries;
use App\Models\ReportCountry;
use Illuminate\Http\Request;

use Validator;
use Input;
use Storage;

class AppStoreCountriesController extends HomeController
{
    protected $modelName = 'AppStore国家';
    protected $uri = 'appStoreCountries';

    public function index(Request $request){

        $countryList = ReportCountry::getShowCountryList_justCountry();
        unset($countryList['all']);
        $appStoreCountryList = AppleStoreReviewCountries::getCountryList();

        return $this->render(
            'admin.' . $this->uri . '_index',
            [
                'pageInfo'         => $this->pageInfo,
                'filter'         => $this->filter,
                'countryList'         => $countryList,
                'appStoreCountryList'         => $appStoreCountryList,
            ]
        );
    }

    public function store(){
        $countryList = ReportCountry::getShowCountryList_justCountry();
        unset($countryList['all']);
        $appStoreCountryList = AppleStoreReviewCountries::getCountryList();
        $addToDbCountryList = [];
        $delCountryList = [];
        $appStoreCountries = Input::get('countries',[]);
        foreach ($appStoreCountries as $k => $code2 ){
            if(isset($appStoreCountryList[$code2])){
                unset($appStoreCountryList[$code2]);
            }else{
                $addToDbCountryList[] = [
                    'code_2' => $code2,
                    'country' => $countryList[$code2],
                ];
            }
        }
        if(!empty($appStoreCountryList)){
            foreach ($appStoreCountryList as $code2 => $country){
                $delCountryList[] = $code2;
            }
        }
        $appStoreCountry = new AppleStoreReviewCountries();
        $ret = $appStoreCountry->batchInsertOrUpdate($addToDbCountryList);
        $appStoreCountry::onWriteConnection()->whereIn('code_2',$delCountryList)->delete();

        return redirect('admin/'.$this->uri)->with('status', '修改成功!');
    }


}



















