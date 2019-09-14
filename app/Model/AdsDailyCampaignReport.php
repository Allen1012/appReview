<?php

namespace App\Model;


class AdsDailyCampaignReport extends BaseModel{

    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'ads_daily_campaign_report';

    /**
     * 用来向表中插入数据的字段
     *
     * @var array
     */
    protected $tableColumns = [
        'apple_id',
        'date',
        'campaign_id',
        'campaign',
        'installs',
        'spent',
        'updated_at'
    ];

}
