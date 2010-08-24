<?php
require_once("../../../includes/config.php");
require_once("../../../includes/functions.php");
require_once("../../../includes/database.php");
$per_page = ZM_WEB_EVENTS_PER_PAGE;
$page = isset($_GET['page']) ? $_GET['page'] : 1;
chdir(ZM_PATH_WEB);
$start = ($page-1)*$per_page;

$countSql = "select count(E.Id) as EventCount from Monitors as M inner join Events as E on (M.Id = E.MonitorId) where";
$eventsSql = "select E.Id,E.MonitorId,M.Name As MonitorName,M.Width,M.Height,M.DefaultScale,Date(E.StartTime) as Date, Time(E.StartTime) as Time,E.Length from Monitors as M inner join Events as E on (M.Id = E.MonitorId) where";
if ( $user['MonitorIds'] )
{
$countSql .= " M.Id in (".join( ",", preg_split( '/["\'\s]*,["\'\s]*/', $user['MonitorIds'] ) ).")";
$eventsSql .= " M.Id in (".join( ",", preg_split( '/["\'\s]*,["\'\s]*/', $user['MonitorIds'] ) ).")";
}
else
{
$countSql .= " 1";
$eventsSql .= " 1";
}

parseSort();
parseFilter( $_REQUEST['filter'] );
$filterQuery = $_REQUEST['filter']['query'];

if ( $_REQUEST['filter']['sql'] )
{
$countSql .= $_REQUEST['filter']['sql'];
$eventsSql .= $_REQUEST['filter']['sql'];
}
$eventsSql .= " order by E.StartTime desc";

if ( isset($_REQUEST['page']) )
$page = validInt($_REQUEST['page']);
else
$page = 0;
if ( isset($_REQUEST['limit']) )
$limit = validInt($_REQUEST['limit']);
else
$limit = 0;
$nEvents = dbFetchOne( $countSql, 'EventCount' );
if ( !empty($limit) && $nEvents > $limit )
{
$nEvents = $limit;
}
$pages = (int)ceil($nEvents/ZM_WEB_EVENTS_PER_PAGE);
if ( $pages > 1 )
{
if ( !empty($page) )
{
if ( $page < 0 )
    $page = 1;
if ( $page > $pages )
    $page = $pages;
}
}
if ( !empty($page) )
{
$limitStart = (($page-1)*ZM_WEB_EVENTS_PER_PAGE);
if ( empty( $limit ) )
{
$limitAmount = ZM_WEB_EVENTS_PER_PAGE;
}
else
{
$limitLeft = $limit - $limitStart;
$limitAmount = ($limitLeft>ZM_WEB_EVENTS_PER_PAGE)?ZM_WEB_EVENTS_PER_PAGE:$limitLeft;
}
$eventsSql .= " limit $limitStart, $limitAmount";
}
elseif ( !empty( $limit ) )
{
$eventsSql .= " limit 0, ".dbEscape($limit);
}

$maxWidth = 0;
$maxHeight = 0;
$archived = false;
$unarchived = false;
$events = array();
foreach (dbFetchAll($eventsSql) as $event) {
 $events[] = $event;
 $scale = max( reScale( SCALE_BASE, $event['DefaultScale'], ZM_WEB_DEFAULT_SCALE ), SCALE_BASE );
    $eventWidth = reScale( $event['Width'], $scale );
    $eventHeight = reScale( $event['Height'], $scale );
    if ( $maxWidth < $eventWidth ) $maxWidth = $eventWidth;
    if ( $maxHeight < $eventHeight ) $maxHeight = $eventHeight;
    if ( $event['Archived'] )
        $archived = true;
    else
        $unarchived = true;
}

?>
<input type="hidden" id="inptMonitorName" value="<?= $event['MonitorName'] ?>"/>
<input type="hidden" id="inptPages" value="<?= $pages ?>"/>
<?php
$count = 0;
foreach ( $events as $event ){
$eid = $event['Id'];
$mid = $event['MonitorId'];
$fullpath = "events/$mid/$eid/";
 if ($thumbData = createListThumbnail($event)) {
?>
<div id="<?= $event['Id']?>" class="thumb">
 <a href="<?= $fullpath ?>001-capture.jpg">
  <img src="<?= $thumbData['Path'] ?>" width="<?= $thumbData['Width'] ?>" height="<?= $thumbData['Height'] ?>" alt="<?= $thumbData['FrameId'].'/'.$event['MaxScore'] ?>" />
 </a>
  <p>Date: <?=$event['Date'] ?></p>
  <p>Time: <?=$event['Time'] ?></p>
  <p>Event: <?= $event['Id'] ?></p>
  <p>Duration: <?= $event['Length'] ?></p>
</div>
<?php
 }
}
?>
