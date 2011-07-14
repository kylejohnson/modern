$(function() {
  $("#content").load("skins/new/views/adminMonitors.php", function() { Load_Monitors() });

 function Load_Monitors() {
  $("#delMonitor").click(function() {
   if (confirm('Are you sure you want to delete the selected monitors and all associated events?')) {
    $(".spinner").fadeIn(900,0);
    $(".spinner").html("<img src='skins/new/graphics/spinner.gif' />");
    $("#tblMonitors input:checked").each(function() {
     var MonitorId = $(this).attr("value");
     $.post("skins/new/includes/deleteMonitor.php?MonitorId="+ MonitorId);
    });
    $(".spinner").fadeOut('slow');
   }
   $("#content").load("skins/new/views/adminMonitors.php", function() { Load_Monitors() });
  });
 $("#addMonitor").colorbox({iframe:true, innerWidth:340, innerHeight:400});

  $("a[rel='function']").colorbox({ // Colorbox for Function links
   preloading:false,
   current:'{current} of {total}',
   width:'250px',
  });
  $("a[rel='zones']").colorbox({ // Colorbox for Zones links
   preloading:false,
   current:'{current} of {total}',
  });
 }


});
