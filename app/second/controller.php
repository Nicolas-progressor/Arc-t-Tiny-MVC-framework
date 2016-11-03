<?php

class controller_Second{
    
    public function index(){
        $text = $this->model->getThird(1);
        
        $this->view->render(array('text' => $text));
    }    
        
}