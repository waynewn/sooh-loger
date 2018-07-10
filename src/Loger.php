<?php
namespace Sooh;
/**
 * 日志记录（copy from github/sooh2）
 * 使用方法：
 *      1）初始化实例，参看 getInstance() 方法
 *      2）如果是要自定义文件名路径名，调用 initFileTpl()设置
 *      3）当一个新的请求开始时，调用initOnNewRequest()
 *      4）根据需要，尽可能早的设置其他信息，
 *              比如用户信息：initSession() 
 *              比如其他自定义信息：initMoreInfo()
 *      5）需要记录日志的地方，调用：
 *              app_common() 常规日志（应用级）,不受tracelevel限制
 *              app_trace()  跟踪日志（应用级）
 *              app_error()错误报警日志（应用级）,不受tracelevel限制
 *              lib_trace()  跟踪日志（库级）
 *              lib_error()错误报警日志（库级）,不受tracelevel限制
 *              sys_trace()  跟踪日志（系统底层）
 *              sys_error()错误报警日志（系统底层） ,不受tracelevel限制
 *          2个参数，其中一个可以是非字符串标量的数组之类的，将被var_export方式输出
 * 
 * 其他：可以通过 traceLevel()获取和设置新的tracelevel
 *      除了common,其他的日志，都会记录当时是哪个类的哪个函数触发的
 * 
 * 输出日志格式：
 * 时间信息 ## 日志信息 ## 其他自定义信息 ## 用户、环境、进程信息  ## 触发位置函数信息以及类数组信息
 */
        /**
     * 初始化、获取Loger实例
     * 
     * 如果是获取实例，参数为null，获取设置好的实例
     * 如果是初始化，分两种：
     *   （参数是兼容的实例）使用兼容的实例，初始化作为本类的instance实例
     *   （参数是日志级别）生成本类的实例
     * 
     *   日志的级别（记录哪些日志）   
     *          1）system
     *          2）lib
     *          3）system 和 lib   
     *          4）application
     *          5）application & system
     *          6）application & lib
     *          7）all 
     *     
     * @param mixed $newInstance_or_traceLevel 
     * @return Loger
     */
class Loger{
    protected $errorsDriver;
    protected $commonDriver;
    protected $tracesDriver;
    /**
     *
     * @var \Sooh\LogClasses\LogParts 
     */
    protected $parts;
    protected static $_instance = null;
    /**
     * 获取当前运行中的唯一实例
     * @return \Sooh\Ini
     */
    public static function getInstance($commonDriver=null,$errorDriver=null,$traceDriver=null,$logParts=null)
    {
        if(self::$_instance==null){
            $c = get_called_class();
            self::$_instance = new $c;
            if($logParts==null){
                $logParts = new \Sooh\LogClasses\LogParts;
            }
            self::$_instance->parts = $logParts;
            self::$_instance->parts->reset();
            self::$_instance->traceLevel = 7;
        }
        $defaultDriver = new \Sooh\LogClasses\Driver\PHPDefault();
        if($commonDriver!==null){
            self::$_instance->commonDriver = $commonDriver;
        }
        if(self::$_instance->commonDriver==null){
            self::$_instance->commonDriver = $defaultDriver;
        }
        if($errorDriver!==null){
            self::$_instance->errorsDriver = $errorDriver;
        }
        if(self::$_instance->errorsDriver==null){
            self::$_instance->errorsDriver = $defaultDriver;
        }        
        if($traceDriver!==null){
            self::$_instance->tracesDriver = $traceDriver;
        }
        if(self::$_instance->tracesDriver==null){
            self::$_instance->tracesDriver = $defaultDriver;
        }
        
        return self::$_instance;
    }
    
    const trace_sys = 1;
    const trace_lib = 2;
    const trace_app = 4;    
    protected $traceLevel=0;
    /**
     * 设置新的trace level
     */
    public function setTraceLevel($newLv=null)
    {
        $this->traceLevel = $newLv;
        return $this;
    }    
    /**
     * 获取trace level
     */
    public function getTraceLevel()
    {
        return $this->traceLevel;
    }
    /**
     * 开始处理一个新的请求的时候，调用此函数完成一些初始化（创建当前请求的唯一标识）
     */
    public function initOnNewRequest($uri,$remote_addr,$reqSn=null,$timestamp=null)
    {
        if($reqSn===null){
            $reqSn = md5(gethostname().'-'. getmypid().'-'. microtime(true).'-'.rand(100000,999999));
        }
        if($timestamp===null){
            $timestamp = time();
        }
        $splitBy = new \Sooh\LogClasses\SplitBy($timestamp);
        if($this->errorsDriver){
            $this->errorsDriver->onNewRequest($splitBy);
        }
        if($this->commonDriver){
            $this->commonDriver->onNewRequest($splitBy);
        }
        if($this->tracesDriver){
            $this->tracesDriver->onNewRequest($splitBy);
        }
        $this->parts->reset();
        $this->parts->remoteIp = $remote_addr;
        $this->parts->requestUri = $uri;
        $this->parts->requestSN = $reqSn;

        return $this;
    }
    public function getReqLogSN()
    {
        return $this->parts->requestSN;
    }
    /**
     * 记录当前请求的会话sessionid 和 userid, 给null的时候不记录，给 ''记录
     * @param type $sessId 给null的时候不记录，给 ''记录
     * @param type $userId 给null的时候不记录，给 ''记录
     */
    public function initSession($sessId,$userId)
    {
        if($sessId!==null){
            $this->parts->sessionid=$sessId;
        }
        if($userId!==null){
            $this->parts->userid=$userId;
        }
        return $this;
    }
    
    /**
     * 记录更多信息
     * @return \Sooh2\Misc\Loger
     */
    public function initMoreInfo($k,$v)
    {
        if(isset($this->parts->$k)){
            $this->parts->$k = $v;
        }else{
            $this->parts->custom[$k]=$v;
        }
        return $this;
    }

    public function sys_trace($msgOrObj,$extmsg=null)
    {
        if($this->traceLevel & self::trace_sys){
            $this->tracesDriver->write($this->parts->prepare('trace','sys', $msgOrObj, $extmsg));
        }
    }
    public function sys_error($msgOrObj,$extmsg=null)
    {
        $this->errorsDriver->write($this->parts->prepare('error','sys', $msgOrObj, $extmsg));
    }
    public function app_common($msgOrObj,$extmsg=null)
    {
        $this->commonDriver->write($this->parts->prepare('common','app', $msgOrObj, $extmsg));
    }
    public function app_trace($msgOrObj,$extmsg=null)
    {
        if($this->traceLevel & self::trace_app){
            $this->tracesDriver->write($this->parts->prepare('trace','app', $msgOrObj, $extmsg));
        }
    }
    public function app_error($msgOrObj,$extmsg=null)
    {
        $this->errorsDriver->write($this->parts->prepare('error','app', $msgOrObj, $extmsg));
    }
    public function lib_trace($msgOrObj,$extmsg=null)
    {
        if($this->traceLevel & self::trace_lib){
            $this->tracesDriver->write($this->parts->prepare('trace','lib', $msgOrObj, $extmsg));
        }
    }
    public function lib_error($msgOrObj,$extmsg=null)
    {
        $this->errorsDriver->write($this->parts->prepare('error','lib', $msgOrObj, $extmsg));
    }        
}
