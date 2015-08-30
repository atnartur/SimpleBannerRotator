<?php

namespace App\Models;


class BannersOptions extends ModelBase
{
    public function initialize()
    {
        parent::initialize();
        $this->belongsTo('option_id', 'App\Models\Options', 'id',
            array('alias' => 'option')
        );
        $this->belongsTo('banner_id', 'App\Models\Banners', 'id',
            array('alias' => 'banner')
        );

        // Значение для диапазонного параметра
        $this->hasOne("id", 'App\Models\BannersRangeOptionsValues', "banner_option_id", array(
            'alias' => 'rangeOptionValue',
            "foreignKey" => array(
                'action' => \Phalcon\Mvc\Model\Relation::ACTION_CASCADE
            )
        ));

        // Значение для точного параметра
        $this->hasOne("id", 'App\Models\BannersSpecificOptionsValues', "banner_option_id", array(
            'alias' => 'specificOptionValue',
            "foreignKey" => array(
                'action' => \Phalcon\Mvc\Model\Relation::ACTION_CASCADE
            )
        ));

        // Значение для параметра единичного выбора
        $this->hasOne("id", 'App\Models\BannersSelectOptionsValues', "banner_option_id", array(
            'alias' => 'singleSelectOptionValue',
            "foreignKey" => array(
                'action' => \Phalcon\Mvc\Model\Relation::ACTION_CASCADE
            )
        ));

        // Значения для параметра множественного выбора
        $this->hasMany("id", 'App\Models\BannersSelectOptionsValues', "banner_option_id", array(
            'alias' => 'multipleSelectOptionValues',
            "foreignKey" => array(
                'action' => \Phalcon\Mvc\Model\Relation::ACTION_CASCADE
            )
        ));
    }
}