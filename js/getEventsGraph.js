var options = {
	 series: {
		lines: { show: false },
		points: { show: true },
		bars: {
			show: true,
			lineWidth: 2,
			fill: true
		}
	}

};
var griddata = [];
var previousItem = null;
var plot = null;
//#content-main-cameras

var monitorid = 0;
jQuery(function () {
	monitorid = $("#frmgraphevent input[name=mid]").val()
	$("#frmgraphevent input[name=sdate], #frmgraphevent input[name=edate]").datepicker( {dateFormat:'yy-mm-dd'});
	plot = jQuery.plot($("#id-console-events-graph"), griddata, options);

	
	$("#id-console-events-graph").bind("plotclick", function (event, pos, item) {
		if (item) {
			if(griddata[0]['customdefined']['details'].type=='monitor') {
			//select an event
				mydata = griddata[0]['customdefined']['eventdetail'][item.dataIndex];
				//page_modal_ajax('index.php?request=eventmain',{eid:mydata[0],eventlist:mydata[0]},mydata[1]+200,mydata[2]+200+130);
				//alert(mydata[0]+' '+mydata[1]+' '+mydata[2]);
				jQuery.fn.colorbox({
					href:'?view=event&eid='+mydata[0],
					iframe:true,
					innerWidth:mydata[1]+200,
					innerHeight:mydata[2]+200+130
				});

				
			} else if(griddata[0]['customdefined']['details'].type=='graph') {
			//select a date range
			
				mydata = griddata[0]['customdefined']['daterange'][item.dataIndex];
				var d1 = new Date();
				var d2 = new Date();
				d1.setTime(mydata[1]*1000);
				d2.setTime(mydata[2]*1000);
				graph_form_set(d1.getFullYear()+'-'+padL(d1.getMonth()+1,2,0)+'-'+padL(d1.getDate(),2,0),
							   d2.getFullYear()+'-'+padL(d2.getMonth()+1,2,0)+'-'+padL(d2.getDate(),2,0),
							   padL(d1.getHours(),2,0)+':00',
							   padL(d2.getHours(),2,0)+':59:59',
							   mydata[0]);
				loadgraph();
			}

		}
	});
	$("#id-console-events-graph").bind("plothover", function (event, pos, item) { loadgraphtooltip(item,griddata[0].customdefined); });
    $("#id-console-events-graph").bind("plotselected", function (event, ranges) { 
		var data = griddata[0].customdefined;
		var xy = data.details.orientation=='xy' ? 'x' : 'y';
		var min = eval('ranges.'+xy+'axis.from.toFixed(1)');
		var max = eval('ranges.'+xy+'axis.to.toFixed(1)');
		loadevents(data,min,max); 
	});

	$("#btngraph").click(function () { loadgraph(); return false; });
	$("#btnbackgraph").click(function () { horizontal_slide_click('-'); return false; });
	$("#btnfwdgraph").click(function () { horizontal_slide_click('+'); return false; });
	$("#btngraphswap").click(function () { 
		$("#frmgraphevent input[name=graphlayout]").val($("#frmgraphevent input[name=graphlayout]").val()=='xy' ? 'yx' : 'xy');
		loadgraph(); return false; 
	});
	
	$("#btnallevents").click(function () { loadevents(griddata[0]['customdefined']); return false;	});
	
	$("#close_btn_graphs")
		.button()
		.click(function() {
			is_graph_mode = 0;
			$('#sidebar').show();
			$('#tabs_events').css({'marginLeft':'14.8461em'});
			
			var selected = $tabs.tabs('option', 'selected'); // => 0	
			i=0;
			$('#tabs_events .ui-tabs-nav a').each(function() {
			if(i==selected) {
				  var id = $(this).attr('href');
					var url = $.data(this, 'href.tabs');
					url = url.replace(/\&is_graph_mode\=(0|1)/g, "&is_graph_mode="+is_graph_mode);
					$tabs.tabs('url', selected, url);
					$tabs.tabs('load', selected);
				 // break;
				 }
				  i++;
			});	
		})
	;


	//reloadWindow.periodical( consoleRefreshTimeout );
	//if ( showVersionPopup ) createPopup( '?view=version', 'zmVersion', 'version' );
	//if ( showDonatePopup ) createPopup( '?view=donate', 'zmDonate', 'donate' );
	

	// load graph
	d1 = new Date();
	d2 = new Date();
	d1.setTime(d2.getTime()-82800000);
	graph_form_set(d1.getFullYear()+'-'+padL(d1.getMonth()+1,2,0)+'-'+padL(d1.getDate(),2,0),
				   d2.getFullYear()+'-'+padL(d2.getMonth()+1,2,0)+'-'+padL(d2.getDate(),2,0),
				   padL(d1.getHours(),2,0)+':00',
				   padL(d2.getHours(),2,0)+':59:59',
				   'event');
	//graph_form_set('2009-12-09','2009-12-09','15:00','15:59:59','event'); 
	loadgraph();

});
  
function graph_form_set(sdate,edate,stime,etime,interval,yaxistype,ystart,yend,graphlayout) {
	if(sdate != undefined) $("#frmgraphevent input[name=sdate]").val(sdate);
	if(edate != undefined) $("#frmgraphevent input[name=edate]").val(edate);
	if(stime != undefined) $("#frmgraphevent select[name=stime]").val(stime);
	if(etime != undefined) $("#frmgraphevent select[name=etime]").val(etime);
	if(interval != undefined) $("#frmgraphevent select[name=interval]").val(interval);
	
	if(yaxistype != undefined) {
		$("#frmgraphevent select[name=yaxistype]").val(yaxistype);
		changeyaxistype(yaxistype);
		if(yaxistype=='');
		else if(yaxistype=='><') {
			$("#frmgraphevent input[name=ystart]").val(ystart);
			$("#frmgraphevent input[name=yend]").val(yend);
		} else $("#frmgraphevent input[name=ystart]").val(ystart);
	}
	if(graphlayout != undefined) $("#frmgraphevent input[name=graphlayout]").val(graphlayout);
	
}
function changeyaxistype(val) {
	if(val == '') document.getElementById('spanyaxiscriteria').innerHTML = '';
	else if(val == '><') document.getElementById('spanyaxiscriteria').innerHTML = '<input name="ystart" type="text" size="2"> and <input name="yend" type="text" size="2">';
	else if(val == '=') document.getElementById('spanyaxiscriteria').innerHTML = '<input name="ystart" type="text" size="2">';
	else if(val == '<') document.getElementById('spanyaxiscriteria').innerHTML = '<input name="ystart" type="text" size="2">';
	else if(val == '>') document.getElementById('spanyaxiscriteria').innerHTML = '<input name="ystart" type="text" size="2">';
}

function horizontal_slide_click(type) {
			
	if(griddata[0]!=undefined) {
		mydata = griddata[0]['customdefined']['details'];
		
		diff = mydata.etime - mydata.stime + 1;

		d1 = new Date();
		d1.setTime(eval(mydata.stime+type+diff)*1000);
		
		d2 = new Date();
		d2.setTime(eval(mydata.etime+type+diff)*1000);
		
		graph_form_set(d1.getFullYear()+'-'+padL(d1.getMonth()+1,2,0)+'-'+padL(d1.getDate(),2,0),
					   d2.getFullYear()+'-'+padL(d2.getMonth()+1,2,0)+'-'+padL(d2.getDate(),2,0),
					   padL(d1.getHours(),2,0)+':00',
					   padL(d2.getHours(),2,0)+':59:59',
					   mydata.interval);
		
		loadgraph();
	}
}

function loadgraph() {
// needed to verify that the monitor is loaded before pulling data
	//if(monitorid==undefined || monitorid==0) setTimeout(function(){loadgraph();}, 500);//monitorid=$('.mymonitor:first').attr('monitorid');
	//else 
	loadgraphhelper();
}

function loadgraphhelper() {
	var p = {
		monitorid: monitorid,
		sdate: $("#frmgraphevent input[name=sdate]").val(),
		edate: $("#frmgraphevent input[name=edate]").val(),
		stime: $("#frmgraphevent select[name=stime]").val(),
		etime: $("#frmgraphevent select[name=etime]").val(),
		interval: $("#frmgraphevent select[name=interval]").val(),
		eventtype: $("#frmgraphevent select[name=eventtype]").val(),
		yaxistype: $("#frmgraphevent select[name=yaxistype]").val(),
		ystart: 0,
		yend: 0,
		graphlayout: $("#frmgraphevent input[name=graphlayout]").val()
	}
	var d1 = new Date(p.sdate.substr(0,4), p.sdate.substr(5,2)*1-1, p.sdate.substr(8,2), p.stime.substr(0,2), 0, 0, 0);
	var d2 = new Date(p.edate.substr(0,4), p.edate.substr(5,2)*1-1, p.edate.substr(8,2), p.etime.substr(0,2), 59, 59, 0);
	p['smktime'] = d1.getTime()/1000;
	p['emktime'] = d2.getTime()/1000;
	if( (p['emktime']-p['smktime']) > 86400) {
//		if(p['sdate']!=p['edate']) {
		//p['stime'] = '00:00';
		//p['stime'] = '23:59:59';
		p['interval'] = '1day';
		graph_form_set(p.sdate,p.edate,p.stime,p.etime,'1day')
	}
	/*
	if( p['yaxistype'] == '><') {
		p['ystart'] = $("#frmgraphevent input[name=ystart]").val()*1;
		p['yend'] = $("#frmgraphevent input[name=yend]").val()*1;
	} else if( p['yaxistype']=='=' || p['yaxistype']=='<' || p['yaxistype']=='>') {
		p['ystart'] = $("#frmgraphevent input[name=ystart]").val()*1;
	}
	*/
 
	//var xman = ''; jQuery.each(p, function(i, val) { xman += i + " : " + val + "\n";  });  alert(xman);

   
	$.ajax({
		url: 'index.php?request=eventgraph',
		type: 'POST',
		dataType: 'script',
		data: p,
		beforeSend: function(x) {
			if(x && x.overrideMimeType) {
				x.overrideMimeType("application/j-son;charset=UTF-8");
			}
		},
		success: function (series) {
			eval('series = '+series);
			griddata = [];
			griddata.push(series['griddata']);
//alert(dump($.extend(options,series['options'])));
//			plot = $.plot($("#id-console-events-graph"), griddata, $.extend(options,series['options']));
			plot = $.plot($("#id-console-events-graph"), griddata,series['options']);
			
			// populate used criteria for graph
			vals = griddata[0]['customdefined']['details'];
			graph_form_set(null,null,null,null,vals.interval,vals.event_operator,vals.event_start,vals.event_end,vals.orientation);
		},
		complete: function(XMLHttpRequest) {/*alert("Complete: "+XMLHttpRequest.status+"\n"+XMLHttpRequest.responseText);*/},
		error:function (xhr, textStatus, errorThrown) {  $("#error").append("Error: "+xhr.status+"\n"+textStatus+"\n"+xhr.responseText);alert("Error: "+xhr.status+"\n"+textStatus+"\n"+xhr.responseText); }
	});
	return false;
}


function loadgraphtooltip(item, data) {
	//show tooltip
	if (item) {
		if (previousItem != item.dataIndex) {
			previousItem = item.dataIndex;
			
			$("#tooltip").remove();
			var x = item.datapoint[0].toFixed(2),
				y = item.datapoint[1].toFixed(2);
			
			$('<div id="tooltip">' + (data.details.orientation=='xy' ? y*1:x*1) + '</div>').css( {
				position: 'absolute',
				display: 'none',
				top: item.pageY-10,
				left: item.pageX + 5,
				border: '1px solid #fdd',
				padding: '2px',
				'background-color': '#fee',
				opacity: 0.80
			}).appendTo("body").fadeIn(30);
		}
	}
	else {
		$("#tooltip").remove();
		previousItem = null;            
	}

}



function loadevents(griddata,rangeFr,rangeTo) {
	if (typeof(rangeFr) == "undefined") rangeFr = griddata.details.axismin; //-1;
	if (typeof(rangeTo) == "undefined") rangeTo = griddata.details.axismax; //-1;
	var mydata = griddata['eventdetail'];
	var myevents = [];
	var maxwidth = 0;
	var maxheight = 0;
	for(i=0,len=mydata.length;i<len; i++) {
		//if(rangeFr==-1 || rangeTo==-1 || (griddata['datapoint'][i][0]>=rangeFr && griddata['datapoint'][i][0]<=rangeTo)) {
		if(griddata['datapoint'][i][0]>=rangeFr && griddata['datapoint'][i][0]<=rangeTo) {
			if ( maxwidth < mydata[i][1] ) maxwidth = mydata[i][1];
			if ( maxheight < mydata[i][2] ) maxheight = mydata[i][2];
			myevents.push(mydata[i][0]);
		}
	}
	// open selected events for playback
	if (myevents.length>0) {
		var p = {eventlist:myevents.join(',')};
		$("#id-console-popup").css( { display: 'block'} ).empty().load('index.php?request=eventmain',p);
		$.fn.colorbox({
			//html:'<p>common</p>',
			inline:true,
			href:"#id-console-popup",
					
			innerWidth:maxwidth+200,
			innerHeight:maxheight+200+130,
			scrolling:false,
			open:true,
			onClosed: function(){ $("#id-console-popup").css( { display: 'none'} ); }
		});
	}
}

function padL(str, len, pad) {
	str = str+'';
	if (typeof(len) == "undefined") { var len = 0; }
	if (typeof(pad) == "undefined") { var pad = ' '; }
	if ((len+1) >= str.length) str = Array(len+1-str.length).join(pad) + str;
	return str;
 
}
