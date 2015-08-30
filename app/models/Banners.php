<?php

namespace App\Models;

use App\Library\Functions;
use Phalcon\Db\RawValue;

class Banners extends ModelBase
{
    public $id, $name, $width, $height, $link, $target_blank, $priority, $type, $content, $max_impressions, $start_date, $end_date, $url_mask, $advertiser_id, $active, $archived;
    public $labels = array(
        'name' => 'Имя',
        'width' => 'Ширина',
        'height' => 'Высота',
        'link' => 'Ссылка',
        'target_blank' => 'Открывать в новой вкладке',
        'priority' => 'Приоритет',
        'type' => 'Тип баннера',
        'content' => 'Содержимое баннера',
        'max_impressions' => 'Предел по показам',
        'start_date' => 'Дата начала показа',
        'end_date' => 'Дата конца показа',
        'url_mask' => 'URL-маска',
        'advertiser_id' => 'Рекламодатель',
        'active' => 'Статус',
        'archived' => 'Архивный'
    );

    public function initialize()
    {
        parent::initialize();
        $this->belongsTo('advertiser_id', 'App\Models\Users', 'id',
            array('alias' => 'advertiser')
        );
        $this->hasManyToMany(
            "id",
            'App\Models\BannersZones',
            "banner_id",
            "zone_id",
            'App\Models\Zones',
            "id",
            array('alias' => 'zones')
        );
        $this->hasMany('id', 'App\Models\Views', 'banner_id', array(
            'alias' => 'views',
            'foreignKey' => array(
                'action' => \Phalcon\Mvc\Model\Relation::ACTION_CASCADE
            )
        ));
    }

    public function beforeValidation()
    {
        $this->max_impressions = abs($this->max_impressions);

        if(empty($this->max_impressions) || !$this->max_impressions) {
            $this->max_impressions = new RawValue('default');
        }
        if (!isset($this->target_blank) || $this->target_blank === "") {
            $this->target_blank = new RawValue('default');
        }
        if (empty($this->priority) || !$this->priority) {
            $this->priority = new RawValue('default');
        }
        if (!isset($this->active) || $this->active === "") {
            $this->active = new RawValue('default');
        }
        if (!isset($this->archived) || $this->archived === "") {
            $this->archived = new RawValue('default');
        }
        if (!isset($this->advertiser_id) || $this->advertiser_id === "") {
            $this->advertiser_id = new RawValue('default');
        }
        if (!isset($this->width) || $this->width === "") {
            $this->width = new RawValue('default');
        }
        if (!isset($this->height) || $this->height === "") {
            $this->height = new RawValue('default');
        }
        if (!isset($this->start_date) || $this->start_date === "") {
            $this->start_date = new RawValue('default');
        }
        if (!isset($this->end_date) || $this->end_date === "") {
            $this->end_date = new RawValue('default');
        }
    }

    public function getSize() {
        $segment1 = '';
        $segment2 = '';
        $segment3 = '';
        if(!empty($this->width) || !empty($this->height)) {
            $segment2 = 'x';
        } else {
            return 'Не задан';
        }
        if(empty($this->width)) {
            $segment1 = '?';
        } else {
            $segment1 = $this->width;
        }

        if(empty($this->height)) {
            $segment3 = '?';
        } else {
            $segment3 = $this->height;
        }
        return $segment1.$segment2.$segment3;
    }

    public function getType() {
        if($this->type == "image") {
            return "Изображение";
        } elseif($this->type == "flash") {
            return "Flash";
        } elseif($this->type == "html") {
            return "HTML-код";
        } else {
            return false;
        }
    }

    public function getStartDate() {
        return Functions::formatted_unixtime($this->start_date);
    }

    public function getEndDate() {
        return Functions::formatted_unixtime($this->end_date);
    }

    public function toggle() {
        if($this->active == 1)
            $result = $this->update(array('active'=>0));
        else
            $result = $this->update(array('active'=>1));
        return $result;
    }

    public function toggleArchived() {
        if($this->archived == 1)
            $result = $this->update(array('archived'=>0));
        else
            $result = $this->update(array('archived'=>1));
        return $result;
    }

    public static function findByOptions($options, $cat_id = NULL, $order = 'id', $sort = 'ASC') {
        $sql = "SELECT * FROM `banners`
        INNER JOIN `banners_parents` ON `banners_parents`.`banner_id` = `banners`.`id`
        WHERE `banners_parents`.`parent_id` = {$cat_id} ";
        if (count($options)) {
            foreach($options as $option) {
                $optionModel = Options::findFirst($option['id']);
                if($optionModel) {
                    if($optionModel->banner_value_type == 'single_select') {
                        if($optionModel->filter_type == 'multiple_select' && (!isset($option['values']) || !count($option['values']))) continue;
                        if($optionModel->filter_type == 'single_select' && empty($option['value'])) continue;
                        $values = $optionModel->filter_type == 'single_select' ? '= '.$option['value'] : 'IN ('.implode(',',$option['values']).')';
                        $sql.= " AND `banners`.`id` IN (
                            SELECT `banners`.`id`
                            FROM `banners`
                            JOIN `banners_options` ON `banners_options`.`banner_id` = `banners`.`id`
                            LEFT JOIN `banners_select_options_values` as `o{$option['id']}` ON `o{$option['id']}`.`banner_option_id` = `banners_options`.`id`
                            WHERE `o{$option['id']}`.`select_option_value_id` {$values}
                        )";
                    }
                    elseif($optionModel->banner_value_type == 'multiple_select') {
                        if(isset($option['values']) && count($option['values'])) {
                            $count_values = count($option['values']);
                            $values_row = implode(',',$option['values']);
                            $sql.= " AND `banners`.`id` IN (
                                SELECT `banners`.`id`
                                FROM `banners`
                                JOIN `banners_options` ON `banners_options`.`banner_id` = `banners`.`id`
                                JOIN  (SELECT `banner_option_id` FROM `banners_select_options_values` WHERE `select_option_value_id` IN ({$values_row}) GROUP BY `banner_option_id` HAVING COUNT(`banner_option_id`) = {$count_values}) as `o{$option['id']}` ON `o{$option['id']}`.`banner_option_id` = `banners_options`.`id`
                            )";
                        }
                    }
                    elseif($optionModel->banner_value_type == 'range') {
                        // убираем все левые символы из строки и заменяем запятые на точки
                        $from_value = filter_var(str_replace(',', '.', $option['from_value']), FILTER_SANITIZE_NUMBER_FLOAT, array('flags'=>FILTER_FLAG_ALLOW_FRACTION));
                        $to_value = filter_var(str_replace(',', '.', $option['to_value']), FILTER_SANITIZE_NUMBER_FLOAT, array('flags'=>FILTER_FLAG_ALLOW_FRACTION));

                        // Если хотя бы одно из значений содержит в себе хоть одну цифру (да, именно цифру... а вдруг там одна только точка)
                        if(!empty(preg_match("/\d/",$from_value)) || !empty(preg_match("/\d/",$to_value))) {
                            if(!empty(preg_match("/\d/",$from_value)) && !empty(preg_match("/\d/",$to_value)))
                                $range_query = "(`o{$option['id']}`.`from_value`>={$from_value} AND `o{$option['id']}`.`from_value` <= {$to_value}) OR (`o{$option['id']}`.`to_value`<={$to_value} AND `o{$option['id']}`.`to_value`>={$from_value})";
                            // Если пустое стартовое значение
                            elseif(empty(preg_match("/\d/",$from_value)) && !empty(preg_match("/\d/",$to_value)))
                                $range_query = "`o{$option['id']}`.`from_value`<={$to_value}";
                            // Если пустое конечное значение
                            elseif(!empty(preg_match("/\d/",$from_value)) && empty(preg_match("/\d/",$to_value)))
                                $range_query = "`o{$option['id']}`.`to_value`>={$from_value}";
                            else continue;
                            $sql.= " AND `banners`.`id` IN (
                                SELECT `banners`.`id`
                                FROM `banners`
                                JOIN `banners_options` ON `banners_options`.`banner_id` = `banners`.`id`
                                LEFT JOIN `banners_range_options_values` as `o{$option['id']}` ON `o{$option['id']}`.`banner_option_id` = `banners_options`.`id`
                                WHERE ({$range_query})
                            )";
                        }
                    }
                    elseif($optionModel->banner_value_type == 'specific') {
                        // убираем все левые символы из строки и заменяем запятые на точки
                        $from_value = filter_var(str_replace(',', '.', $option['from_value']), FILTER_SANITIZE_NUMBER_FLOAT, array('flags'=>FILTER_FLAG_ALLOW_FRACTION));
                        $to_value = filter_var(str_replace(',', '.', $option['to_value']), FILTER_SANITIZE_NUMBER_FLOAT, array('flags'=>FILTER_FLAG_ALLOW_FRACTION));

                        // Если хотя бы одно из значений содержит в себе хоть одну цифру (да, именно цифру... а вдруг там одна только точка)
                        if(!empty(preg_match("/\d/",$from_value)) || !empty(preg_match("/\d/",$to_value))) {
                            if(!empty(preg_match("/\d/",$from_value)) && !empty(preg_match("/\d/",$to_value)))
                                $range_query = "`o{$option['id']}`.`value` >= {$from_value} AND `o{$option['id']}`.`value` <= {$to_value}";
                            // Если пустое стартовое значение
                            elseif(empty(preg_match("/\d/",$from_value)) && !empty(preg_match("/\d/",$to_value)))
                                $range_query = "`o{$option['id']}`.`value`<={$to_value}";
                            // Если пустое конечное значение
                            elseif(!empty(preg_match("/\d/",$from_value)) && empty(preg_match("/\d/",$to_value)))
                                $range_query = "`o{$option['id']}`.`value`>={$from_value}";
                            else continue;
                            $sql.= " AND `banners`.`id` IN (
                                SELECT `banners`.`id`
                                FROM `banners`
                                JOIN `banners_options` ON `banners_options`.`banner_id` = `banners`.`id`
                                LEFT JOIN `banners_specific_options_values` as `o{$option['id']}` ON `o{$option['id']}`.`banner_option_id` = `banners_options`.`id`
                                WHERE ({$range_query})
                            )";
                        }
                    }
                }
            }
        }
        $sql.= " ORDER BY {$order} {$sort};";
        if($cat_id != NULL)
            $banner = new Banners();
        return new Resultset(null, $banner, $banner->getReadConnection()->query($sql));
    }
}
