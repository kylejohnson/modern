<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
 <title>Stills</title>
 <script type="text/javascript" src="../js/jquery-1.4.2.min.js"></script>
 <script type="text/javascript" src="../js/jquery-ui-1.8.custom.min.js"></script>
 <link rel="stylesheet" href="../css/skin.css" type="text/css"/>
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
     $("#stills").append('<li><a href="/'+imgs[i]+'">'+'<img src="/'+imgs[i]+'"/>'+'</a></li>');
    };
    $("#stills img").attr('width', '250');
   });
  });
 </script>
</head>
<body>
<ul id="stills">

</ul>
</body>
</html>
