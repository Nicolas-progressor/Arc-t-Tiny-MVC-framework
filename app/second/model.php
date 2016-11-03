<?php

class model_Second{
    
    public function getThird($id) {
        $resp = $this->sql->get("test", "text", ['id' => $id]);
        return $resp;
    }
    
}