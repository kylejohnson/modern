$(function() {
 $("#tabs").tabs(); // Init tabs

 // Add list of monitors
 add_monitors();

 // Load all events for selected monitor 


 //FUNCTIONS//
 function add_monitors(){
  $.post("skins/new/includes/getMonitors.php", function(data){
   var monitors = data.split(","); // Put monitors into array
   monitors.pop(); // Pop off last monitor (it is blank)
   var x = monitors.length; // Number of monitors
   for (var i=0;i<x;i++){
    var monitor = monitors[i];
    $("#tabs").tabs('add', "skins/new/includes/getEvents.php?MonitorName="+monitor, monitor);
   }
  });
 };
 //FUNCTIONS//
});
