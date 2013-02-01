<?php

if(empty($_POST['eventlist'])) exit; //need the content width in order to display cams properly
$eventlist = explode(',',$_POST['eventlist']);

foreach($eventlist as $k=>$val)
	if(!ctype_digit($val)) unset($eventlist[$k]);
	else $eventlist[$k] = trim($val);
if(empty($_POST['eventlist'])) exit; //need the content width in order to display cams properly

$sql = "SELECT E.Id,E.Name,E.Cause,E.Notes,E.StartTime,date_format( E.StartTime, '".MYSQL_FMT_DATETIME_SHORT."' ) AS StartTimeShort,
			 E.EndTime,E.Width,E.Height,E.Length,E.Frames,E.AlarmFrames,E.TotScore,E.AvgScore,E.MaxScore,E.Archived,
			 M.DefaultRate,M.DefaultScale
		 FROM Events E
		INNER JOIN Monitors M ON M.Id=E.MonitorId
		WHERE E.Id IN (".implode(',',$eventlist).")
		ORDER BY E.Id";
$eventdata = array();
$archived = false;
$unarchived = false;
foreach( dbFetchAll( $sql ) as $sqlData ) {
	$eventdata[$sqlData['Id']] = $sqlData;
    if ( $sqlData['Archived'] ) $archived = true;
    else $unarchived = true;
}
$eventlist = implode(',',array_keys($eventdata));
if(isset($_POST['eid']) && isset($eventdata[$_POST['eid']])) $currentevent = $eventdata[$_POST['eid']];

$event = !empty($currentevent) ? $currentevent : current($eventdata);
$rate = isset($_POST['rate']) ? validInt($_POST['rate']) : reScale( RATE_BASE, $event['DefaultRate'], ZM_WEB_DEFAULT_RATE );
$scale = isset( $_POST['scale']) ? validInt($_POST['scale']) : reScale( SCALE_BASE, $event['DefaultScale'], ZM_WEB_DEFAULT_SCALE );



/*
$replayModes = array(
    'single' => $SLANG['ReplaySingle'],
    'all' => $SLANG['ReplayAll'],
    'gapless' => $SLANG['ReplayGapless'],
);
$replayMode = isset( $_POST['replayMode']) ? validHtmlStr($_POST['replayMode']) : array_shift( array_keys( $replayModes ) );
*/
$replayMode = !empty($currentevent) ? 'single' : 'gapless';

$streamMode = isset( $_POST['streamMode']) ? validHtmlStr($_POST['streamMode']) : (canStream()?'stream':'stills');

$panelSections = 40;
$panelSectionWidth = (int)ceil(reScale($event['Width'],$scale)/$panelSections);
$panelWidth = ($panelSections*$panelSectionWidth-1);

$connkey = generateConnKey();

?>
<link rel="stylesheet" href="<?=getSkinFile( 'ajax/css/eventmain.css' );?>" type="text/css"/>
<script type="text/javascript"><?php require_once( getSkinFile('ajax/js/eventmain.js.php') ); ?></script>
<script type="text/javascript" src="<?=getSkinFile('ajax/js/eventmain.js')?>"></script>
  

<div class="menuBar1">
	<div>
		<label for="scale"><?= $SLANG['Scale'] ?></label>
		<?= buildSelect( "scale", $scales, "changeScale();" ); ?>
	</div>
	<div><a href="javascript:reloadme();">Reload All Events</a></div>
</div>

<div class="datasingleevent">
<div class="dataBar">
	<table class="dataTable major" cellspacing="0">
	<tr>
		<td><span id="eventdataId" title="<?= $SLANG['Id'] ?>"><?= $event['Id'] ?></span></td>
		<td><span id="eventdataCause" title="<?= $event['Notes']?validHtmlStr($event['Notes']):$SLANG['AttrCause'] ?>"><?= validHtmlStr($event['Cause']) ?></span></td>
		<td><span id="eventdataTime" title="<?= $SLANG['Time'] ?>"><?= strftime( STRF_FMT_DATETIME_SHORT, strtotime($event['StartTime'] ) ) ?></span></td>
		<td><span id="eventdataDuration" title="<?= $SLANG['Duration'] ?>"><?= $event['Length'] ?></span>s</td>
		<td><span id="eventdataFrames" title="<?= $SLANG['AttrFrames']."/".$SLANG['AttrAlarmFrames'] ?>"><?= $event['Frames'] ?>/<?= $event['AlarmFrames'] ?></span></td>
		<td><span id="eventdataScore" title="<?= $SLANG['AttrTotalScore']."/".$SLANG['AttrAvgScore']."/".$SLANG['AttrMaxScore'] ?>"><?= $event['TotScore'] ?>/<?= $event['AvgScore'] ?>/<?= $event['MaxScore'] ?></span></td>
	</tr>
	</table>
</div>
<?php if(!empty($currentevent)) { ?>
<div class="dataBar2">
	<div class="nameControl"><input type="text" id="eventName" name="eventName" value="<?= validHtmlStr($currentevent['Name']) ?>" size="16"/><input type="button" value="<?= $SLANG['Rename'] ?>" onclick="renameEvent()"<?php if ( !canEdit( 'Events' ) ) { ?> disabled="disabled"<?php } ?>/></div>

	<div><a href="javascript:showStream()"  class="linkstreamEvent selectedlink"><?= $SLANG['Stream'] ?></a></div>
	<div ><a href="javascript:showEventFrames()" class="linkframesEvent"><?= $SLANG['Frames'] ?></a></div>
	<div><a href="javascript:showStills()" class="linkstillsEvent"><?= $SLANG['Stills'] ?></a></div>
	<?php if ( ZM_OPT_FFMPEG ) { ?>
	<div><a href="javascript:showVideo()" class="linkvideoEvent"><?= $SLANG['Video'] ?></a></div>
	<?php } ?>
	
	<!--<div class="framesEvent"><a href="javascript:showEventFrames()"><?= $SLANG['Frames'] ?></a></div>
	<div class="streamEvent <?=$streamMode == 'stream' ? 'hidden' : ''?>"><a href="javascript:showStream()"><?= $SLANG['Stream'] ?></a></div>
	<div class="stillsEvent <?=$streamMode == 'still' ? 'hidden' : ''?>"><a href="javascript:showStills()"><?= $SLANG['Stills'] ?></a></div>
	<?php if ( ZM_OPT_FFMPEG ) { ?>
	<div class="videoEvent"><a href="javascript:videoEvent()"><?= $SLANG['Video'] ?></a></div>
	<?php } ?>-->
</div>
<?php } ?>
</div>

<div class="eventStream">
	<div class="imageFeed">
<?php
if ( ZM_WEB_STREAM_METHOD == 'mpeg' && ZM_MPEG_LIVE_FORMAT ) {
	$streamSrc = getStreamSrc( array( "source=event", 
										"mode=mpeg", 
										(!empty($currentevent) ? "event=".$currentevent['Id'] : "eventlist=".$eventlist), 
										"frame=1", 
										"scale=".$scale, 
										"rate=".$rate, 
										"bitrate=".ZM_WEB_VIDEO_BITRATE,
										"maxfps=".ZM_WEB_VIDEO_MAXFPS, 
										"format=".ZM_MPEG_REPLAY_FORMAT, 
										"replay=".$replayMode ) );
	outputVideoStream( "evtStream", $streamSrc, reScale( $event['Width'], $scale ), reScale( $event['Height'], $scale ), ZM_MPEG_LIVE_FORMAT );
} else {
	$streamSrc = getStreamSrc( array( "source=event", 
										"mode=jpeg", 
										(!empty($currentevent) ? "event=".$currentevent['Id'] : "eventlist=".$eventlist), 
										"frame=1", 
										"scale=".$scale, 
										"rate=".$rate, 
										"maxfps=".ZM_WEB_VIDEO_MAXFPS, 
										"replay=".$replayMode) );
//echo $streamSrc;
	if ( canStreamNative() ) outputImageStream( "evtStream", $streamSrc, reScale( $event['Width'], $scale ), reScale( $event['Height'], $scale ), validHtmlStr($event['Name']) );
	else outputHelperStream( "evtStream", $streamSrc, reScale( $event['Width'], $scale ), reScale( $event['Height'], $scale ) );
}
?>
	</div>
	<p class="dvrControls">
		
		<button id="prevBtn" title="<?= $SLANG['Prev'] ?>" class="inactive" onclick="streamPrev( true )">&lt;+</button>
		<button id="eventfastRevBtn" title="<?= $SLANG['Rewind'] ?>" class="inactive" disabled="disabled" onclick="streamFastRev( true )">&lt;&lt;</button>
		<button id="eventslowRevBtn" title="<?= $SLANG['StepBack'] ?>" class="unavail" disabled="disabled" onclick="streamSlowRev( true )">&lt;</button>
		<button id="eventpauseBtn" title="<?= $SLANG['Pause'] ?>" class="inactive" onclick="streamPause( true )">||</button>
		<button id="eventplayBtn" title="<?= $SLANG['Play'] ?>" class="active" disabled="disabled" onclick="streamPlay( true )">|></button>
		<button id="eventslowFwdBtn" title="<?= $SLANG['StepForward'] ?>" class="unavail" disabled="disabled" onclick="streamSlowFwd( true )">&gt;</button>
		<button id="eventfastFwdBtn" title="<?= $SLANG['FastForward'] ?>" class="inactive" disabled="disabled" onclick="streamFastFwd( true )">&gt;&gt;</button>
		<button id="eventzoomOutBtn" title="<?= $SLANG['ZoomOut'] ?>" class="avail" onclick="streamZoomOut()">&ndash;</button>
		<button id="nextBtn" title="<?= $SLANG['Next'] ?>" class="inactive" onclick="streamNext( true )">+&gt;</button>
	</p>
	<div class="replayStatus">
		<span class="mode">Mode: <span class="modeValue">&nbsp;</span></span>
		<span class="rate">Rate: <span class="rateValue"></span>x</span>
		<span class="progress">Progress: <span class="progressValue"></span>s</span>
		<span class="zoom">Zoom: <span id="zoomValue"></span>x</span>
	</div>
	<div id="progressBar" class="invisible">
		<?php for ( $i = 0; $i < $panelSections; $i++ ) { ?>
		<div class="progressBox" id="progressBox<?= $i ?>" title=""></div>
		<?php } ?>
	</div>
</div>


<div class="eventStills hidden">
	<div class="eventThumbsPanel"><div id="eventThumbs"></div></div>
	<div id="eventImagePanel" class="hidden">
		<div class="eventImageFrame">
			<img id="eventImage" src="graphics/transparent.gif" alt=""/>
			<div class="eventImageBar">
				<div class="eventImageClose"><input type="button" value="<?= $SLANG['Close'] ?>" onclick="hideEventImage()"/></div>
				<div class="eventImageStats hidden"><input type="button" value="<?= $SLANG['Stats'] ?>" onclick="showFrameStats()"/></div>
				<div class="eventImageData">Frame <span id="eventImageNo"></span></div>
			</div>
		</div>
	</div>
	<div class="eventImageNav">
		<div class="eventImageButtons">
			<div class="prevButtonsPanel">
				<input class="prevEventBtn" type="button" value="&lt;E" onclick="prevEvent()" disabled="disabled"/>
				<input class="prevThumbsBtn" type="button" value="&lt;&lt;" onclick="prevThumbs()" disabled="disabled"/>
				<input class="prevImageBtn" type="button" value="&lt;" onclick="prevImage()" disabled="disabled"/>
				<input class="nextImageBtn" type="button" value="&gt;" onclick="nextImage()" disabled="disabled"/>
				<input class="nextThumbsBtn" type="button" value="&gt;&gt;" onclick="nextThumbs()" disabled="disabled"/>
				<input class="nextEventBtn" type="button" value="E&gt;" onclick="nextEvent()" disabled="disabled"/>
			</div>
		</div>
		<div class="thumbsSliderPanel"><div id="thumbsSlider"><div id="thumbsKnob"></div></div></div>
	</div>
</div>

<div class="hidden" id="eventmain-content" style="height:<?=$event['Height']+50?>px; overflow-y:auto;"></div>

<form method="post" action="">
<?php if ( canView( 'Events' ) ) { ?>
      <div class="eventList">
        <table class="eventListTbl" cellspacing="0">
          <thead>
            <tr>
              <th class="colId"><?= $SLANG['Id'] ?></th>
              <th class="colName"><?= $SLANG['Name'] ?></th>
              <th class="colTime"><?= $SLANG['Time'] ?></th>
              <th class="colSecs"><?= $SLANG['Secs'] ?></th>
              <th class="colFrames"><?= $SLANG['Frames'] ?></th>
              <th class="colScore"><?= $SLANG['Score'] ?></th>
              <th class="colMark"><input type="checkbox" name="toggleCheck" value="1" onclick="toggleCheckbox( this, 'markEids' );"<?php if ( !canEdit( 'Events' ) ) { ?> disabled="disabled"<?php } ?>/></th>
            </tr>
          </thead>
          <tbody>
		  <?php
		  foreach($eventdata as $row) {
			echo '<tr>
				<td><a href="javascript:loadsingleevent('.$row['Id'].')">'.$row['Id'].'</a></td>
				<td>'.$row['Name'].'</td>
				<td>'.$row['StartTime'].'</td>
				<td>'.$row['Length'].'</td>
				<td>'.$row['Frames'].'/'.$row['AlarmFrames'].'</td>
				<td>'.$row['AvgScore'].'/'.$row['MaxScore'].'</td>
				<td><input type="checkbox" name="markEids[]" value="'.$row['Id'].'" onclick="configureButton( this, \'markEids\' );" '.( !canEdit( 'Events' ) ? 'disabled="disabled"' : '').'/></td>
			</tr>';
		  }
/*var link = new Element( 'a', { 'href': '#', 'events': { 'click': createEventPopup.pass( [ event.Id, '&trms=1&attr1=MonitorId&op1=%3d&val1='+monitorId+'&page=1', event.Width, event.Height ] ) } });
link.injectInside( row.getElement( 'td.colId' ) );

link = new Element( 'a', { 'href': '#', 'events': { 'click': createEventPopup.pass( [ event.Id, '&trms=1&attr1=MonitorId&op1=%3d&val1='+monitorId+'&page=1', event.Width, event.Height ] ) } });
link.injectInside( row.getElement( 'td.colName' ) );

link = new Element( 'a', { 'href': '#', 'events': { 'click': createFramesPopup.pass( [ event.Id, event.Width, event.Height ] ) } });
link.injectInside( row.getElement( 'td.colFrames' ) );

link = new Element( 'a', { 'href': '#', 'events': { 'click': createFramePopup.pass( [ event.Id, '0', event.Width, event.Height ] ) } });
link.injectInside( row.getElement( 'td.colScore' ) );

link = new Element( 'a', { 'href': '#', 'title': deleteString, 'events': { 'click': deleteEvent.bindWithEvent( link, event.Id ), 'mouseover': highlightRow.pass( row ), 'mouseout': highlightRow.pass( row ) } });
link.injectInside( row.getElement( 'td.colDelete' ) );
*/
		  ?>
          </tbody>
        </table>
      </div>
<?php } 
if ( canEdit( 'Events' ) ) {
?>
        <div id="contentButtons">
          <input type="button" name="archiveBtn" value="<?= $SLANG['Archive'] ?>" onclick="archiveEvents( this, 'markEids' )" disabled="disabled"/>
          <input type="button" name="unarchiveBtn" value="<?= $SLANG['Unarchive'] ?>" onclick="unarchiveEvents( this, 'markEids' );" disabled="disabled"/>
          <input type="button" name="editBtn" value="<?= $SLANG['Edit'] ?>" onclick="editEvents( this, 'markEids' )" disabled="disabled"/>
          <input type="button" name="exportBtn" value="<?= $SLANG['Export'] ?>" onclick="exportEvents( this, 'markEids' )" disabled="disabled"/>
          <input type="button" name="deleteBtn" value="<?= $SLANG['Delete'] ?>" onclick="deleteEvents( this, 'markEids' );" disabled="disabled"/>
        </div>
<?php
}
?>
</form>

