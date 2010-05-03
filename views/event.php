<?php
//
// ZoneMinder web event view file, $Date: 2009-05-08 12:21:28 +0100 (Fri, 08 May 2009) $, $Revision: 2866 $
// Copyright (C) 2001-2008 Philip Coombes
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
//

if ( !canView( 'Events' ) )
{
    $view = "error";
    return;
}

$eid = validInt( $_REQUEST['eid'] );
$fid = !empty($_REQUEST['fid'])?validInt($_REQUEST['fid']):1;

if ( $user['MonitorIds'] )
    $midSql = " and MonitorId in (".join( ",", preg_split( '/["\'\s]*,["\'\s]*/', dbEscape($user['MonitorIds']) ) ).")";
else
    $midSql = '';

$sql = "select E.*,M.Name as MonitorName,M.Width,M.Height,M.DefaultRate,M.DefaultScale from Events as E inner join Monitors as M on E.MonitorId = M.Id where E.Id = '".dbEscape($eid)."'".$midSql;
$event = dbFetchOne( $sql );

if ( isset( $_REQUEST['rate'] ) )
    $rate = validInt($_REQUEST['rate']);
else
    $rate = reScale( RATE_BASE, $event['DefaultRate'], ZM_WEB_DEFAULT_RATE );
if ( isset( $_REQUEST['scale'] ) )
    $scale = validInt($_REQUEST['scale']);
else
    $scale = reScale( SCALE_BASE, $event['DefaultScale'], ZM_WEB_DEFAULT_SCALE );

$replayModes = array(
    'single' => $SLANG['ReplaySingle'],
    'all' => $SLANG['ReplayAll'],
    'gapless' => $SLANG['ReplayGapless'],
);

if ( isset( $_REQUEST['streamMode'] ) )
    $streamMode = validHtmlStr($_REQUEST['streamMode']);
else
    $streamMode = canStream()?'stream':'stills';

if ( isset( $_REQUEST['replayMode'] ) )
    $replayMode = validHtmlStr($_REQUEST['replayMode']);
else
    $replayMode = array_shift( array_keys( $replayModes ) );

parseSort();
parseFilter( $_REQUEST['filter'] );
$filterQuery = $_REQUEST['filter']['query'];

$panelSections = 40;
$panelSectionWidth = (int)ceil(reScale($event['Width'],$scale)/$panelSections);
$panelWidth = ($panelSections*$panelSectionWidth-1);

$connkey = generateConnKey();

$focusWindow = true;

$eventMonitorSQL = "select MonitorID from Events where Id = $eid";
$eventMonitor = dbFetchOne($eventMonitorSQL);
$eventMonitor = $eventMonitor['MonitorID'];
$cwd = getcwd();
$mainpath = "events/$eventMonitor/$eid/"; # Full path to image directory
$files = scandir($cwd . "/" . $mainpath); # All of the files inside $path
array_shift($files);
array_shift($files);
array_shift($files);
$paths = array();
foreach ($files as $file){
 if (preg_match("/capture/i", $file)) 
 {
  $tmp = "/events/$eventMonitor/$eid/" . $file;
  array_push($paths, $tmp);
 }
};

xhtmlHeaders(__FILE__, $SLANG['Event'] );
?>
<script type="text/javascript">
$(function(){

$("#btnExport").button();
$("#btnExport").click(function() {
 $.post("skins/new/includes/createVideo.php?eid=<?= $eid ?>&action=video&path=<?= $mainpath ?>", function(data){
  $("#videoExport span").append(data);
 });
});

var images = new Array();
<?php
 foreach($paths as $key => $value) {
  echo "images[$key] = \"$value\";\n";
  }
?>
x = images.length;
for (image in images) {
 if (image == 0) {
  $("#imageFeed").append('<img src="' + images[image] + '" class="eventImage" id="img_' + image + '" />');
 } else {
  $("#imageFeed").append('<img src="' + images[image] + '" class="eventImageHide" id="img_' + image + '" />');
 }
}
i = 0;

function changeClass() {
 if (i<x){
  $("#img_" + (i - 1)).attr("class", "eventImageHide");
  $("#img_" + i).attr("class", "eventImage");
  i++;
 }
};

setInterval(function(){changeClass()}, 200);
 
});
</script>
<body>
  <div id="page">
    <div id="content">
      <div id="eventStream">
            <span id="dataTime" title="<?= $SLANG['Time'] ?>"><?= strftime( STRF_FMT_DATETIME_SHORT, strtotime($event['StartTime'] ) ) ?></span>
            <span id="dataDuration" title="<?= $SLANG['Duration'] ?>"><?= $event['Length'] ?></span>s
        <div id="imageFeed"></div>
       <div id="videoExport">
	<input type="submit" value="Export" id="btnExport"></input>
	<span></span>
       </div>
      </div>
      <div id="eventStills" class="hidden">
        <div id="eventThumbsPanel">
          <div id="eventThumbs">
          </div>
        </div>
        <div id="eventImagePanel" class="hidden">
          <div id="eventImageFrame">
            <img id="eventImage" src="graphics/transparent.gif" alt=""/>
            <div id="eventImageBar">
              <div id="eventImageStats" class="hidden"><input type="button" value="<?= $SLANG['Stats'] ?>" onclick="showFrameStats()"/></div>
              <div id="eventImageData">Frame <span id="eventImageNo"></span></div>
            </div>
          </div>
        </div>
        <div id="eventImageNav">
          <div id="eventImageButtons">
            <div id="prevButtonsPanel">
              <input id="prevEventBtn" type="button" value="&lt;E" onclick="prevEvent()" disabled="disabled"/>
              <input id="prevThumbsBtn" type="button" value="&lt;&lt;" onclick="prevThumbs()" disabled="disabled"/>
              <input id="prevImageBtn" type="button" value="&lt;" onclick="prevImage()" disabled="disabled"/>
              <input id="nextImageBtn" type="button" value="&gt;" onclick="nextImage()" disabled="disabled"/>
              <input id="nextThumbsBtn" type="button" value="&gt;&gt;" onclick="nextThumbs()" disabled="disabled"/>
              <input id="nextEventBtn" type="button" value="E&gt;" onclick="nextEvent()" disabled="disabled"/>
            </div>
          </div>
          <div id="thumbsSliderPanel">
            <div id="thumbsSlider">
                <div id="thumbsKnob">
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
