  $(document).ready(function(){
   var refresh = $("#inptRefresh").val();
   refresh = (refresh * 1000);
   $("#monitors").load("skins/new/views/monitors.php", function() {cb() });

   function cb() {
    $("a[rel='monitor']").colorbox({current:'{current} of {total}'});
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
