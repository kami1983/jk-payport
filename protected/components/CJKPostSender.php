<?php

/*
#编码人：linxuanxu
#创建日期：2008-06-25
#阶段：上线
#Bug：Nothing
#修改人：linxuanxu
#最后修改日期：2008-09-01 :更改了构造方法的名字，为了和QBV3系统统一，将所有private属性更改为protected
*/



/*
 * Author:Joy.Kami
 * Create Data:2010-02-25
 * Last Modify Data:2010-02-25
 * UML Location:需要补充这部分的UML
 * Explan:实体类，用于发送和接收POST或者GET请求可以控制超时的时间
*/

///:引入钱包异常信息包
//include_once(QB_BASEPATH.'/QBException/IncludeQBException.include.php');
///:向某些一个服务器页面发送POST请求.
///:使用方法见详细的注释.
///:在2008年7月份有增强此类功能,主要增加的功能是针对网络复杂的情况而设定的方超时功能.
///:现在具备访问发送请求后不监听结果的功能,和发送请求后在容忍时间内监听请求的功能.
class CJKPostSender extends CJKClassBase{

    ///:构造方法
    public function __construct() {
        $this->setContentType(self::CONTENT_TYPE_APPLICATION_FROM_URLENCODEED);
    }

    //设置Content-type
    public function setContentType($contentType){
        $this->_contentType=$contentType;
    }

    ///:
    ///:	
    ///:	
    ///:	
    ///:	
    /**
     * 初始化一个POST请求发送类
     * @param string $urlStr 是一个要请求页面URL地址.一般应该包含http://如果页面就在本服务器,则没必要,加上路径即可但路径必须为绝对.
     * @param string $postList 是一个请求列表,必须使用相连数组的形式带入即 name=>value 的形式数组.
     * @param int $portNum 是端口默认为80一般情况忽略即可.
     * @param int $timeOut 是超时时间默认为30一般不需要更改.
     * @return CJKPostSender
     */
    public function setSender($urlStr, $postList,$portNum=80,$timeOut=30,$agentStr='JKGLib-CJKPostSender') {
        if($agentStr==null)$agentStr='JKGLib-CJKPostSender';
        $this->_agentStr=$agentStr; //请求的标识信息
        $this->urlSplit($urlStr);
        $this->_postDates=$this->dateSplit($postList);

        @$this->_filePoint=fsockopen($this->_operHost,$portNum, $errno, $errstr,$timeOut);
        if(!$this->_filePoint)
            throw new Exception('ERROR CODE::CJKPostSender_1136 Content::远程服务器没有相应！1、检查请求地址正确。2、检查网络是否连接。3、确认我方域是否被对方允许。4、检查客户端标识字符串是否被对方允许我方默认QBGAPD.qian8ao。5、检查对方网站允许端口是否为80，如果不是可以更改发送到对方的端口参数'."其他信息：{$errno} / {$errstr}");
    }

    ///:析构函数.
    public function __destruct() {
        $this->_operURL=null;
        @fclose($this->_filePoint);
    }

    ///:请求数据解析函数,外部无需调用.
    protected function dateSplit($postList) {
        $returnResult='';
        foreach($postList as $key=>$value) {
            $returnResult.="{$key}=".urlencode($value)."&";
        }
        $returnResult=substr($returnResult,0,strlen($returnResult)-1);
        return $returnResult;
    }

    ///:请求解析函数,外部无需调用.
    protected function urlSplit($urlStr) {
        $tmpArr=explode("http://",$urlStr);

        if(count($tmpArr)<=1) {
            $this->_operHost='localhost';
            $this->_operURL=$urlStr;
        }else {
            $urlStr=$tmpArr[1];
            $intNum=strpos($urlStr,'/');
            $this->_operHost=substr($urlStr,0,$intNum);
            $this->_operURL=substr($urlStr,$intNum);
        }
    }

    ///:返回请求后的结果数据
    ///:参数作用
    ///:$NeedReturn=true  代表是否需要返回值
    ///:$timeOut=NULL 返回值响应接受时间
    ///:返回值说明：
    ///:getDatas(false) 发送Post后不需要接受任何参数,改方法将返回一个NULL
    ///:getDatas(true)  要求返回取得的串,不惜时间代价
    ///:getDatas(true,int) 要求返回取得的串,但是必须在int指定的容忍时间内返回
    public function getDatas($NeedReturn=true,$timeOut=null) {

        if($timeOut!=null) {
            if(!is_numeric($timeOut)) {
                throw new Exception('ERROR CODE::CJKPostSender_1141 Content::您完全可以忽略$timeOut参数，或者使用null值屏蔽它但是您应该注意它仅仅可以接受一个数值而非字符串或者boolean等类型');
            }
        }

        if($this->_filePoint) {

            $oper=$this->_filePoint;
            ############################################################ OLD CODE ############
            fwrite($oper, "POST {$this->_operURL} HTTP/1.1\r\n");
            fwrite($oper, "Host: " .$this->_operHost. "\r\n");
            fwrite($oper, "Content-type: {$this->_contentType}\r\n");
            fwrite($oper, "Content-length:".strlen($this->_postDates)."\r\n");
            /*/*///fwrite($oper, "Content-length:0\r\n");
            fwrite($oper, 'Content-Disposition: form-data; name="1.txt"; filename="C:\1.txt"\r\n'); //
            fwrite($oper, "User-Agent: $this->_agentStr\r\n");
            fwrite($oper,"Pragma:no-cache\r\n");
            fwrite($oper, "Accept: */*\r\n");
            fwrite($oper,"Connection: Close\r\n");
            fwrite($oper, "\r\n");
            fwrite($oper, "$this->_postDates\r\n");
            fwrite($oper, "\r\n");
            #################################################################################

            if($timeOut!=NULL) { //判断如果使用请求超时变量
                //-->设置请求超时:
                stream_set_timeout($oper,$timeOut);
            }

            if(!$NeedReturn) { //判断是否需要返回值
                fclose($this->_filePoint);
                return NULL;
            }

            $content='';

            //$content = fread($oper,999999);
            while (!feof($oper)) {
                $content.= fgets($oper, 1024);

                if($timeOut!=null) { //判断如果使用请求超时变量
                    $info = stream_get_meta_data($oper);
                    if ($info['timed_out']) {
                        fclose($oper);
                        throw new ExceptionTimeOut('ErrorCode:5.QBGAPD_200807291527&5.5.SUB_1108_1145 Content:获取数据中间造成超时，一般情况是网络不稳定造成。但不可以武断的确定为网络问题，该文题原因还有可能是因为您在设置了获取数据的超时时间，而对方没有在该时间内返回数据造成的。');
                    }
                }
            }

            $num=strpos($content,"<");
            $content=substr($content,$num);
            fclose($this->_filePoint);
            return $content;
        }else {
            throw new ExceptionTimeOut('ErrorCode:5.QBGAPD_200807291527&5.5.SUB_1108_1147 Content:连接已经中断原因可能是因为您过早的初始化类造成的，一般重新实例化类可以解决此问题');
        }
    }

    protected $_operHost=null; //主机
    protected $_operURL=null; //URL地址
    protected $_filePoint=null; //文件指针
    protected $_postDates=null; //表单数据
    protected $_agentStr=null; //我方请求标识
    protected $_contentType=null;

    const CONTENT_TYPE_APPLICATION_FROM_URLENCODEED='application/x-www-form-urlencoded';
    const CONTENT_TYPE_MULTIPART_FROM_DATA='multipart/form-data';

}

?>