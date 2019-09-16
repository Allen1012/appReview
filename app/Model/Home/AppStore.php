<?php

namespace App\Models;

use DB;

class AppStore extends BaseModel
{

    const APPLE_STORE = 'Apple Store';
    const GOOGLE_PLAY = 'Google Play';
    const FULL_NAME = 'Full Name';
    const PACKAGE = 'Package';
    const AUTHOR = 'Author';
    const TITLE = 'Title';
    const REVIEW = 'Review';
    const RATING = 'Rating';
    const VERSION = 'Version';
    const LANGUAGE = 'Language';
    const COUNT = 'Count';
    const APP_STORE_ID = 'App Store Id';
    const APP_STORE = 'App Store';


    protected $tableColumns = []; //定义改表的字段

    public static function getAppStoreList(){
        return [
            'apple_store' => self::APPLE_STORE,
            'google_play' => self::GOOGLE_PLAY,
        ];
    }

}






















