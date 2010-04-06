<?php
require_once("../../../includes/config.php");
require_once("../../../includes/database.php");
require_once("../../../includes/functions.php");
$per_page = ZM_WEB_EVENTS_PER_PAGE;
if($_GET)
{
$page=$_GET['page'];
}

$start = ($page-1)*$per_page;

$eventsSql = "select E.Id,E.MonitorId,M.Name As MonitorName,M.Width,M.Height,M.DefaultScale,E.Name,E.Cause,E.Notes,E.StartTime,E.Length,E.Frames,E.AlarmFrames,E.TotScore,E.AvgScore,E.MaxScore,E.Archived from Monitors as M inner join Events as E on (M.Id = E.MonitorId) limit $start,$per_page";

$maxWidth = 0;
$maxHeight = 0;
$archived = false;
$unarchived = false;
$events = array();
foreach (dbFetchAll($eventsSql) as $event) {
 $events[] = $event;
}
?>
<ul id="monitorHistory">
<?php
$count = 0;
foreach ( $events as $event ){
        if ( $thumbData = createListThumbnail( $event ) )
        {
?>
 <li>
  <a class="box" href="/?view=event&eid=<?= $event['Id'] ?>"">
   <img src="/<?= $thumbData['Path'] ?>" width="<?= $thumbData['Width'] ?>" height="<?= $thumbData['Height'] ?>" alt="<?= $thumbData['FrameId'].'/'.$event['MaxScore'] ?>" />
  </a>
  <p>Date: <?= strftime( STRF_FMT_DATETIME_SHORTER, strtotime($event['StartTime']) ) ?></p>
  <p>Duration: <?= $event['Length'] ?></p>
 </li>
<?php
        }
}
?>
</ul>
