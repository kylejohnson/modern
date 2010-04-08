$(document).ready(function(){

var url = location.search;
	function Display_Load() {
	 $("#loading").fadeIn(900,0);
         $("#loading").html("<img src='/skins/new/graphics/bigLoader.gif' />");
	};

	function Hide_Load() {
         $("#loading").fadeOut('slow');
	};

	function Build_Pagination(query) {
         Hide_Load();

	 $("#pagination li").click(function() {
           Display_Load();
	  var pageNum = this.id;
          var monitorName = $("#inptMonitorName").attr("value");
	  $("#events").load("/skins/new/views/pagination_data.php?&page=" + pageNum + query, function () { Build_Pagination() });
	});

        };

	Display_Load();
	 $("#events").load("/skins/new/views/pagination_data.php" + url, function() { Build_Pagination() });


 $('#sidebarHistory input').change(function() {

  var allVals = []; //Make the array

  $("#sidebarHistory input:checked").each(function() { //For each checked box
   allVals.push(this.id); //Push the checkbox id into the array
  });

  var x = allVals.length; //Get the number of checked boxes
  var query = "";
  for (i=0;i<x;i++) { //For each checked box
   if (i>0){
    query += "&filter[terms][" + i + "][cnj]=or&filter[terms][" + i + "][attr]=MonitorName&filter[terms][" + i + "][op]==&filter[terms][" + i + "][val]=" + allVals[i];
   } else {
    query += "&filter[terms][" + i + "][attr]=MonitorName&filter[terms][" + i + "][op]==&filter[terms][" + i + "][val]=" + allVals[i];
   }
  };

  $("#inptMonitorName").attr('value', this.id);

  Display_Load();

//  $("#events").load("/skins/new/views/pagination_data.php?page=1&filter[terms][0][attr]=MonitorName&filter[terms][0][op]==&filter[terms][0][val]=" + this.id, function() { Build_Pagination()});
  $("#events").load("/skins/new/views/pagination_data.php?page=1" + query, function() { Build_Pagination()});

 });

});
