<?php
namespace App\Models;

use Phalcon\Mvc\Model\Validator\PresenceOf;

class ItemsSelectOptionsValues extends ModelBase
{
    public $labels = array(
        'select_option_value_id' => 'Значение параметра выбора',
    );

    public function initialize()
    {
        parent::initialize();
        $this->belongsTo('select_option_value_id', 'App\Models\SelectOptionsValues', 'id',
            array('alias' => 'selectOptionValue')
        );
    }


    public function validation()
    {
        $this->validate(new PresenceOf(array(
            "field" => "select_option_value_id",
            'message' => 'Поле "'.$this->getLabel('select_option_value_id').'" должно быть заполнено',
        )));

        return $this->validationHasFailed() != true;
    }
}