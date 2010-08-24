$(document).ready(function() {
 $tabs = $("#tabs").tabs({
  select: function(event, ui) {
   $(".thumb").remove();
   page = 1;
  },
  load: function(event, ui) {
   $(".event").colorbox({width:"50%", height:"50%"});
  }
 });
 page = 1;
 add_monitors();
 setup_is();

 


 //FUNCTIONS//
 function add_monitors(){
  $.post("skins/new/includes/getMonitors.php", function(data){
   var monitors = data.split(","); // Put monitors into array
   monitors.pop(); // Pop off last monitor (it is blank)
   var x = monitors.length; // Number of monitors
   for (var i=0;i<x;i++){
    var monitor = monitors[i];
    $tabs.tabs('add', "skins/new/includes/getEvents.php?MonitorName="+monitor, monitor);
   }
  });
 };

 function setup_is(){
  $(window).scroll(function(){
   if ($(window).scrollTop() == $(document).height() - $(window).height()){
    FetchMore();
   }
  });
 }

 function FetchMore(){
  var MonitorName = $('li.ui-state-active a span').text();
  $.post("skins/new/includes/getEvents.php?MonitorName="+MonitorName+"&page="+page, function(data){
   if (data != "") {
   }
  });
  page = page + 1;
 }
 //FUNCTIONS//
});
