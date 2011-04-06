<?php

class YiiFacebook extends CApplicationComponent
{

        public $dev_appid = '';
        public $dev_secret = '';
        public $cookie = true;

        protected $facebook;
        private static $registeredScripts = false;

        public function init() {
                $this->registerScripts();
                parent::init();
        }

        public function getFacebook() {
                if ($this->facebook===null){
                        $this->facebook = new Facebook(array('appId'=>$this->dev_appid,'secret'=>$this->dev_secret,'cookie'=>$this->cookie));
                        }
                return $this->facebook;
        }

    /**
    * Registers swiftMailer autoloader and includes the required files
    */
    public function registerScripts() {
        if (self::$registeredScripts) return;
        self::$registeredScripts = true;
                require dirname(__FILE__).'/facebook.php';
        }
}
