<?php
namespace Sooh\LogClasses\Driver;
class PHPDefault extends \Sooh\LogClasses\DriverBase{
    protected $_tplRow;
    public function __construct($tpl='[{logtype}]{message} ## {result} {extmsg} {custom} ## {remoteIp} User:{userid} SESS:{sessionid} Uri:{requestUri} P_SN:{requestSN} ## {codeInfo} {detail}') {
        $this->_tplRow = $tpl;
    }
    /**
     * 
     * @param \Sooh\LogClasses\LogParts $parts
     */
    public function write($parts)
    {
        $row = $parts->strReplace($this->_tplRow);
        error_log($row);
    }
    /**
     * 
     * @param \Sooh\LogClasses\SplitBy $SplitBy
     */
    public function onNewRequest($SplitBy){}
}