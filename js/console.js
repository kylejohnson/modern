  $(document).ready(function(){
   var refresh = $("#inptRefresh").val();
   refresh = (refresh * 1000);
   $("#footer a").colorbox({iframe:true, width:'25%', height:'25%'});
   $("#monitors").load("skins/new/views/monitors.php", function(){post_load()});

   function post_load() {
    $("a[rel='monitor']").colorbox({current:'{current} of {total}'});
    width = $("#monitors li:first").width() + 20 + 10;
    count = $("#monitors").children().size();
    ulwidth = ((width * count) /2);
    $("#monitors").css("width", ulwidth);
   }

  setInterval(function() {
   $("#monitors li").each(function() {
   var _this = $(this);
   $(".spinner",_this).html("<img src='skins/new/graphics/spinner.gif' />");
   var mid = $(this).attr("id");
   mid = mid.split("_");
   $(".mon",this).load("skins/new/views/monitors.php?mid=" + mid[1] + " .mon", function () { 
    $(".spinner",_this).fadeOut('slow');
   });
  });
 }, refresh);

  $("#monitors").sortable({ opacity: 0.6, cursor: 'move', update: function() {
  var order = $(this).sortable("serialize") + '&action=sequence';
    $.post("skins/new/includes/updateSequence.php", order);
   }});

 });
