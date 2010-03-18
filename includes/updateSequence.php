<?php
require("../../../includes/config.php");
$action 		= $_POST['action'];
$updateRecordsArray 	= $_POST['monitor'];

if ($action == "sequence"){

	$listingCounter = 1;
	foreach ($updateRecordsArray as $recordIDValue) {
		$query = "update Monitors SET Sequence = " . $listingCounter . " WHERE Id = " . $recordIDValue;
		mysql_query($query) or die('Error, insert query failed');
		$listingCounter = $listingCounter + 1;
	}

}
?>
