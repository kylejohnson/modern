<?php
require_once("../includes/config.php");require_once("../includes/functions.php");

//require_once('skins/modern/includes/config.php');require_once('skins/modern/includes/functions.php');

# seyi_code start
ini_set( "session.name", "ZMSESSID" );
session_start();
# seyi_code end


if(empty($_REQUEST['width']) || !ctype_digit($_REQUEST['width'])) exit; //need the content width in order to display cams properly
$grid_width = $_REQUEST['width'];
$hpad = 81;
$wpad = 43;

$def_bigscreen1 = array(6,8,13);
$def_bigscreen2 = array(10);

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
	//$contentwidth = ($grid_width/$_REQUEST['number']) - ($grid_width*0.15/$_REQUEST['number']);	
	$contentwidth = ($grid_width/$_REQUEST['number']) - ($wpad+20);	
}
elseif($_REQUEST['type']=='monitor') {
	if(empty($_REQUEST['monitorid']) || !ctype_digit($_REQUEST['monitorid'])) exit;
	$monitorid = $_REQUEST['monitorid'];
	//$contentwidth = ($grid_width) - ($grid_width*0.15);	
	$contentwidth = ($grid_width) - ($wpad+20);	
}
elseif($_REQUEST['type']=='view') {
	$columns = array(1=>1,4=>2,6=>3,8=>4,9=>3,10=>4,13=>4,16=>4);
	if(!isset($columns[$_REQUEST['number']])) exit;
		
	$limit = $_REQUEST['number'];
	//$contentwidth = ($grid_width/$columns[$_REQUEST['number']]) - ($grid_width*0.15/$columns[$_REQUEST['number']]);
	$contentwidth = ($grid_width/$columns[$_REQUEST['number']]) - ($wpad+10);
	if($_REQUEST['number']==6 || $_REQUEST['number']==8 || $_REQUEST['number']==10 || $_REQUEST['number']==13) $var_viewchange = 'true';
		
	$tmp = dbFetchOne( 'SELECT MonitorIds FROM Groups WHERE Name="view-'.$_REQUEST['number'].'"' );
	if(!empty($tmp['MonitorIds'])) $view_monitorids = $tmp['MonitorIds'];
	
	if(!empty($_REQUEST['monitorid'])) {
		if(!ctype_digit($_REQUEST['monitorid'])) exit;
		$first_monitor = $_REQUEST['monitorid'];
	}
	$cookie_zmViewBigScreen = @unserialize($_COOKIE['zmViewBigScreen']);
	if(!empty($first_monitor)) {
		if(in_array($_REQUEST['number'],$def_bigscreen1)) {
			$cookie_zmViewBigScreen[$_REQUEST['number']] = $first_monitor;
		} elseif(in_array($_REQUEST['number'],$def_bigscreen2)) {
			$tmp = !empty($cookie_zmViewBigScreen[$_REQUEST['number']]) ? explode(',',$cookie_zmViewBigScreen[$_REQUEST['number']]) : array();
			if(!in_array($first_monitor,$tmp)) {
				if(count($tmp)==2) array_pop($tmp);
				array_unshift($tmp,$first_monitor);
				$cookie_zmViewBigScreen[$_REQUEST['number']] = implode(',',$tmp);
			}
		}
	}
	$zmViewBigScreen = array();
	if(!empty($cookie_zmViewBigScreen)) {
		setCookie('zmViewBigScreen',serialize($cookie_zmViewBigScreen),time()+3600*24*30);
		$zmViewBigScreen = explode(',',$cookie_zmViewBigScreen[$_REQUEST['number']]);
	}
}


//		  WHERE 1=1 AND Function!="None"
$maxHeight = 0;
$sql = 'SELECT * 
		  FROM Monitors 
		  WHERE 1=1 AND Function!="None"
		  '.(!empty($monitorid) ? 'AND Id='.$monitorid : '').' 
		  '.(!empty($view_monitorids) ? 'AND Id IN ('.$view_monitorids.')' : '').' 
		  ORDER BY Sequence ASC
		  '.(!empty($limit) ? 'LIMIT '.$limit : '');
$monitors = dbFetchAll( $sql );
$index = 0;
$displayMonitors = array();
for ( $i = 0; $i < count($monitors); $i++ ) {
	if ( !visibleMonitor( $monitors[$i]['Id'] ) ) continue;
	
	$tmpheight = $contentwidth * $monitors[$i]['Height'] / $monitors[$i]['Width'];
	if ( $maxHeight < $tmpheight ) $maxHeight = $tmpheight;
	    
	$monitors[$i]['connKey'] = generateConnKey();
	$monitors[$i]['index'] = $index++;

	
	$displayMonitors[$monitors[$i]['Id']] = $monitors[$i];
}

//if(!empty($first_monitor) && isset($displayMonitors[$first_monitor])) {
//	$tmp = $displayMonitors[$first_monitor];
//	unset($displayMonitors[$first_monitor]);
//	array_unshift($displayMonitors,$tmp);
//}
$order_monitors = array();
foreach($zmViewBigScreen as $m) {
	$order_monitors[$m] = $displayMonitors[$m];
	unset($displayMonitors[$m]);
}
if(!empty($order_monitors)) $displayMonitors = $order_monitors + $displayMonitors;








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

<ul id="monitors" class="clearfix">
<?php


foreach( $displayMonitors as $monitor ) {
	$connkey = $monitor['connKey']; // Minor hack
	
	$mycontentwidth = $contentwidth;
	$mymaxheight = $maxHeight;
	$myvar_viewchange = $var_viewchange;
	
	
	
	$acustom =($_REQUEST['type']=='view' 
			&& (	(in_array($_REQUEST['number'],$def_bigscreen1) && !$firsttime)
				||	(in_array($_REQUEST['number'],$def_bigscreen2) && !$firsttime && !$secondtime))
			  )	? 'href="javascript:loadcameras({type:\'view\',number:'.$_REQUEST['number'].',monitorid:'.$monitor['Id'].'},\'changemonitor(0);\');"'
				: 'rel="monitor" href="?view=watch&amp;mid='.$monitor['Id'].'"';
	
	
	if($secondtime) {
		$secondtime = false;
		if($_REQUEST['type']=='view') {
			if($_REQUEST['number']==10) {
				$mycontentwidth *= 2;
				$mymaxheight *= 2;
				
				$mycontentwidth += (2-1)*$wpad;
				$mymaxheight += (2-1)*$hpad;
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
				$mycontentwidth += (2-1)*$wpad;
				$mymaxheight += (2-1)*$hpad;
				$myvar_viewchange = 'false';
			}
			elseif($_REQUEST['type']=='view' && $_REQUEST['number']==8) {
				$mycontentwidth *= 3;
				$mymaxheight *= 3;
				$mycontentwidth += (3-1)*$wpad;
				$mymaxheight += (3-1)*$hpad;
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
			<div id="monitorFrame<?= $monitor['index'] ?>" class="monitorFrame">
				<div id="monitor<?= $monitor['index'] ?>" class="monitor idle">
					<a <?php echo $acustom; ?> title="<?= $monitor['Name']; ?>">
					<?php
						if(!empty($ratioWidth)) outputlivestream($monitor,$mycontentwidth,$mymaxheight);
						else outputlivestream($monitor,$mycontentwidth);
					?>
					</a>
				</div>
			</div>
		</div>
		<div class="monfooter"></div>
	</li>
	<?php
}
?>
</ul>
<script>
var monitors = new Array();
var delay = Math.round( (Math.random()+0.5)*statusRefreshTimeout );
var i = 0;
<?php 
foreach ( $displayMonitors as $monitor ) { 
?>
		monitors[i] = new Monitor( i, <?= $monitor['Id'] ?>, <?= $monitor['connKey'] ?> );
		monitors[i].start( delay );
		i++;
<?php } ?>

</script>

<?php

ob_end_flush();
