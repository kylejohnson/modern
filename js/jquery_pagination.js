query = "";
$(document).ready(function(){
 url = location.search;

 function Display_Load() {
  $(".spinner").fadeIn(900,0);
  $(".spinner").html("<img src='skins/new/graphics/spinner.gif' />");
 };

 function Hide_Load() {
  $(".spinner").fadeOut('slow');
 };

 function Build_Pagination() {
  $("a[rel='event']").colorbox({
   current:'event {current} of {total}',
   iframe:'true',
   width:"75%",
   height:"95%",
   preloading:'false',
  });
  Hide_Load();  //Hide spinner
  var monitorName = $("#inptMonitorName").attr("value"); // Get the currently selected monitor.  This is only needed for the first page load, before any filters are set.
  $(".pagination li").click(function() { //If page is changed
   Display_Load(); //Show spinner
   var pageNum = this.id; //Set page number
   if (!(query == "")) {
    $("#events").load("skins/new/views/pagination_data.php?page=" + pageNum + query, function () { Build_Pagination() }); //Load data to page then rebuild Build_Pagination function
   } else {
    $("#events").load("skins/new/views/pagination_data.php?page=" + pageNum + "&filter[terms][0][attr]=MonitorName&filter[terms][0][op]==&filter[terms][0][val]=" + monitorName, function () { Build_Pagination() }); //Load data to page then rebuild Build_Pagination function
   }
  });

 // Auto-check the default monitor filter box
 $("#sidebarHistory input").each(function() {
  if ((this.id) == $("#inptMonitorName").attr("value")) {
    $(this).attr("checked", true);
  }});

 };

 $("#liSpecificDate").click(function() {
  $("#filterSpecificDate").css("display", "block");
 });

 $("#btnSubmit").click(function() {
  Display_Load();
  Build_Query();
  Hide_Load();
 });

 Display_Load(); //First thing that happens - display spinner
 $("#events").load("skins/new/views/pagination_data.php" + url, function() { Build_Pagination() }); //Second, load data into #events then build Build_Pagination function

 $('#sidebarHistory li input').change(function() { //When a checkbox is checked
  if ($(this).attr("checked") == true) {
   monitorName = this.id;
   Display_Load();
   Build_Query();
   Hide_Load();
  }});




 function Build_Query() {
  var aryMonitors = [];

  $("#sidebarHistory input:checked").each(function() { // For each checked monitor
   aryMonitors.push(this.id); // Push its name into the array
  });
  var x = aryMonitors.length; // Number of checked monitors
  
  var Dfrom = $("#inptDateFrom").val();
  var Dto = $("#inptDateTo").val();
  var Tfrom = $("#inptTimeFrom").val();
  var Tto = $("#inptTimeTo").val();
  var eid = $("#inptEventID").val();

  for (var i=0;i<x;i++) {
   if (i>0) {
    query += "&filter[terms][" + i + "][cnj]=or&filter[terms][" + i + "][attr]=MonitorName&filter[terms][" + i + "][op]==&filter[terms][" + i + "][val]=" + aryMonitors[i];
   } else {
    query = "&filter[terms][" + i + "][attr]=MonitorName&filter[terms][" + i + "][op]==&filter[terms][" + i + "][val]=" + aryMonitors[i];
   }
  };

  if ((Dfrom != "") && (Dto != "")) {
   query +=  "&filter[terms][" + i + "][cnj]=and&filter[terms][" + i + "][obr]=0&filter[terms][" + i + "][attr]=Date&filter[terms][" + i + "][op]=%3E%3D&filter[terms][" + i + "][val]=" + Dfrom + "&filter[terms][" + i + "][cbr]=0";
   i++;
   query += "&filter[terms][" + i + "][cnj]=and&filter[terms][" + i + "][obr]=0&filter[terms][" + i + "][attr]=Date&filter[terms][" + i + "][op]=%3C%3D&filter[terms][" + i + "][val]=" + Dto + "&filter[terms][" + i + "][cbr]=0";
  }
  if ((Tfrom != "") && (Tto != "")) {
   i++;
   query +=  "&filter[terms][" + i + "][cnj]=and&filter[terms][" + i + "][obr]=0&filter[terms][" + i + "][attr]=Time&filter[terms][" + i + "][op]=%3E&filter[terms][" + i + "][val]=" + Tfrom + "&filter[terms][" + i + "][cbr]=0";
   i++;
   query += "&filter[terms][" + i + "][cnj]=and&filter[terms][" + i + "][obr]=0&filter[terms][" + i + "][attr]=Time&filter[terms][" + i + "][op]=%3C&filter[terms][" + i + "][val]=" + Tto + "&filter[terms][" + i + "][cbr]=0";
  }
  if ((eid >= 1) && (Dfrom == "") && (Dto == "") && (Tfrom == "") && (Tto == "")) {
   i = 0;
   query =  "&filter[terms][" + i + "][attr]=Id&filter[terms][" + i + "][op]=%3D&filter[terms][" + i + "][val]=" + eid;
  }

   $("#events").load("skins/new/views/pagination_data.php?page=1" + query, function() { Build_Pagination()}); // Load data into #events

 };
});
