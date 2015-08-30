<?php
namespace App\Models;

use Phalcon\Mvc\Model\Validator\PresenceOf;

class ItemSpecificOptionsValues extends ModelBase
{
    public $labels = array(
        'value' => 'Значение параметра',
    );

    public function validation()
    {
        $this->validate(new PresenceOf(array(
            "field" => "value",
            'message' => 'Поле "'.$this->getLabel('value').'" должно быть заполнено',
        )));

        return $this->validationHasFailed() != true;
    }
}