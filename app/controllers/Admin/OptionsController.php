<?php
namespace App\Controllers\Admin;

use App\Models\Options;

class OptionsController extends ControllerBase
{
    public function initialize(){
        parent::initialize();
    }
    public function indexAction() {
        \Phalcon\Tag::prependTitle('Список параметров таргетинга');
        $this->view->title = "Список параметров таргетинга";
        $this->view->options = Options::find();
    }

    public function addAction() {
        if ($this->request->isPost())
        {
            $option = new Options();
            if($option->addOption($this->request->getPost('name'), $this->request->getPost('filter_type'), $this->request->getPost('banner_value_type'), $this->request->getPost('select_values')))
            {
                $this->flashSession->success("Параметр таргетинга успешно добавлен");
                return $this->response->redirect("options");
            }
            else {
                foreach($option->getMessages() as $message) {
                    $this->flashSession->error($message->getMessage());
                }
            }
        }

        $this->view->title="Добавление параметра таргетинга";
        \Phalcon\Tag::prependTitle("Добавление параметра таргетинга");

        $this->assets->collection('bottom-js')
            ->addJs('https://ajax.googleapis.com/ajax/libs/angularjs/1.2.21/angular.min.js', false)
            ->addJs('js/bootbox.min.js')
            ->addJs('js/angular/app.js')
            ->addJs('js/angular/AdminOption.ctrl.js');
    }

    public function deleteAction() {
        $id = $this->dispatcher->getParam('id');
        $option = Options::findFirst($id);
        if($option) {
            if($this->request->getQuery('confirm')==1) {
                $option->delete();
                $this->flashSession->success("Параметр таргетинга успешно удалён");
                return $this->response->redirect("admin/options");
            }
            $this->view->option = $option;
            $this->view->title="Удаление \"{$option->name}\"";
            \Phalcon\Tag::prependTitle("Удаление \"{$option->name}\"");
        } else $this->dispatcher->forward(array("controller" => "error", "action" => "notFound"));
    }

    public function editAction() {
        $this->assets->collection('js')
            ->addJs('https://ajax.googleapis.com/ajax/libs/angularjs/1.2.21/angular.min.js', false)
            ->addJs('js/angular/app.js')
            ->addJs('js/angular/AdminOption.ctrl.js')
            ->addJs('js/bootbox.min.js');
        $id = $this->dispatcher->getParam('id');
        $option = Options::findFirst($id);
        if ($option && $id)
        {
            if ($this->request->isPost())
            {
                if($option->updateOption($this->request->getPost('name'), $this->request->getPost('select_values')))
                {
                    $this->flashSession->success("Параметр таргетинга успешно изменён");
                }
                else {
                    foreach($option->getMessages() as $message) {
                        $this->flashSession->error($message->getMessage());
                    }
                }
            }
            $this->view->option=$option;
            $this->view->title="Редактирование параметра";
            \Phalcon\Tag::prependTitle("Редактирование параметра");
        }
        else $this->dispatcher->forward(array("controller" => "error", "action" => "notFound"));
    }
}