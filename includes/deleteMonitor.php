<?php
 require("../../../includes/config.php");
 $MonitorId 	= $_REQUEST["MonitorId"];
 $query = "delete from Monitors where Id = " . $MonitorId;
 mysql_query($query) or die('Error, deleting monitor failed.');
 $query = "delete from Events where MonitorId = ".$MonitorId;
 mysql_query($query) or die('Error, deleting associated events failed.');
?>
