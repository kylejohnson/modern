<?php
$MonitorName = $_REQUEST['MonitorName'];
$result = mysql_query("select Id from Monitors where Name='$MonitorName'") or die('Error, selecting monitors failed.');
$row = mysql_fetch_row($result);
$mid = $row[0];
if(empty($mid)) return;


function timeDD($type,$hr) {
	$s = '';
	
	for($i=0; $i<24; $i++) {
		$val = str_pad($i,2,0,STR_PAD_LEFT);
		$ending = $type=='s' ? '00' : '59:59';
		$s .= '<option value="'.$val.':'.$ending.'" '.($i==$hr ? 'SELECTED' : '').'>'.$val.':'.$ending.'</option>';
	}
	return $s;
}
?>
<pre id="error"></pre>
<script type="text/javascript" src="skins/modern/js/getEventsGraph.js"></script>
<div style="background-color:#ffffff;padding:10px;border:2px solid #777777;">
	
	<form id="frmgraphevent" name="frmgraphevent" style="vertical-align:top;">
	<table class="graphform" valign="top">
	<tr><th colspan="2">Graph Form</th></tr>
	<tr><td nowrap><input
				type="text" name="sdate" value="<?=date('Y-m-d')?>" size="13" READONLY><select 
				name="stime" class="short"><?=timeDD('s',date('G'))?></select></td></tr>
	<tr><td nowrap><input
				type="text" name="edate" value="<?=date('Y-m-d')?>" size="13" READONLY><select 
				name="etime" class="short"><?=timeDD('e',date('G'))?></select></td></tr>
	<tr><td><select name="interval">
				<option value="event">Each</option>
				<option value="5min">5 minutes</option>
				<option value="1hr">1 hour</option>
				<option value="1day">1 day</option>
			</select></td></tr>
	<tr><td><select name="eventtype" onchange="loadgraph();">
				<option value="frames">Frames</option>
				<option value="score">Score</option>
				<option value="duration">Length</option>
			</select></td></tr>
	<tr><td><select name="yaxistype" onchange="changeyaxistype(this.value)"><option></option>
			<option value="><">Between</option>
			<option value="=">Equal To</option>
			<option value="<">Less Than</option>
			<option value=">">Greater Than</option>
		</select><br><span id="spanyaxiscriteria"></span></td></tr>
	<tr><td><button id='btngraph' style="width:100%">Graph</button></td></tr>
	</table>
	<input type="hidden" name="graphlayout" value="yx">
	<input type="hidden" name="mid" value="<?php echo $mid; ?>">
	</form>
	
	<div id="id-console-events-graph" style="position:relative;z-index:0;width:100%;height:500px;" 
		title="1 click on yellow circle to drill down and/or play back"></div>
	
	
	<br style="clear:both;">

	<div style="float:left"><button id="btnbackgraph">&laquo;&laquo;back</button></div>
	<div style="float:right"><button id="btnfwdgraph">forward&raquo;&raquo;</button></div>
	<br style="clear:both;">
	<!--<button id="btnallevents">Playback all events in this time window</button>-->
	<br /><button id="close_btn_graphs" style="height:25px;">Close</button>
	
</div>

 <div id="sdljkseilsdkowiesd" style="width:655px;height:300px;"></div>
<div id="id-console-popup" style="display:none;"></div>
