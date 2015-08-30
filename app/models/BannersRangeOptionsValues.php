<?php
namespace App\Models;

use Phalcon\Mvc\Model\Validator\PresenceOf;

class BannersRangeOptionsValues extends ModelBase{
    public $labels = array(
        'from_value' => 'От',
        'to_value' => 'До',
    );

    public function validation()
    {
        $this->validate(new PresenceOf(array(
            "field" => "from_value",
            'message' => 'Поле "'.$this->getLabel('from_value').'" должно быть заполнено',
        )));

        $this->validate(new PresenceOf(array(
            "field" => "to_value",
            'message' => 'Поле "'.$this->getLabel('to_value').'" должно быть заполнено',
        )));

        return $this->validationHasFailed() != true;
    }
}