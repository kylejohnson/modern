<?php
//require_once("../includes/config.php");
//require_once("../includes/functions.php");

require_once('skins/modern/includes/config.php');
require_once('skins/modern/includes/functions.php');

# seyi_code start
ini_set( "session.name", "ZMSESSID" );
session_start();
# seyi_code end


if(empty($_REQUEST['width']) || !ctype_digit($_REQUEST['width'])) exit; //need the content width in order to display cams properly
$grid_width = $_REQUEST['width'];



// default
if(empty($_REQUEST['type'])) {
	$_REQUEST['type'] = 'columns';
	$_REQUEST['number'] = 3;
}

$types = array('columns'=>1,'monitor'=>1,'view'=>1);
if(!isset($types[$_REQUEST['type']])) exit;
$types = array('-'=>1,'4:3'=>1,'5:4'=>1,'11:9'=>1,'19:16'=>1);
//if(!isset($types[$_REQUEST['aspectratio']])) exit;
@$_REQUEST['aspectratio'] = $_COOKIE['zmAspectRatio'];
if(!isset($types[$_REQUEST['aspectratio']])) $_REQUEST['aspectratio'] = '4:3';
elseif($_REQUEST['aspectratio']=='-') $_REQUEST['aspectratio'] = '';

$contentwidth = '';
$monitorid = '';
$view_monitorids = '';
$var_viewchange = 'false';
$first_monitor = '';
$limit = 0;
if($_REQUEST['type']=='columns') {
	$columns = array(2=>1,3=>1,4=>1);
	if(!isset($columns[$_REQUEST['number']])) exit;
	$contentwidth = ($grid_width/$_REQUEST['number']) - ($grid_width*0.15/$_REQUEST['number']);	
}
elseif($_REQUEST['type']=='monitor') {
	if(empty($_REQUEST['monitorid']) || !ctype_digit($_REQUEST['monitorid'])) exit;
	$monitorid = $_REQUEST['monitorid'];
	$contentwidth = ($grid_width) - ($grid_width*0.15);	
}
elseif($_REQUEST['type']=='view') {
	$columns = array(1=>1,4=>2,6=>3,8=>4,9=>3,10=>4,13=>4,16=>4);
	if(!isset($columns[$_REQUEST['number']])) exit;
	$limit = $_REQUEST['number'];
	$contentwidth = ($grid_width/$columns[$_REQUEST['number']]) - ($grid_width*0.15/$columns[$_REQUEST['number']]);
	if($_REQUEST['number']==6 || $_REQUEST['number']==8 || $_REQUEST['number']==10 || $_REQUEST['number']==13) $var_viewchange = 'true';
		
	$tmp = dbFetchOne( 'SELECT MonitorIds FROM Groups WHERE Name="view-'.$_REQUEST['number'].'"' );
	if(!empty($tmp['MonitorIds'])) $view_monitorids = $tmp['MonitorIds'];
	
	if(!empty($_REQUEST['monitorid'])) {
		if(!ctype_digit($_REQUEST['monitorid'])) exit;
		$first_monitor = $_REQUEST['monitorid'];
	}
	
}





$maxHeight = 0;
$sql = 'SELECT * 
		  FROM Monitors 
		  WHERE 1=1 
		  '.(!empty($monitorid) ? 'AND Id='.$monitorid : '').' 
		  '.(!empty($view_monitorids) ? 'AND Id IN ('.$view_monitorids.')' : '').' 
		  ORDER BY Sequence ASC
		  '.(!empty($limit) ? 'LIMIT '.$limit : '');
$monitors = dbFetchAll( $sql );
$displayMonitors = array();
for ( $i = 0; $i < count($monitors); $i++ ) {
	if ( !visibleMonitor( $monitors[$i]['Id'] ) ) continue;
	
	$tmpheight = $contentwidth * $monitors[$i]['Height'] / $monitors[$i]['Width'];
	if ( $maxHeight < $tmpheight ) $maxHeight = $tmpheight;
	    
	$monitors[$i]['connKey'] = generateConnKey();

	
	$displayMonitors[$monitors[$i]['Id']] = $monitors[$i];
}

if(!empty($first_monitor) && isset($displayMonitors[$first_monitor])) {
	$tmp = $displayMonitors[$first_monitor];
	unset($displayMonitors[$first_monitor]);
	array_unshift($displayMonitors,$tmp);
}










$firsttime = true;
$secondtime = false;

$ratioWidth = $ratioHeight = 0;
if(!empty($_REQUEST['aspectratio'])) {
	list($ratioWidth,$ratioHeight) = explode(':',$_REQUEST['aspectratio']);
	$maxHeight = $contentwidth/$ratioWidth*$ratioHeight;
}

$protocol = isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == 'on' ? 'https' : 'http';
define( "ZM_BASE_URL", $protocol.'://'.$_SERVER['HTTP_HOST'] );

ob_start();	
?>
<script type="text/javascript" src="<?= dirname(ZM_BASE_URL.$_SERVER['PHP_SELF']).'/skins/modern/js/jquery.ajaxq-0.0.1.js' ?>"></script>

<ul id="monitors" class="clearfix">
<?php


foreach( $displayMonitors as $monitor ) {
	$mycontentwidth = $contentwidth;
	$mymaxheight = $maxHeight;
	$myvar_viewchange = $var_viewchange;
	if($secondtime) {
		$secondtime = false;
		if($_REQUEST['type']=='view') {
			if($_REQUEST['number']==10) {
				$mycontentwidth *= 2;
				$mymaxheight *= 2;
				$myvar_viewchange = 'false';
			}
		}
	}
	if($firsttime) {
		$firsttime=false;
		if($_REQUEST['type']=='view') {
			if($_REQUEST['number']==6 || $_REQUEST['number']==10 || $_REQUEST['number']==13) {
				$mycontentwidth *= 2;
				$mymaxheight *= 2;
				$myvar_viewchange = 'false';
			}
			elseif($_REQUEST['type']=='view' && $_REQUEST['number']==8) {
				$mycontentwidth *= 3;
				$mymaxheight *= 3;
				$myvar_viewchange = 'false';
			}
			if($_REQUEST['number']==10) $secondtime = true;
		}
	}
	$mycontentwidth = (int)$mycontentwidth;
	$mymaxheight= (int)$mymaxheight;

	/*$pad = 4+8;
	$extra_css = 'noborder'; //!empty($monitor['TrackMotion']) ? 'redborder' : 'noborder';
	echo '<div class="mymonitor-container" title="1 click to show events on the time graph / 2 clicks to show enlarged picture of monitor" style="height:'.($mymaxheight+$pad).'px;"><div id="mymonitor'.$monitor['Id'].'" monitorid="'.$monitor['Id'].'" onclick="changemonitor('.$monitor['Id'].',false,'.$myvar_viewchange.');" class="mymonitor" ondblclick="loadmonitor('.$monitor['Id'].','.$monitor['Width'].','.$monitor['Height'].');"><div class="mymonitor-name">'.$monitor['Name'].'</div><div class="in_motion '.$extra_css.'">';
	
	if(!empty($ratioWidth)) outputlivestream($monitor,$mycontentwidth,$mymaxheight);
	else outputlivestream($monitor,$mycontentwidth);
	
	//if(!empty($_COOKIE['zmCameraMotionDetection'])) echo '<script>setInterval( function() { statusCmdQuery('.$monitor['Id'].'); }, 1000 ); </script>';

	echo '</div></div></div>';*/
	
	$display_name = strlen($monitor['Name'])>10 ? substr($monitor['Name'],0,10).'...' : $monitor['Name'];
	$display_name = $monitor['Name'];
	?>
	<li id="monitor_<?php echo $monitor['Id'] ?>" style="float:left;width:<?php echo ($mycontentwidth+20) ?>px;height:<?php echo ($mymaxheight+50); ?>px;">
		<div class="mon_header">
			<div class="nameleft" style=" width:<?php echo ($mycontentwidth-50) ?>px;"><h3 style="display:inline;"><?php echo $display_name ?></h3></div>
			<div class="right" style="width:60px;">
				<div class="spinner"></div>
				<div class="minimize"><img src="skins/modern/graphics/minimize.png" style="width:15px;" alt="minimize" /></div>
				<div class="maximize" url="?view=watch&amp;mid=<?= $monitor['Id']; ?>"><img src="skins/modern/graphics/maximize.png" style="width:15px;" alt="maximize" /></div>


			</div>
			<br style="clear:both;" />
		</div>
		<div class="mon">
			<a rel="monitor" href="?view=watch&amp;mid=<?= $monitor['Id']; ?>" title="<?= $monitor['Name']; ?>">
			<?php
				if(!empty($ratioWidth)) outputlivestream($monitor,$mycontentwidth,$mymaxheight);
				else outputlivestream($monitor,$mycontentwidth);
			?>
			</a>
		</div>
		<div class="monfooter"></div>
	</li>
	<?php
}
?>
</ul>
<script>
var thisUrl = "<?= ZM_BASE_URL.$_SERVER['PHP_SELF'] ?>";
var AJAX_TIMEOUT = <?= ZM_WEB_AJAX_TIMEOUT ?>;
var STATE_IDLE = <?= STATE_IDLE ?>;
var STATE_PREALARM = <?= STATE_PREALARM ?>;
var STATE_ALARM = <?= STATE_ALARM ?>;
var STATE_ALERT = <?= STATE_ALERT ?>;
var STATE_TAPE = <?= STATE_TAPE ?>;
var statusRefreshTimeout = <?= 1000*ZM_WEB_REFRESH_STATUS ?>;
var monitorData = new Array();
<?php foreach ( $displayMonitors as $monitor ) { ?>
	monitorData[monitorData.length] = { 'id': <?= $monitor['Id'] ?>, 'connKey': <?= $monitor['connKey'] ?> };
<?php } ?>

function initPage() {
	for ( var i = 0; i < monitorData.length; i++ ) {
		monitors[i] = new Monitor( i, monitorData[i].id, monitorData[i].connKey );
alert(i+'---');
		var delay = Math.round( (Math.random()+0.5)*statusRefreshTimeout );
		//monitors[i].start( delay );
	}
}

// Kick everything off
//window.addEvent( 'domready', initPage );


function Monitor( index, id, connKey ) {
	this.index = index;
	this.id = id;
	this.connKey = connKey;
	this.status = null;
	this.alarmState = STATE_IDLE;
	this.lastAlarmState = STATE_IDLE;
	this.streamCmdParms = "view=request&request=stream&connkey="+this.connKey;
	this.streamCmdTimer = null;

	this.start = function( delay ) {
		//this.streamCmdTimer = this.streamCmdQuery.delay( delay, this );
		this.streamCmdTimer = setTimeout ( function() { this.streamCmdQuery(this); }, delay );

    }
	
	this.setStateClass = function( element, stateClass ) {
		if ( !element.hasClass( stateClass ) ) {
			if ( stateClass != 'alarm' ) element.removeClass( 'alarm' );
			if ( stateClass != 'alert' ) element.removeClass( 'alert' );
			if ( stateClass != 'idle' ) element.removeClass( 'idle' );
			element.addClass( stateClass );
		}
	}

	
	this.getStreamCmdResponse = function( respObj, respText ) {
		if ( this.streamCmdTimer ) this.streamCmdTimer = $clear( this.streamCmdTimer );

		if ( respObj.result == 'Ok' ) {
			this.status = respObj.status;
			this.alarmState = this.status.state;

			var stateClass = "";
			if ( this.alarmState == STATE_ALARM ) stateClass = "alarm";
			else if ( this.alarmState == STATE_ALERT ) stateClass = "alert";
			else stateClass = "idle";

			this.setStateClass( $('#monitor'+this.index), stateClass );

			//Stream could be an applet so can't use moo tools
			var stream = document.getElementById( "liveStream"+this.id );
            stream.className = stateClass;

			var isAlarmed = ( this.alarmState == STATE_ALARM || this.alarmState == STATE_ALERT );
			var wasAlarmed = ( this.lastAlarmState == STATE_ALARM || this.lastAlarmState == STATE_ALERT );

			var newAlarm = ( isAlarmed && !wasAlarmed );
			var oldAlarm = ( !isAlarmed && wasAlarmed );

			if ( newAlarm ) {
				if ( false && SOUND_ON_ALARM ) {
					// Enable the alarm sound
					$('#alarmSound').removeClass( 'hidden' );
				}
				if ( POPUP_ON_ALARM ) { windowToFront(); }
			}
			if ( false && SOUND_ON_ALARM ) {
				if ( oldAlarm ) {
					// Disable alarm sound
					$('#alarmSound').addClass( 'hidden' );
				}
			}
		}
		else {
			console.error( respObj.message );
		}
		var streamCmdTimeout = statusRefreshTimeout;
		if ( this.alarmState == STATE_ALARM || this.alarmState == STATE_ALERT ) streamCmdTimeout = streamCmdTimeout/5;
		this.streamCmdTimer = this.streamCmdQuery.delay( streamCmdTimeout, this );
		this.lastAlarmState = this.alarmState;
    }

    this.streamCmdQuery = function( resent ) {
        //if ( resent )
            //console.log( this.connKey+": Resending" );
        //this.streamCmdReq.cancel();
		$.ajax({
  url: thisUrl,
  method: 'post',
  context: document.body,
  success: function(){
    this.getStreamCmdResponse( this );
  }
});
        //this.streamCmdReq.send( this.streamCmdParms+"&command="+CMD_QUERY );
    }
	
	this.streamCmdReq = new Request.JSON( { url: thisUrl, method: 'post', timeout: AJAX_TIMEOUT, onSuccess: this.getStreamCmdResponse.bind( this ), onTimeout: this.streamCmdQuery.bind( this, true ), link: 'cancel' } );
	//this.streamCmdReq = new Request.JSON( { url: thisUrl, method: 'post', timeout: AJAX_TIMEOUT, onSuccess: this.getStreamCmdResponse.bind( this ), onTimeout: this.streamCmdQuery.bind( this, true ), link: 'cancel' } );

	// requestQueue.addRequest( "cmdReq"+this.id, this.streamCmdReq );
}
initPage();
</script>

<?php

ob_end_flush();
