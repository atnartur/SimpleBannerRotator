<?php

namespace App\Controllers\Admin;

use Phalcon\Mvc\View;

class ControllerBase extends \App\Controllers\ControllerBase
{

    protected function initialize()
    {
        parent::initialize();
    }

    public function beforeExecuteRoute($dispatcher)
    {
        //var_dump('das');exit;
        $this->view->setViewsDir(APPLICATION_PATH.$this->di->getConfig()->app->viewsDir.'admin/');
        $this->view->setPartialsDir('partials/');
        $this->view->setLayoutsDir('../layouts/');
        $this->view->setMainView('../index');
    }
}