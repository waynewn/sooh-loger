<?php
namespace Sooh\LogClasses\Driver;

class Files extends \Sooh\LogClasses\DriverBase{
    protected $_tplFilename;
    protected $_tplRow;
    protected $_filenameByType=array();
    public function __construct($nameDefine,$rowDefine) {
        $this->_tplFilename = $nameDefine;
        $this->_tplRow = $rowDefine;
    }
    /**
     * 
     * @param \Sooh\LogClasses\LogParts $parts
     */
    public function write($parts)
    {
        $filename = $this->_filenameByType[$parts->logtype];
        $row = $parts->strReplace($this->_tplRow);
        if( file_put_contents($filename, $row, FILE_APPEND)===false){
            $dirname = dirname($filename);
            mkdir($dirname,0666,true);
            file_put_contents($filename, $row, FILE_APPEND);
        }
    }
    /**
     * 
     * @param \Sooh\LogClasses\SplitBy $SplitBy
     */
    public function onNewRequest($SplitBy)
    {
        if(empty($this->fileTpl)){
            return;
        }
        $this->_filenameByType=array();
        $this->_filenameByType['common'] = $SplitBy->replace('common',$this->fileTpl['dir'].'/'.$this->fileTpl['file']);
        $this->_filenameByType['error'] = $SplitBy->replace('error',$this->fileTpl['dir'].'/'.$this->fileTpl['file']);
        $this->_filenameByType['trace'] = $SplitBy->replace('trace',$this->fileTpl['dir'].'/'.$this->fileTpl['file']);;
    }
}

