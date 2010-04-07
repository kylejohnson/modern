$(document).ready(function(){
var url = location.search
	function Display_Load() {
	    $("#loading").fadeIn(900,0);
		$("#loading").html("<img src='/skins/new/graphics/bigLoader.gif' />");
	}
	function Hide_Load()
	{
		$("#loading").fadeOut('slow');
	};
	$("#pagination li:first").css({'color' : '#FF0084'}).css({'border' : 'none'});
	Display_Load();
	$("#events").load("/skins/new/views/pagination_data.php" + url, Hide_Load());
	$("#pagination li").click(function(){
		Display_Load();
		$("#pagination li")
		.css({'border' : 'solid #dddddd 1px'})
		.css({'color' : '#0063DC'});
		$(this)
		.css({'color' : '#FF0084'})
		.css({'border' : 'none'});
		var pageNum = this.id;
		$("#events").load("/skins/new/views/pagination_data.php?page=" + pageNum, Hide_Load());
	});
});
