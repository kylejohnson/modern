$(function() {
  $("#content").load("/skins/new/views/adminMonitors.php", function() { Load_Monitors() });

 function Load_Monitors() {
  $("#delMonitor").click(function() {
   if (confirm('Are you sure you want to delete the selected monitors and all associated events?')) {
    $("#tblMonitors input:checked").each(function() {
     var MonitorId = $(this).attr("value");
     $.post("skins/new/includes/deleteMonitor.php?MonitorId="+ MonitorId);
    });
   }
   $("#content").load("/skins/new/views/adminMonitors.php");
  });
 $("#addMonitor").colorbox({iframe:true, innerWidth:340, innerHeight:400});
 }
});
