<?php
// Change from vscode.dev on iPad

require_once("cloud.class.php");



$cloud = new cloud;

$cloud->key = "AUTH-KEY";
$cloud->usr = "USERNAME";

$cloud->path = '/path/';

if( $cloud->xAuth() ){

if( $cloud->xSend("file.ext") ):

    $cloud->path = '/images';

    if( $cloud->publish() ){
        
        echo $cloud->publish_file;
        
    } else {
     
        echo $cloud->error;
     
    }
    
    

else:

    echo $cloud->error;

endif;


}
