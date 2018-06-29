<?php
namespace Sooh\LogClasses;

abstract class DriverBase {

    abstract public function onNewRequest($SplitBy);

    abstract public function write($parts);
    
}

