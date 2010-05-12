<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<?php
include("../../../includes/config.php");
$cwd = ZM_PATH_WEB; # Get the current working directory (root path to zm install)
$path = $_REQUEST['path'];
$files = scandir($cwd . '/' . $path);
array_shift($files); # Delete first 3 entires (.., . and .file);
array_shift($files);
array_shift($files);
$paths = array();
foreach ($files as $file){ # For each file, push the path + file into paths
 if (preg_match("/capture/i", $file)){
  array_push($paths, $path . $file);
 }
};
?>
<html>
<head>
 <title>Stills</title>
 <script type="text/javascript" src="../js/jquery-1.4.2.min.js"></script>
 <script type="text/javascript" src="../js/jquery-ui-1.8.custom.min.js"></script>
 <script type="text/javascript" src="../js/jquery.ad-gallery.js"></script>
 <link rel="stylesheet" href="skins/new/css/jquery.ad-gallery.css" type="text/css"/>
 <script type="text/javascript">
  $(function(){
   var galleries = $('.ad-gallery').adGallery();
  });
 </script>
</head>
<body>
<div class="ad-gallery">
  <div class="ad-image-wrapper">
  </div>
  <div class="ad-controls">
  </div>
  <div class="ad-nav">
    <div class="ad-thumbs">
      <ul class="ad-thumb-list">
        <li>
          <a href="images/1.jpg">
            <img src="images/thumbs/t1.jpg" title="Title for 1.jpg">
          </a>
        </li>
        <li>
          <a href="images/2.jpg">
            <img src="images/thumbs/t2.jpg" longdesc="Description of the image 2.jpg">
          </a>
        </li>
      </ul>
    </div>
  </div>
</div>
</body>
</html>
