<?php
 require("../../../includes/config.php");
 $query = "select Id, Name from Monitors order by Name";
 $result = mysql_query($query) or die('Error, selecting monitors failed.');
 while ($row = mysql_fetch_array($result)){
  echo $row['Id'] . ' ' . $row['Name'] . ',';
 }
?>
