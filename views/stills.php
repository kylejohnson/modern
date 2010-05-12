<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
 <title>Stills</title>
 <script type="text/javascript" src="../js/jquery-1.4.2.min.js"></script>
 <script type="text/javascript" src="../js/jquery-ui-1.8.custom.min.js"></script>
 <script type="text/javascript" src="../js/jquery.ad-gallery.js"></script>
 <link rel="stylesheet" href="../css/jquery.ad-gallery.css" type="text/css"/>
 <script type="text/javascript">
  $(function(){
   var imgs = new Array();
   var qs = window.location.search.substring(1);
   var wtf = qs.split("=");
   var path = wtf[1];
   
   $.post("../includes/getFiles.php?path=" + path, function(data){
    imgs = data.split(" ");
    var x = imgs.length;
    for (var i=0;i<x;i++){
     $(".ad-thumb-list").append('<li><a href="/'+imgs[i]+'">'+'<img src="/'+imgs[i]+'"/>'+'</a></li>');
    };
    $(".ad-thumb-list img").attr('width', '250');
    build_gallery();
   });
 
   function build_gallery(){

   var galleries = $('.ad-gallery').adGallery();
   };
  });
 </script>
</head>
<body>
<div id="gallery" class="ad-gallery">
  <div class="ad-image-wrapper">
  </div>
  <div class="ad-controls">
  </div>
  <div class="ad-nav">
    <div class="ad-thumbs">
      <ul class="ad-thumb-list">
      </ul>
    </div>
  </div>
</div>
</body>
</html>
