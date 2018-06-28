<?php
namespace Sooh\LogClasses;

class SplitBy {
    protected $_tmp;
    public function __construct($timestamp) {
        $this->_tmp = explode(' ',date('Y m d H i s',$timestamp));
    }
    public function replace($logType,$tpl)
    {
        $this->_tmp[6] = $logType;
        return str_replace(
               array('{year}','{month}','{day}','{hour}','{minute}','{second}','{type}'), 
               $this->_tmp, 
               $tpl);
    }
}
