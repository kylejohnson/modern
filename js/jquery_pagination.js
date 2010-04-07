$(document).ready(function(){

var url = location.search;
	function Display_Load() {
	 $("#loading").fadeIn(900,0);
         $("#loading").html("<img src='/skins/new/graphics/bigLoader.gif' />");
	};

	function Hide_Load() {
         $("#loading").fadeOut('slow');
	};

	function Build_Pagination() {
         Hide_Load();

	 $("#pagination li").click(function() {
           Display_Load();
	  var pageNum = this.id;
          var monitorName = $("#inptMonitorName").attr("value");
	  $("#events").load("/skins/new/views/pagination_data.php?filter[terms][0][attr]=MonitorName&filter[terms][0][op]==&filter[terms][0][val]=" + monitorName + "&page=" + pageNum, function () { Build_Pagination() });
	});

        };

	Display_Load();
	 $("#events").load("/skins/new/views/pagination_data.php" + url, function() { Build_Pagination() });


 $('#sidebarHistory input').change(function() {
  $("#inptMonitorName").attr('value', this.id);
  Display_Load();
  $("#events").load("/skins/new/views/pagination_data.php?page=1&filter[terms][0][attr]=MonitorName&filter[terms][0][op]==&filter[terms][0][val]=" + this.id, function() { Build_Pagination()});
 });
});
