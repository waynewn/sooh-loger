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
    
    /**
     * 格式化日志信息 ： 
     *      ## 日志信息 ## 其他信息 ## 用户、进程信息  ## 触发位置函数信息以及类数组信息
     * @param string $flg
     * @param type $msgOrObj
     * @param type $prefixIntro
     * @return string
     */
    protected function fmtTxtLog($flg,$msgOrObj, $prefixIntro)
    {

        $traceStr='';
        $traceObj='';
        if(is_null($msgOrObj)){
            if(is_null($prefixIntro)){
                $traceStr.='null';
            }
        }if(is_scalar($msgOrObj)){
            $traceStr.=$msgOrObj;
        }else{
            $traceObj = var_export($msgOrObj,true);
        }
        if(is_null($prefixIntro)){
            if(is_null($msgOrObj)){
                $traceStr.='null';
            }
        }if(is_scalar($prefixIntro)){
            $traceStr.=$prefixIntro;
        }else{
            $traceObj = var_export($prefixIntro,true);
        }
        if($flg!=='app_common'){
            $traceCallPos = $this->getCallPosition();
        }else{
            $traceCallPos='';
        }
        
        $tmp = $this->env;
        $traceUserProcessInfo ='';
        if(isset($tmp['user'])){
            $traceUserProcessInfo.='User:'.$tmp['user'].', ';
            unset($tmp['user']);
        }
        if(isset($tmp['sess'])){
            $traceUserProcessInfo.='SESS:'.$tmp['sess'].', ';
            unset($tmp['sess']);
        }
        if(isset($tmp['sooh2_the_req_uri'])){
            $traceUserProcessInfo.='Uri:'.$tmp['sooh2_the_req_uri'].', ';
            unset($tmp['sooh2_the_req_uri']);
        }
        if(isset($tmp['sooh2_the_proc_sn'])){
            $traceUserProcessInfo.='P_SN:'.$tmp['sooh2_the_proc_sn'].', ';
            unset($tmp['sooh2_the_proc_sn']);
        }
        $otherTraceInfo = $type;
        foreach ($tmp as $k=>$v){
            $otherTraceInfo.=" $k:$v";
        }
        //## 日志信息 ## 其他信息 ## 用户、进程信息  ## 触发位置函数信息以及类数组信息
        return $traceStr.' ## '.$otherTraceInfo.' ## '.$traceUserProcessInfo.' ## '.$traceCallPos." ".$traceObj;
    }    
}

