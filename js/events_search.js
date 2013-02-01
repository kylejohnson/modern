fnum = 1;

$(document).ready(function(){
query = "";
page = 1;
build_monitors();

$("#inptDateFrom").datepicker();
$("#inptDateTo").datepicker();

$("#btnSubmit").click(function(){
 Build_Query()
});



// FUNCTIONS //
function build_monitors(){
 $.post("skins/modern/includes/getMonitors.php", function(data){
  var monitors = data.split(",");
  monitors.pop();
  var x = monitors.length;
  for (var i=0;i<x;i++){
   var monitor = monitors[i];
   var li = "<li>";
   var li = li + '<input type="checkbox" name="monitorName" id="'+monitor+'" />';
   var li = li + '<label for="'+monitor+'">'+monitor+'</label>';
   var li = li + "</li>";
   $("#monitors_search").append(li);
  }
 });
};

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

 $tabs = $('#tabs_events').tabs({ // Auto-select new tab
  add: function(event, ui) { $tabs.tabs('select', '#' + ui.panel.id); }
  });
 $tabs = $("#tabs_events").tabs('add', "skins/modern/views/pagination_data.php?page=0"+query, 'Search #' + fnum); // Add the tab
 fnum++;

 };



});
function advancedsearch(query) {
	$tabs = $('#tabs_events').tabs({ // Auto-select new tab
		add: function(event, ui) { $tabs.tabs('select', '#' + ui.panel.id); }
	});
	$tabs = $("#tabs_events").tabs('add', "skins/modern/views/pagination_data.php?page=0&"+query, 'Search #' + fnum); // Add the tab
	fnum++;
}
