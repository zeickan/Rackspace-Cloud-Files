<?php

/*
 * class rackspace cloud files
 * @author Andros Romo <andros@pixblob.com>
 * #twitter @andorosu
 */

class rackspace {
    
    /*
     * __construct()
     */
    
    function __construct() {
        
    }
    
    protected function auth($auth_key = '',$auth_user = ''){
        
        exec('curl -D - -H "X-Auth-Key: '.$auth_key.'" -H "X-Auth-User: '.$auth_user.'" https://auth.api.rackspacecloud.com/v1.0',$f);
        
        $array = array();
        
        foreach($f as $k => $v ){
            
            preg_match_all("@([^:]+)@i",$v,$var);
            
            $var = $var[0][0];
            
            switch( $var ){
            
                case( $var == "X-Storage-Url"):
                
                    $v = trim(str_replace($var.':','',$v));
                    
                    if( !empty($v) ):
                    $array[xurl] = $v;
                    endif;
                break;
            
                case( $var == "X-CDN-Management-Url" ):
                
                    $v = trim(str_replace($var.':','',$v));
                
                    if( !empty($v) ):
                    $array[cdn] = $v;
                    $this->CDN_URL = $v;
                    endif;
                
                break;
            
                case( $var == "X-Auth-Token" ):
                
                    $v = trim(str_replace($var.':','',$v));
                    if( !empty($v) ):
                    $array[auth] = $v;
                    endif;
                break;
            
                default:
                    
                break;  
            
            }           
            
        }
        
        return $array;
        
    }
    
    /*
     * function sendFile
     * @param $file
     */
    
    protected function sendFile($file,$meta = 'Description') {
        
        if( is_array($file) ):
        
            $tmp = $file[0];
            
            $fnl = $file[1];
        
        else:
        
            $tmp = $file;
            
            $fnl = $file;
        
        endif;
        
        exec('curl -X PUT -T '.$tmp.' -D - -H "X-Auth-Token: '.$this->auth_token.'" -H "X-Object-Meta-Screenie: '.$meta.'" '.$this->url.''.$fnl.'',$f);
        
        foreach($f as $k => $v){
            
            preg_match_all("@([^:]+)@i",$v,$var);
            
            $var = trim($var[0][0]);
            
            if( $var == "HTTP/1.1 201 Created"){
                
                $return = true;
                
            }
        }
        
        if( $return ){        
            return true;        
        } else {            
            $this->error = "Cloud error";            
            return false;        
        }
        
    }
    
    /*
     * function CDN
     * @param $arg
     */
    
    protected function CDN($arg = true) {
        
        $enabled = $arg?'True':'False';
        
        $url = $this->CDN_URL.$this->path;
        
        exec('curl -X PUT -D - -H "X-Auth-Token: '.$this->auth_token.'" -H "X-CDN-Enabled: '.$enabled.'" -H "X-TTL: '.$this->ttl.'" '.$url,$f);
        
        $response = array();
        
        foreach($f as $k => $v){
            
            preg_match_all("@([^:]+)@i",$v,$var);
            
            $var = trim($var[0][0]);
            
            if( !empty($var) ){
                
                 $v = trim(str_replace($var.':','',$v));
            
                $response[$var] = $v;
            
            }
            
            
        }
        
        #echo"<pre>".print_r($response,1)."</pre>";
        
        #echo 'curl -X PUT -D - -H "X-Auth-Token: '.$this->auth_token.'" -H "X-CDN-Enabled: '.$enabled.'" -H "X-TTL: '.$this->ttl.'" '.$url;
        
        $this->CDN_RESPONSE = $response;
        
        if( $response["X-CDN-URI"] ){
            
            return true;
            
        } else {
            
            $this->error = "CDN enabled failed.";
            
            return false;
        
        }
        
    }
    
}

/*
 * class cloud
 */

final class cloud extends rackspace {
    
    /*
     * __construct()
     */
    
    function __construct() {
        
        $this->ttl = 94348800;
        
        $this->path = "/";
        
    }
    
    public function getFileName($path){
                    
            if( strstr($path,"/") ):
                    
                preg_match_all("@(.*)/(.*)$@i",$path, $out);
                    
               $out = $out[2][0];
                    
            else:
                    
                $out = $path;
                    
            endif;
            
            
            preg_match_all('=^[^/?*;:{}\\\\]+\.[^/?*;:{}\\\\]+$=',$out,$out);
            
            $out = $out[0][0];
                    
            return $out;
                    
    }
    
    
    
    public function xAuth(){

        $auth = parent::auth($this->key,$this->usr);
        
        $this->url = $auth[xurl].$this->path;
        
        $this->auth_token = $auth[auth];
        
        if( is_array($auth) && !empty($auth[xurl]) && !empty($auth[auth]) ){
            
            return true;
            
        } else {
            
            $this->error = "Authentication failed error";
            
            return false;
        
        }
        
    }
    
    
    public function xSend($file , $meta = 'No description'){
        
        if( file_exists($file) ){
        
        $file_name = $this->getFileName($file);
        
        $this->file_name = $file_name;
        
            $response = parent::sendFile( array($file,$file_name), $meta);
        
        } else {
            
            $response = false;
            
            $this->error = "File does not exists";
            
        }
        
        return $response;
        
    }
    
    
    public function publish(){
        
        if( parent::CDN() ):
        
        $this->publish_file = $this->CDN_RESPONSE["X-CDN-URI"].'/'.$this->file_name;
        
        return true;
        
        else:
        
        return false;
        
        endif;
        
    }
    
}





