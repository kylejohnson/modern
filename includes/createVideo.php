<?php
require("../../../includes/config.php");
require("../../../includes/functions.php");
$action	= $_REQUEST['action'];
$eid = $_REQUEST['eid'];

if ($action == "video"){
 createVideo2($eid, $path);
}

function createVideo2( $event )
{
    $command = ZM_PATH_BIN."/zmvideo.pl -e ".$event." -f ogg";
    $result = exec( escapeshellcmd( $command ), $output, $status );
 if ($status == 0) {
  echo $result;
 }
    return( $status?"":rtrim($result) );
}
?>
