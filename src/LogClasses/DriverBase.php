<?php
namespace Sooh\LogClasses;

abstract class DriverBase {

    public function onNewRequest($SplitBy);

    public function write($parts);
    
    protected function replaceTpl()
    {
        
    }
}

