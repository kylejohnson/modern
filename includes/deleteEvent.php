<?php
 require("../../../includes/config.php");
 $eid 	= $_REQUEST["eid"];
 $query = "delete from Events where Id = " . $eid;
 mysql_query($query) or die('Error, deleting event failed.');
?>
