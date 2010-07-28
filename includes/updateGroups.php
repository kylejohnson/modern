<?php
require("../../../includes/config.php");
$groupName 	= $_REQUEST['groupName'];
$mids		= $_REQUEST['mids'];
$action		= $_REQUEST['action'];

if ($action == "insert") { # Insert a new group
 $existing = mysql_query("select Id from Groups where Name = '" . $groupName . "'");
 if (!$row = mysql_fetch_array($existing)) { # Only if the group does not already exist
  $query = "insert into Groups (Name, MonitorIds) VALUES ('" . $groupName . "', '" . $mids ."')";
 }
} elseif ($action == "update") { # Update a group (Currently name only)
 $query = "update Groups set Name = '" . $groupName . "'";
} elseif ($action == "delete") { # Delete a group
 $query = "delete from Groups where Name = '" . $groupName . "'";
} elseif ($action == "select") { # Select all the tabs to be loaded into the Daskboard
 $query = "select Name from Groups";
} else {
 die('Invalid Action!');
}

$result = mysql_query($query) or die('Error, query failed!');
while($row = mysql_fetch_array($result)){
 echo $row['Name'] . ',';
}
?>
