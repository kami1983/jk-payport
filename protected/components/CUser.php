<?php

/**
 * Description of CUser
 * 用户API
 * 
 * @author linhai
 */
class CUser {
    
    /**
     * 进行检查某个用户uid 的掩码有效
     * @return boolean
     */
    public static function CheckValid($uid,$masksign){
        
        $user_def=self::GetAccountDefined($uid);
        if(null === $user_def)return false;
        
        //判断是否相等，相等则验证成功
        if($masksign == md5($uid.$user_def['token'])){
            return true;
        }
        
        return false;
    }
    
    /**
     * @return array Description
     */
    public static function GetAccountDefined($uid){
        //定义几个用户
        $user_def=array();
        $user_def['1']=array('token'=>'token_141022_1031',
                             'client_id'=>'AfSbYRAe0Li9JullQ41NFRZrSlOyDrs_TnOzwmXio7uk8-0TOS86vYWXRsF-',
                             'client_secret'=>'EPkh2BDXwnw3604-BQa4Hxdu1aZWAAjStHeymfOsveTE-8m5YsG_VhBlUXIp',);
        
        if(!isset($user_def[$uid]))return null;
        
        return $user_def[$uid];
    }
}
