<?php

class controller_welcome{
    
    public function index(){
        $text = $this->model->getContent();
        
        $this->view->render(array('text' => $text));
    }    
    
    public function second(){
        $text = $this->model->getSecond();
        
        $this->view->renderTo('second', array('text' => $text));
    }
    
}