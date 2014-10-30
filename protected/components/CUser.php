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
        
        $userlist_file= Yii::app()->basePath.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'userlist.conf.php';
        
        if(!is_file($userlist_file)){
            //查找 sample 文件
            if(!is_file($userlist_file.'.sample')){
                throw new Exception('Sample file not found.','141030_1057');
            }
            
            //copy sample 文件
            copy($userlist_file.'.sample',$userlist_file);
        }
        
        $user_def=  require $userlist_file;
        
        if(!isset($user_def[$uid]))return null;
        
        return $user_def[$uid];
    }
}
