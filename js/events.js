$(document).ready(function() {
	tab = $("#inptTab").val();

	$tabs = $("#tabs_events")
		.tabs({
			select: function(event, ui) { 
				$(".thumb").remove(); 
				page = 1; 
				var url = $.data(ui.tab, 'load.tabs');
				url = url.replace(/\&is_graph_mode\=(0|1)/g, "&is_graph_mode="+is_graph_mode)
				$.data(ui.tab, 'load.tabs',url);
				return true;
			},
			load: function(event, ui) { $(".event").colorbox({rel:'event',iframe:true, innerWidth:800, innerHeight:500}); },
			add: function(event, ui) {
				var name = "#" + ui.panel.id;
				if (ui.tab.firstChild.textContent == tab) { $tabs.tabs('select', '#' + ui.panel.id); }
			},
			show: function(event, ui) { $(".ui-tabs-hide").empty(); } // empty hidden tabs so we dont have id conflicts

		});

	page = 1;
	add_monitors();
	//setup_is();


	// PAGE STUFF //
	$("#btnSubmit").button();
	$("#btnDelete")
		.button()
		.click(function(){
			var answer = confirm("Are you sure you want to delete these events?");
			if (answer){
				$("#tabs_events input:checked").each(function (i){
					var eid = $(this).attr("value");
					$.post("skins/modern/includes/deleteEvent.php?eid="+eid);
				});
			}
		})
	;
	$("#btnSelectall").click(function() { $("#tabs_events input:checkbox").attr('checked', this.checked); });
	$("#btn_graphs")
		.button()
		.click(function() {
			if(is_graph_mode) {
				is_graph_mode = 0;
				$('#sidebar').show();
				$('#tabs_events').css({'marginLeft':'14.8461em'});
			}
			else {
				is_graph_mode = 1;
				$('#sidebar').hide();
				$('#tabs_events').css({'marginLeft':0});
			}
			
			var selected = $tabs.tabs('option', 'selected'); // => 0	
			i=0;
			$('#tabs_events .ui-tabs-nav a').each(function() {
				if(i==selected) {
					var id = $(this).attr('href');
					var url = $.data(this, 'href.tabs');
					url = url.replace(/\&is_graph_mode\=(0|1)/g, "&is_graph_mode="+is_graph_mode);
					$tabs.tabs('url', selected, url);
					$tabs.tabs('load', selected);
					return false;
				}
				i++;
			});	
		})
	;
	$("#btn_advanced")
		.button()
		.click(function() {
			$.fn.colorbox({
				inline:true,
				href:"index.php?view=events-advanced&page=1&filter[terms][0][attr]=MonitorId&filter[terms][0][op]=%3D",
						
				iframe:true,
				innerWidth:600,
				innerHeight:300,
				open:true
			});
		})
	;
	
	
	$("#btnExportall")
		.button()
		.click(function() { 
			var eids = ''
			$('#tabs_events input:checkbox').each(function () { if(this.checked) eids += 'eids[]='+$(this).val()+'&'; });
			if(eids!='') {
				// open image overlay
				$('#screen').css({'display': 'block', opacity: 0.7, 'width':$(document).width(),'height':$(document).height()});
				$('body').css({'overflow':'hidden'});
				$("#spinner").css({'display': 'block'}).html('<img src="skins/modern/graphics/spinner.gif" alt="spinner" />'); // Display the spinner

				$.post("skins/modern/includes/export_functions.php?"+eids, function(data){ // Create the video file
					window.open('skins/modern/includes/download.php?file='+encodeURIComponent(data));
					
					// close image overlay
					$('#screen').css('display', 'none');
					$('body').css({'overflow':'auto'});
					$("#spinner").css({'display':'none'}).html('');
				});
			}
		})
	;
	$("#selSortBy")
		.change(function() {
			var selected = $tabs.tabs('option', 'selected'); // => 0	
			var selection = $(this).val();
			i=0;
			$('#tabs_events .ui-tabs-nav a').each(function() {
				if(i==selected) {
					var id = $(this).attr('href');
					var url = $.data(this, 'href.tabs');
					if(url.indexOf('&order_by=')==-1) url = url+'&order_by='+selection;
					else url = url.replace(/\&order_by\=[^&]*\&?$/g, "&order_by="+selection);
					$tabs.tabs('url', selected, url);
					$tabs.tabs('load', selected);
					return false;
				}
				i++;
			});	
		})
	;
	// PAGE STUFF //
function dumpProps4(obj, parent) {
	var msg = "";
	for (var i in obj) {
		if (parent) { msg = msg + parent + "." + i + "-" + obj[i]; } 
		else { msg = msg + i + "-" + obj[i] + "<br>"; }
		i++;
	}
	return msg;
}

	//FUNCTIONS//
	function add_monitors(){
		$.post("skins/modern/includes/getMonitors.php", function(data){
			var monitors = data.split(","); // Put monitors into array
			monitors.pop(); // Pop off last monitor (it is blank)
			var x = monitors.length; // Number of monitors
			for (var i=0;i<x;i++){
				var monitor = monitors[i];
				$tabs.tabs('add', "skins/modern/includes/getEvents.php?MonitorName="+monitor+"&gridwidth="+$("#tabs_events").width()+"&is_graph_mode=0", monitor);
			}
		});
	};

	function setup_is(){
		$(window).scroll(function(){
			if ($(window).scrollTop() == ($(document).height() - $(window).height())){
				FetchMore();
			}
		});
	}

	function FetchMore(){
		display_spinner();
		var MonitorName = $('li.ui-state-active a span').text(); // Currently selected monitor
		$.post("skins/modern/includes/getEvents.php?is_graph_mode=0&MonitorName="+MonitorName+"&gridwidth="+$("#tabs_events").width()+"&page="+page, function(data){ // Get more events
			if (data != "") {
				var ui_tab = $("li.ui-state-active a").attr("href");
				$(".ui-tabs-panel .clearfix").remove(); // Remove the clearfix div so events display correctly
				$(ui_tab).append(data); // Append next page of events
			}
			$(".event").colorbox({rel:'event',iframe:true, innerWidth:800, innerHeight:500});
		});
		page = page + 1;
		hide_spinner();
	}
	//FUNCTIONS//
});

is_graph_mode = 0;


function display_spinner(){
	var spinner = '<img class="spinner" src="skins/modern/graphics/spinner.gif" alt="Loading..." />';
	var ui_tab = $("li.ui-state-active a").attr("href");
	$(ui_tab).append(spinner);
};

function hide_spinner(){$(".spinner").remove();};

function pagination(page,order_by){
	display_spinner();
	var MonitorName = $('li.ui-state-active a span').text(); // Currently selected monitor
	$.post("skins/modern/includes/getEvents.php?is_graph_mode=0&MonitorName="+MonitorName+"&gridwidth="+$("#tabs_events").width()+"&page="+page+"&order_by="+order_by, function(data){ // Get more events
		if (data != "") {
			var ui_tab = $("li.ui-state-active a").attr("href");
			$(".ui-tabs-panel .clearfix").remove(); // Remove the clearfix div so events display correctly
			$(ui_tab).empty().append(data); // Append next page of events
		}
		$(".event").colorbox({rel:'event',iframe:true, innerWidth:800, innerHeight:500});
	});
	page = page + 1;
	hide_spinner();
}
function pagination2(qstring,page){
	display_spinner();
	var MonitorName = $('li.ui-state-active a span').text(); // Currently selected monitor
	$.post("skins/modern/views/pagination_data.php?"+qstring+"&gridwidth="+$("#tabs_events").width()+"&page="+page, function(data){ // Get more events
		if (data != "") {
			var ui_tab = $("li.ui-state-active a").attr("href");
			$(".ui-tabs-panel .clearfix").remove(); // Remove the clearfix div so events display correctly
			$(ui_tab).empty().append(data); // Append next page of events
		}
		$(".event").colorbox({rel:'event',iframe:true, innerWidth:800, innerHeight:500});
	});
	page = page + 1;
	hide_spinner();
}

function page_modal_ajax(url,params,w,h) {
	$("#id-console-popup").css( { display: 'block'} ).empty().load(url,params);

	$.fn.colorbox({
		inline:true,
		href:"#id-console-popup",
				
		innerWidth:w,
		innerHeight:h,
		scrolling:false,
		open:true,
		onClosed: function(){ $("#id-console-popup").css( { display: 'none'} ); }
	});

}

