query = "";
page = 1;
$(document).ready(function(){
 url = location.search;

 function Display_Load() {
  $(".spinner").fadeIn(900,0);
  $(".spinner").html("<img src='skins/new/graphics/spinner.gif' />");
 };

 function Hide_Load() {
  $(".spinner").fadeOut('slow');
 };

 function Next_Results(){
  Display_Load(); //Show spinner
  var pageNum = this.id; //Set page number
  if (!(query == "")) {
   $(".ad-thumb-list").load("skins/new/views/pagination_data.php?page=" + pageNum + query, function () { Build_Pagination() }); //Load data to page then rebuild Build_Pagination function
  } else {
   $(".ad-thumb-list").load("skins/new/views/pagination_data.php?page=" + pageNum + "&filter[terms][0][attr]=MonitorName&filter[terms][0][op]==&filter[terms][0][val]=" + monitorName, function () { Build_Pagination() }); //Load data to page then rebuild Build_Pagination function
  }
 };

$("#btnPlay").click(function(){
 start = setInterval(function(){changeClass()}, 200);
 $("#btnPause").css("border", "1px solid #C5DBEC");
 $(this).css('border', "1px solid red");
});

function changeClass() {
 if (z<x){
  $("#img_" + (z-1)).attr("class", "eventImageHide");
  $("#img_" + z).attr("class", "eventImage");
  z++;
 }
};

 function Build_Pagination() {
  Hide_Load();  //Hide spinner
  var monitorName = $("#inptMonitorName").attr("value"); // Get the currently selected monitor.  This is only needed for the first page load, before any filters are set.

 // Auto-check the default monitor filter box
 $("#sidebarHistory input").each(function() {
  if ((this.id) == $("#inptMonitorName").attr("value")) {
   $(this).attr("checked", true);
  }
 });

  var galleries = $('.ad-gallery').adGallery({
   slideshow: {enable: false},
   callbacks: {
    afterImageVisible: function() {
     z = 0;
    if (!$("#btnPlay").attr('disabled')) {
     $("#btnPlay").attr('disabled', 'disabled');
     $("#btnPlay").addClass('ui-button-disabled ui-state-disabled');
    }
var context = this;
var src = $('#img_0').attr('src');
var width = $('#img_0').css('width');
var height = $('#img_0').css('height');
var style = 'style="width:' + width + '; height:' + height + ';"';
var pos = src.lastIndexOf('/');
path = src.substr(0,pos+1); // This is the path to the event image directory
var imgs = new Array();

$.post("skins/new/includes/getFiles.php?path=" + path, function(data){ // Get the list of files
 imgs = data.split(" "); // Push the list into the array
 x = imgs.length -1; // Number of images
 y = 1; // Loaded image counter
 for (var i=1;i<x;i++){
  $.preLoadImages(imgs[i], function(){ // Preload the image, then,
   y++;
   var percent = (y / x);
   var result = Math.round(percent*100);
   $("#progress").html("Loading... " + result + "%");
   if (x == y){ // All images are loaded; enable btnPlay
    $("#btnPlay").removeAttr('disabled');
    $("#btnPlay").removeClass('ui-button-disabled ui-state-disabled');
   };
  });
  $(".ad-image").append('<a rel="event" href="'+imgs[i]+'"><img class="eventImageHide" id="img_' + i + '" src="' + imgs[i] + '" style="width:'+width+'; height:'+height+';"/></a>');
 };

 $("#btnStills").colorbox({iframe:true, width:'75%', height:'75%', href:'skins/new/views/stills.php?path='+path});

$('.ad-forward').click(function(){
 var divwidth = $('.ad-thumbs').width(); // The width of the continer (how many px are shown at once)
 var width = $('.ad-thumb-list').width(); // Width of <ul>
 var left = $('.ad-thumbs').scrollLeft(); // How far Left I am
 if (width == (divwidth + left)){
  page = page + 1;
  Build_Query();
  $(".ad-thumb-list").load("skins/new/views/pagination_data.php?page=" + page + query, function(){ Build_Pagination() });
 };
});

});

     
    }
   }
  });
 };

 $("#btnSubmit").click(function() {
  Display_Load();
  Build_Query();
   $(".ad-thumb-list").load("skins/new/views/pagination_data.php?page=1" + query, function() { Build_Pagination()}); // Load data into .ad-thumb-list
  Hide_Load();
 });

 Display_Load(); //First thing that happens - display spinner
 $(".ad-thumb-list").load("skins/new/views/pagination_data.php" + url, function(){ //Second, load data into .ad-thumb-list then build Build_Pagination function
   Build_Pagination();
 }); 

 $('#sidebarHistory li input').change(function() { //When a checkbox is checked
  monitorName = this.id;
  Display_Load();
  Build_Query();
   $(".ad-thumb-list").load("skins/new/views/pagination_data.php?page=1" + query, function() { Build_Pagination()}); // Load data into .ad-thumb-list
  Hide_Load();
 });



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


  // First filters are Monitor Names, lets do those!
  for (var i=0;i<x;i++) {
   if (x==1) { // Only filter, no ( or )
    query = "&filter[terms][" + i + "][obr]=0&filter[terms][" + i + "][attr]=MonitorName&filter[terms][" + i + "][op]==&filter[terms][" + i + "][val]=" + aryMonitors[i] + "&filter[terms][" + i + "][cbr]=0";
   } else if (i == 0){ // First filter, open the (
    query = "&filter[terms][" + i + "][obr]=1&filter[terms][" + i + "][attr]=MonitorName&filter[terms][" + i + "][op]==&filter[terms][" + i + "][val]=" + aryMonitors[i] + "&filter[terms][" + i + "][cbr]=0";
   } else if ( !(i==0) && (i<(x-1)) ) { // Any filter that is not the first or last, no ( or )
    query += "&filter[terms][" + i + "][cnj]=or&filter[terms][" + i + "][obr]=0&filter[terms][" + i + "][attr]=MonitorName&filter[terms][" + i + "][op]==&filter[terms][" + i + "][val]=" + aryMonitors[i] + "&filter[terms][" + i + "][cbr]=0";
   } else { // Last filter, close the )
    query += "&filter[terms][" + i + "][cnj]=or&filter[terms][" + i + "][obr]=0&filter[terms][" + i + "][attr]=MonitorName&filter[terms][" + i + "][op]==&filter[terms][" + i + "][val]=" + aryMonitors[i] + "&filter[terms][" + i + "][cbr]=1";
   }
  };

  // Next filters are dates, if filled in
  if ((Dfrom != "") && (Dto != "")) {
   query +=  "&filter[terms][" + i + "][cnj]=and&filter[terms][" + i + "][obr]=0&filter[terms][" + i + "][attr]=Date&filter[terms][" + i + "][op]=%3E%3D&filter[terms][" + i + "][val]=" + Dfrom + "&filter[terms][" + i + "][cbr]=0";
   i++;
   query += "&filter[terms][" + i + "][cnj]=and&filter[terms][" + i + "][obr]=0&filter[terms][" + i + "][attr]=Date&filter[terms][" + i + "][op]=%3C%3D&filter[terms][" + i + "][val]=" + Dto + "&filter[terms][" + i + "][cbr]=0";
  }

  // Next filters are times, if filed in
  if ((Tfrom != "") && (Tto != "")) {
   i++;
   query +=  "&filter[terms][" + i + "][cnj]=and&filter[terms][" + i + "][obr]=0&filter[terms][" + i + "][attr]=Time&filter[terms][" + i + "][op]=%3E&filter[terms][" + i + "][val]=" + Tfrom + "&filter[terms][" + i + "][cbr]=0";
   i++;
   query += "&filter[terms][" + i + "][cnj]=and&filter[terms][" + i + "][obr]=0&filter[terms][" + i + "][attr]=Time&filter[terms][" + i + "][op]=%3C&filter[terms][" + i + "][val]=" + Tto + "&filter[terms][" + i + "][cbr]=0";
  }

  // Last filter is a specific event ID.  If filled in, filter on only that.
  if (eid >= 1) {
   i = 0;
   query =  "&filter[terms][" + i + "][attr]=Id&filter[terms][" + i + "][op]=%3D&filter[terms][" + i + "][val]=" + eid;
  }

  return(query);

 };
});
