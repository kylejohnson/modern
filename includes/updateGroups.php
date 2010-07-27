<?php
require("../../../includes/config.php");
$groupName 	= $_REQUEST['groupName'];
$mids		= $_REQUEST['mids'];
$action		= $_REQUEST['action'];

if ($action == "insert") {
 $query = "insert into Groups (Name, MonitorIds) VALUES ('" . $groupName . "', '" . $mids ."')";
} elseif ($action == "update") {
 $query = "update Groups set Name = '" . $groupName . "'";
} elseif ($action == "delete") {
 $query = "delete from Groups where Name = '" . $groupName . "'";
} else {
 die('Invalid Action!');
}

mysql_query($query) or die('Error, query failed!');
?>
