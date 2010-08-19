<?php
require_once("../../../includes/config.php");
require_once("../includes/config.php");
require_once("../../../includes/database.php");
require_once("../../../includes/functions.php");
$mid = $_REQUEST['mid'];
$groupName = $_REQUEST['groupName'];
$bandwidth = $_COOKIE['zmBandwidth'];
if ( isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == 'on' ){
 $protocol = 'https';
} else {
 $protocol = 'http';
}
define( "ZM_BASE_URL", $protocol.'://'.$_SERVER['HTTP_HOST'] );

if ($mid) {
 $monitors = dbFetchAll( "select Id, Name, Width, Height from Monitors where Id = " . $mid . " order by Sequence asc" );
 foreach( $monitors as $monitor ){
  displayMonitor($monitor);
 }
} elseif ($groupName){ # If a list of monitors
?>
 <ul id="monitors" class="clearfix">
<?php
 $query = "select MonitorIds from Groups where Name = '".$groupName."'"; // Get all of the mids in a group
 $result = mysql_query($query); // Get all of the mids in a group
 $row = mysql_result($result, 0);
  $mids = explode(",", $row); # Put them into an array
 foreach ($mids as $mid){ # Foreach item in the array
  $query = "select Id, Name, Width, Height from Monitors where Id = ".$mid;
  foreach(dbFetchAll($query) as $monitor){ # Query the database
   displayMonitor($monitor); # And call displayMonitor with the result
 }
}
?>
 </ul>
<?php
} else {
 $monitors = dbFetchAll( "select Id, Name, Width, Height from Monitors order by Sequence asc" );
 foreach( $monitors as $monitor ){
  displayMonitor($monitor);
 }
}


function displayMonitor($monitor){
 if (!defined(ZM_WEB_DEFAULT_SCALE)) {
  $scale = 40;
 } else {
  $scale = ZM_WEB_DEFAULT_SCALE;
 }
 if (($bandwidth == 'low' || $bandwidth == "medium" || $bandwidth == "") || !($bandwidth)) {
  $streamSrc = getStreamSrc( array( "mode=single", "monitor=".$monitor['Id'], "scale=".$scale ) );
 } elseif ($bandwidth == 'high') {
   if ( ZM_STREAM_METHOD == 'mpeg' && ZM_MPEG_LIVE_FORMAT ) {
    $streamMode = "mpeg";
    $streamSrc = getStreamSrc( array( "mode=".$streamMode, "monitor=".$monitor['Id'], "scale=".$scale, "bitrate=".ZM_WEB_VIDEO_BITRATE, "maxfps=".ZM_WEB_VIDEO_MAXFPS, "format=".ZM_MPEG_LIVE_FORMAT, "buffer=".$monitor['StreamReplayBuffer'] ) );
} elseif ( canStream() ) {
    $streamMode = "jpeg";
    $streamSrc = getStreamSrc( array( "mode=".$streamMode, "monitor=".$monitor['Id'], "scale=".$scale, "maxfps=".ZM_WEB_VIDEO_MAXFPS, "buffer=".$monitor['StreamReplayBuffer'] ) );
  }
 }
 $width = ($monitor['Width'] * ('.' . $scale) + 20);
?>
<li id="monitor_<?php echo $monitor['Id'] ?>" style="width:<?php echo $width ?>px;">
 <div class="mon_header">
  <h3 style="display:inline;"><?php echo $monitor['Name'] ?></h3>
  <div class="right">
   <div class="spinner"></div>
   <div class="minimize"><img src="skins/new/graphics/minimize.png" style="width:15px;" alt="minimize" /></div>
  </div>
 </div>
 <div class="mon">
 <a href="?view=events&page=1&filter[terms][0][attr]=MonitorId&filter[terms][0][op]==&filter[terms][0][val]=<?php echo $monitor['Id'] ?>" >
  <?php outputImageStill( "liveStream", $streamSrc, reScale( $monitor['Width'], $scale ), reScale( $monitor['Height'], $scale ), $monitor['Name'] ); ?>
 </a>
 </div>
 <div class="monfooter">
 </div>
 <a rel="monitor" href="?view=watch&amp;mid=<?= $monitor['Id']; ?>" title="<?= $monitor['Name']; ?>">View Full</a>
</li>
<?php } ?>
