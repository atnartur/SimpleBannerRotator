<?php
namespace App\Models;

use Phalcon\Mvc\Model\Message;

class Options extends ModelBase
{
    public $id, $name, $filter_type, $banner_value_type;

    public $labels = array(
        'name'            => 'Название',
        'filter_type'     => 'Тип отображаемый при фильтрации',
        'banner_value_type' => 'Тип отображаемый при добавление/редактировании товара',
    );

    public function initialize()
    {
        parent::initialize();
        $this->hasManyToMany(
            "id",
            'App\Models\BannersOptions',
            "option_id",
            "banner_id",
            'App\Models\Banners',
            "id",
            array('alias' => 'banners')
        );

        $this->hasMany('id', __NAMESPACE__ . '\BannersOptions', 'option_id', array(
            'alias' => 'bannersOptions',
            'foreignKey' => array(
                'action' => \Phalcon\Mvc\Model\Relation::ACTION_CASCADE
            )
        ));

        // this work if filter_type = multiple_select or single_select
        $this->hasMany('id', __NAMESPACE__ . '\SelectOptionsValues', 'option_id', array(
            'alias' => 'values',
            'foreignKey' => array(
                'action' => \Phalcon\Mvc\Model\Relation::ACTION_CASCADE
            )
        ));
    }

    public function getFilterTypeName() {
        switch($this->filter_type) {
            case 'range':
                return 'Диапазон';
            case 'multiple_select':
                return 'Множественный выбор';
            case 'single_select':
                return 'Единичный выбор';
        }
    }

    public function getBannerValueTypeName() {
        switch($this->banner_value_type) {
            case 'range':
                return 'Диапазон';
            case 'multiple_select':
                return 'Множественный выбор';
            case 'single_select':
                return 'Единичный выбор';
            case 'specific':
                return 'Точное значение';
        }
    }

    public function addOption($name, $filter_type, $banner_value_type, $select_values = NULL) {
        $this->getDI()->getDb()->begin();

        if($this->save(array(
                'name' => $name,
                'filter_type' => $filter_type,
                'banner_value_type' => $banner_value_type
            )) == false) {
            $this->getDI()->getDb()->rollback();
            return false;
        }

        if(($filter_type == 'multiple_select' || $filter_type == 'single_select') && !count($select_values)) {
            $this->getDI()->getDb()->rollback();
            $this->appendMessage(new Message('При использовании параметров множественного или единичного выбора необходимо задать набор допустимых значений'));
            return false;
        }
        elseif ($filter_type == 'multiple_select' || $filter_type == 'single_select') {
            foreach($select_values as $sv) {
                $m = new SelectOptionsValues();
                $m->value = $sv;
                $m->option_id = $this->id;
                if ($m->create() == false) {
                    $this->getDI()->getDb()->rollback();
                    return false;
                }
            }
        }

        $this->getDI()->getDb()->commit();
        return true;
    }

    public function updateOption($newName, $select_values = NULL) {
        $this->getDI()->getDb()->begin();

        if($this->name != $newName)
            if(!$this->update(array('name'=>$newName))) {
                $this->getDI()->getDb()->rollback();
                return false;
            }

        if(($this->filter_type == 'multiple_select' || $this->filter_type == 'single_select') && !count($select_values)) {
            $this->getDI()->getDb()->rollback();
            $this->appendMessage(new Message('При использовании параметров множественного или единичного выбора необходимо задать набор допустимых значений'));
            return false;
        }
        elseif ($this->filter_type == 'multiple_select' || $this->filter_type == 'single_select') {
            $valuesFromDatabase = $this->getValues();
            $idsValuesForDeleteFromDatabase = array_column($valuesFromDatabase->toArray(), 'value', 'id');

            foreach($select_values as $sv) {
                // Если это уже существуещее в базе значение
                if($sv['new'] == 0) {
                    // Если значение упоминается, то мы оставляем его, т.е. убираем из списка на удаление
                    unset($idsValuesForDeleteFromDatabase[$sv['id']]);
                    $svModel = SelectOptionsValues::findFirst($sv['id']);
                    if($svModel) {
                        // Если изменилось это значение, то применяем изменения
                        if($svModel->value != $sv['value']) {
                            if(!$svModel->update(array('value'=>$sv['value']))) {
                                foreach($svModel->getMessages() as $message) {
                                    $this->appendMessage($message);
                                }
                                $this->getDI()->getDb()->rollback();
                                return false;
                            }
                        }
                    }
                }
                else {
                    // Здесь заносим в базу новые значения
                    $m = new SelectOptionsValues();
                    $m->value = $sv['value'];
                    $m->option_id = $this->id;
                    if ($m->create() == false) {
                        foreach($m->getMessages() as $message) {
                            $this->appendMessage($message);
                        }
                        $this->getDI()->getDb()->rollback();
                        return false;
                    }
                }
            }

            // Удаляем все значения которых больше нет в списке "Набор доступных значений"
            foreach($idsValuesForDeleteFromDatabase as $id => $v) {
                $m = SelectOptionsValues::findFirst($id);
                if(!$m->delete()) {
                    foreach($m->getMessages() as $message) {
                        $this->appendMessage($message);
                    }
                    $this->getDI()->getDb()->rollback();
                    return false;
                }
            }
        }

        $this->getDI()->getDb()->commit();
        return true;
    }
}