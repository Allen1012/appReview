<?php
namespace App\Models;

class AppleStoreReviewCountries extends BaseModel
{
    protected $table = DbTable::APPLE_STORE_REVIEW_COUNTRIES;

    protected $tableColumns = [
        'code_2' => 'code_2',
        'country' => 'country',
    ];

    public static function getCountryList(){
        $query = self::onWriteConnection()->select('country','code_2');
        $list = $query->get()->toArray();
        $countryList = [];
        foreach ($list as $k => $v){
            $countryList[$v['code_2']] = $v['country'];
        }
        return $countryList;
    }

}