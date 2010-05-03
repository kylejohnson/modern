<?php
require("../../../includes/config.php");
require("../../../includes/functions.php");
$action	= $_REQUEST['action'];
$eid = $_REQUEST['eid'];
$path = $_REQUEST['path'];

if ($action == "video"){
 createVideo2($eid, $path);
}

function createVideo2( $event, $p )
{
    $command = ZM_PATH_BIN."/zmvideo.pl -e ".$event." -f avi";
    $result = exec( escapeshellcmd( $command ), $output, $status );
 if ($status == 0) {
  echo('<a href="' . $p . $result .'">' . $result . '</a>');
 } else {
  echo $result;
 }
    return( $status?"":rtrim($result) );
}
?>
