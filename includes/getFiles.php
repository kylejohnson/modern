<?php
include("../../../includes/config.php");
$path = $_REQUEST['path'];
$cwd = ZM_PATH_WEB; # Get the current working directory (root path to zm install)
$files = scandir($cwd . "/" . $path); # All of the files inside $path
array_shift($files); # Delete first 3 entires (.., . and .file);
array_shift($files);
array_shift($files);
foreach ($files as $file){ # For each file, push the path + file into paths
 if (preg_match("/capture/i", $file)) 
 {
  echo  $path . $file . " ";
 }
};
?>
