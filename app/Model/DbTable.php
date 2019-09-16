<?php

namespace App\Models;


class DbTable
{
    const ADMIN_USER                                = 'admin_user_fly';
    const FB_VIDEO_MATERIAL                         = 'fb_video_material';
    const FB_VIDEO_CONTRAST                         = 'fb_video_contrast';
    const FB_AD_ACCOUNT                             = 'fb_ad_accounts';
    const FB_CREATE_AD_LIST                         = 'fb_create_ad_list';
    const FB_JOB                                    = 'fb_job';
    const FB_COPY_LIST                              = 'fb_copy_list';
    const ASO_ADVERTISER                            = 'aso_advertiser';
    const ASO_DAILY_SPENT                           = 'aso_daily_spent';
    const GOOGLE_PLAY_DAILY_COUNTRY_REVIEW          = 'google_play_daily_country_review';
    const GOOGLE_PLAY_REVIEW_APPS                   = 'google_play_review_apps';
    const APPLE_STORE_REVIEW_APPS                   = 'apple_store_review_apps';
    const APPLE_STORE_DAILY_COUNTRY_REVIEW          = 'apple_store_daily_country_review';
    const APPLE_STORE_REVIEW_COUNTRIES              = 'apple_store_review_countries';

    const PAGINATE                  = 30;

    const QUEUE_SYNCHRO_FBAD        = 'synchroFbAd';
    const QUEUE_COPY_FBAD           = 'copyFbAd';

}
