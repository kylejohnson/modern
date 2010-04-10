$(document).ready(function(){
 url = location.search;
 query = "";
 function Display_Load() {
  $(".spinner").fadeIn(900,0);
  $(".spinner").html("<img src='/skins/new/graphics/spinner.gif' />");
 };

 function Hide_Load() {
  $(".spinner").fadeOut('slow');
 };

 function Build_Pagination() {
  Hide_Load();  //Hide spinner
  var monitorName = $("#inptMonitorName").attr("value"); // Get the currently selected monitor.  This is only needed for the first page load, before any filters are set.
  $(".pagination li").click(function() { //If page is changed
   Display_Load(); //Show spinner
   var pageNum = this.id; //Set page number
   if (!(query == "")) {
    $("#events").load("/skins/new/views/pagination_data.php?page=" + pageNum + query, function () { Build_Pagination() }); //Load data to page then rebuild Build_Pagination function
   } else {
    $("#events").load("/skins/new/views/pagination_data.php?page=" + pageNum + "&filter[terms][0][attr]=MonitorName&filter[terms][0][op]==&filter[terms][0][val]=" + monitorName, function () { Build_Pagination() }); //Load data to page then rebuild Build_Pagination function
   }
  });


 // Auto-check the default monitor filter box
 $("#sidebarHistory input").each(function() {
  if ((this.id) == $("#inptMonitorName").attr("value")) {
    $(this).attr("checked", true);
  }
 });

 };

 Display_Load(); //First thing that happens - display spinner
 $("#events").load("/skins/new/views/pagination_data.php" + url, function() { Build_Pagination() }); //Second, load data into #events then build Build_Pagination function

 $('#sidebarHistory input').change(function() { //When a checkbox is checked
  if ($(this).attr("checked") == true) {
  var allVals = []; //Make the array
  $("#sidebarHistory input:checked").each(function() { //For each checked box
   allVals.push(this.id); //Push the checkbox id into the array
  });
  var x = allVals.length; //Get the number of checked boxes
  for (i=0;i<x;i++) { //For each checked box
   if (i>0){ //If more than 1 checked box
    query += "&filter[terms][" + i + "][cnj]=or&filter[terms][" + i + "][attr]=MonitorName&filter[terms][" + i + "][op]==&filter[terms][" + i + "][val]=" + allVals[i]; // add "or"
   } else {
    query = "&filter[terms][" + i + "][attr]=MonitorName&filter[terms][" + i + "][op]==&filter[terms][" + i + "][val]=" + allVals[i]; // Add nothing
   }
  };
  monitorName = this.id
  Display_Load(); //Display spinner
  $("#events").load("/skins/new/views/pagination_data.php?page=1" + query, function() { Build_Pagination()}); // Load data into #events
 }});
});
