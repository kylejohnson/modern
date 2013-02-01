<?php
$events_per_page = 24;

$MonitorName = $_REQUEST['MonitorName'];
$page = @(int)$_REQUEST['page'];
$offset = ($page * $events_per_page );
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

$sql = "SELECT E.Id,E.MonitorId,E.StartTime,M.Name As MonitorName,M.Width,M.Height,M.DefaultScale,Date(E.StartTime) as Date, Time(E.StartTime) as Time,E.Length 
		  FROM Monitors as M 
		  JOIN Events as E on (M.Id = E.MonitorId) 
		 WHERE (M.Name = '$MonitorName') 
		 ORDER BY ".$order_by_definition[$order_by]."
		 LIMIT $offset,".$events_per_page;
$result = mysql_query($sql) or die('Error, selecting monitors failed.');
$count = mysql_num_rows($result);
?>
<div>
	<?php if($page>0) { ?><div style="float:left"><input type="button" value="previous" onclick="pagination(<?php echo ($page-1); ?>,'<?php echo $order_by;?>')" /></div><?php } ?>
	<?php if($count>0 && $count==$events_per_page) { ?><div style="float:right;"><input type="button" value="next"  onclick="pagination(<?php echo ($page+1); ?>,'<?php echo $order_by;?>')" /></div><?php } ?>
</div>
<div class="clearfix"></div>

<?php if ($count > 0) { ?>
	<div class="pagemark"><p><?=$offset+1?> to <?=$offset+$events_per_page?></p></div>
	<?php
	while ($event = mysql_fetch_array($result)){
		$thumbData = createListThumbnail($event);
	?>
		<div class="thumb" id="<?=$event['Id']?>">
			<a class="event" href="?view=event&amp;eid=<?= $event['Id'] ?>"><img src="<?= viewImagePath($thumbData['Path']) ?>" alt="<?= $event['Id'] ?> Thumbnail" width="<?php echo $thumb_width;?>" /></a>
			<p>Date: <?=$event['Date'] ?></p>
			<p>Time: <?=$event['Time'] ?></p>
			<p>Event: <?= $event['Id'] ?></p>
			<p>Duration: <?= $event['Length'] ?></p>
			<input type="checkbox" name="event" value="<?=$event['Id']?>" />
		</div>
	<?php } ?>
	<div class="pagemark"><p><?=$offset+1?> to <?=$offset+$events_per_page?></p></div>
	<div>
		<?php if($page>0) { ?><div style="float:left"><input type="button" value="previous" onclick="pagination(<?php echo ($page-1); ?>,'<?php echo $order_by;?>')" /></div><?php } ?>
		<?php if($count>0 && $count==$events_per_page) { ?><div style="float:right;"><input type="button" value="next"  onclick="pagination(<?php echo ($page+1); ?>,'<?php echo $order_by;?>')" /></div><?php } ?>
	</div>
	<div class="clearfix"></div>
<?php } ?>
