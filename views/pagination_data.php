<?php
require_once '../includes/config.php';

$per_page = ZM_WEB_EVENTS_PER_PAGE;
$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
chdir(ZM_PATH_WEB);
$start = ($page-1)*$per_page;


$events_per_page = 24;
$offset = ($page * $events_per_page);
$count = $page * $offset;
$thumb_width = empty($_REQUEST['gridwidth']) ? 120 : ((int)($_REQUEST['gridwidth']/8)) - 32;
if($thumb_width<120) $thumb_width=120;

$order_by_definition = array( 
	''=>'E.StartTime DESC',
	'date'=>'E.StartTime ASC',
	'date_desc'=>'E.StartTime DESC',
	'number'=>'E.Frames ASC',
	'number_desc'=>'E.Frames DESC',
	'duration'=>'E.Length ASC',
	'duration_desc'=>'E.Length DESC',
	'score'=>'E.TotScore ASC',
	'score_desc'=>'E.TotScore DESC',
);
$order_by = empty($order_by_definition[$_REQUEST['order_by']]) ? '' : $_REQUEST['order_by'];

$qstring = $_SERVER['QUERY_STRING'];

unset($_REQUEST['page']);
$qstring = urldecode(http_build_query($_REQUEST));

$countSql = "select count(E.Id) as EventCount from Monitors as M inner join Events as E on (M.Id = E.MonitorId) where";
$eventsSql = "SELECT E.*,E.Id,E.MonitorId,M.Name As MonitorName,M.Width,M.Height,M.DefaultScale,Date(E.StartTime) as Date, Time(E.StartTime) as Time,E.Length 
				FROM Monitors as M 
				JOIN Events as E on (M.Id = E.MonitorId) WHERE";
if ( $user['MonitorIds'] ) {
	$countSql .= " M.Id in (".join( ",", preg_split( '/["\'\s]*,["\'\s]*/', $user['MonitorIds'] ) ).")";
	$eventsSql .= " M.Id in (".join( ",", preg_split( '/["\'\s]*,["\'\s]*/', $user['MonitorIds'] ) ).")";
}
else {
	$countSql .= " 1";
	$eventsSql .= " 1";
}

unset($_REQUEST['filter']['terms'][0]['cnj']); # seyi_code

parseSort();
parseFilter( $_REQUEST['filter'] );
$filterQuery = $_REQUEST['filter']['query'];
;
if ( $_REQUEST['filter']['sql'] ) {
	$countSql .= $_REQUEST['filter']['sql'];
	$eventsSql .= $_REQUEST['filter']['sql'];
}
//echo '<pre>'; print_r($_REQUEST);
$eventsSql .= " order by  ".$order_by_definition[$order_by];
//echo $eventsSql;
//if ( isset($_REQUEST['page']) )
//$page = validInt($_REQUEST['page']);
//else
//$page = 0;
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

/*
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
*/
$eventsSql .= "  limit $offset,$events_per_page ";

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
$count = count($events);

?>
<input type="hidden" id="inptMonitorName" value="<?= $event['MonitorName'] ?>"/>
<input type="hidden" id="inptPages" value="<?= $pages ?>"/>

<div>
	<?php if($page>0) { ?><div style="float:left"><input type="button" value="previous" onclick="pagination2('<?php echo addslashes($qstring); ?>',<?php echo ($page-1); ?>)" /></div><?php } ?>
	<?php if($count>0 && $count==$events_per_page) { ?><div style="float:right;"><input type="button" value="next"  onclick="pagination2('<?php echo addslashes($qstring); ?>',<?php echo ($page+1); ?>)" /></div><?php } ?>
</div>
<div style="clear:both;"></div>

<div style="font-size:0.3cm;">
 <div class="pagemark">
  <p><?=$offset+1?> to <?=$offset+$events_per_page?></p>
 </div>
<?php
foreach ( $events as $event ){
$eid = $event['Id'];
$mid = $event['MonitorId'];
$fullpath = "events/$mid/$eid/";
 if ($thumbData = createListThumbnail($event)) {

?>
		<div class="thumb" id="<?=$event['Id']?>">
			<a class="event" href="?view=event&amp;eid=<?= $event['Id'] ?>"><img src="events/<?php echo $thumbData['Path']; ?>" width="<?php echo $thumb_width; ?>" alt="<?= $thumbData['FrameId'].'/'.$event['MaxScore'] ?>" /></a>
			<p>Date: <?=$event['Date'] ?></p>
			<p>Time: <?=$event['Time'] ?></p>
			<p>Event: <?= $event['Id'] ?></p>
			<p>Duration: <?= $event['Length'] ?></p>
			<input type="checkbox" name="event" value="<?=$event['Id']?>" />
		</div>

<?php
 }
}
?>
 <div class="pagemark">
  <p><?=$offset+1?> to <?=$offset+$events_per_page?></p>
 </div>
</div>

<div>
	<?php if($page>0) { ?><div style="float:left"><input type="button" value="previous" onclick="pagination2('<?php echo addslashes($qstring); ?>',<?php echo ($page-1); ?>)" /></div><?php } ?>
	<?php if($count>0 && $count==$events_per_page) { ?><div style="float:right;"><input type="button" value="next"  onclick="pagination2('<?php echo addslashes($qstring); ?>',<?php echo ($page+1); ?>)" /></div><?php } ?>
</div>
<div style="clear:both;"></div>
