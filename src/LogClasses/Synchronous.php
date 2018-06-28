<?php
namespace Sooh\LogClasses;

class Synchronous extends DriverBase{
    protected $_arr;
    public function __construct($arrDrivers) {
        if(!is_array($arrDrivers)){
            throw new \ErrorException('array of drivers needs');
        }
        $this->_arr = $arrDrivers;
    }
    public function onNewRequest($SplitBy){
        foreach($this->_arr as $o){
            $o->onNewRequest($SplitBy);
        }
    }

    public function write($parts)
    {
        foreach($this->_arr as $o){
            $o->write($parts);
        }
    }
}