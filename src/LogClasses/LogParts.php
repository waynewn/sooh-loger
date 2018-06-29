<?php
namespace Sooh\LogClasses;

class LogParts {
    // TIME  ## [CUSTOM] MESSAGE ## DEBUG_TRACE_INFO ## REQUEST_INFO  ## MORE_INFO 
    public $logtime; 
    public $message;
    public $extmsg;
    public $result;
    public $custom=array();
    public $remoteIp;
    public $remoteDeviceId;
    public $requestUri;//request-uri
    public $logtype;  //error,common,trace
    public $loglevel; //app,sys,lib
    public $sessionid;
    public $userid;
    public $codeInfo;// request-sn & call postion
    public $requestSN;//对每个请求生成的序列号（有md5在里面，基本能保障唯一）

    public function reset()
    {
        $this->custom= array();
        $this->logtime=date('m-d H:i:s');
        $this->result = '';
        $this->message = '';
        $this->extmsg = '';
        $this->codeInfo = '';
        $this->remoteIp='';
        $this->remoteDeviceId='';
        $this->requestUri='';
        $this->logtype ='';
        $this->sessionid='';
        $this->userid='';
        $this->requestSN='';
    }
    
    public function prepare($type,$level,$msg,$detail){
        $this->logtype = $type;
        $this->loglevel = $level;
        $this->message=$msg;
        if(is_scalar($detail)){
            $this->extmsg = $detail;
        }else{
            $this->extmsg = json_encode($detail);
        }
        $this->codeInfo = $this->getCallPosition();
        return $this;
    }
    
    public function getMap()
    {
        return array(
            'logtime'=>$this->logtime,
            'message'=>$this->message,
            'extmsg'=>$this->extmsg,
            'custom'=> json_encode($this->custom),
            'result'=> $this->result,
            'remoteIp'=>$this->remoteIp,
            'remoteDeviceId'=> $this->remoteDeviceId,
            'requestUri'=>$this->requestUri,
            'logtype'=>$this->logtype,
            'sessionid'=>$this->sessionid,
            'userid'=>$this->userid,
            'codeInfo'=> $this->codeInfo,
            'requestSN'=> $this->requestSN,
        );
    }
    
    public function strReplace($str)
    {
        $find = array('{logtime}','{message}','{custom}','{result}','{remoteIp}',
            '{remoteDeviceId}','{requestUri}','{logtype}','{sessionid}',
            '{userid}','{codeInfo}','{requestSN}','{extmsg}');
        $replace = array($this->logtime, $this->message, json_encode($this->custom),$this->result,$this->remoteIp,
            $this->remoteDeviceId,$this->requestUri,$this->logtype,$this->sessionid,
            $this->userid, $this->codeInfo,$this->requestSN, $this->extmsg);
        return str_replace($find, $replace, $str);
    }
    /**
     * 获取调用位置信息
     */
    public function getCallPosition()
    {
        $trace_begin = 4;
        $arr = debug_backtrace(null , $trace_begin + 2);
        $args = $arr[$trace_begin + 1]['args'];
        $strArgs = '';
        if(!empty($args)){
            foreach($args as $i){
                if(is_scalar($i)){
                    $strArgs.=$i.',';
                }else{
                    $strArgs.= gettype($i).',';
                }
            }
            $strArgs=substr($strArgs,0,-1);
        }
        return  $arr[$trace_begin + 1]['class'].'->'.$arr[$trace_begin + 1]['function'].'['.$arr[$trace_begin]['line'].']('.$strArgs.')';
    }

}

