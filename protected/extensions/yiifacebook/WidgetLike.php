<?php

class WidgetLike extends CInputWidget{
    

    public $url = '';
    
    public function run(){
		
        //$this->render('like_iframe',array('url'=>$this->url));
        $this->render('like_js',array('url'=>$this->url));
    }
    
}
