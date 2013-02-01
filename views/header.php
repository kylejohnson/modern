<?php

$running = daemonCheck();
$status = $running?$SLANG['Running']:$SLANG['Stopped'];


if ( $group = dbFetchOne( "select * from Groups where Id = '".(empty($_COOKIE['zmGroup'])?0:dbEscape($_COOKIE['zmGroup']))."'" ) )
    $groupIds = array_flip(split( ',', $group['MonitorIds'] ));

$monitors = dbFetchAll( "select * from Monitors order by Sequence asc" );

$displayMonitors = array();
for ( $i = 0; $i < count($monitors); $i++ )
{
    if ( !visibleMonitor( $monitors[$i]['Id'] ) )
    {
        continue;
    }
    if ( $group && !empty($groupIds) && !array_key_exists( $monitors[$i]['Id'], $groupIds ) )
    {
        continue;
    }
    $monitors[$i]['zmc'] = zmcStatus( $monitors[$i] );
    $monitors[$i]['zma'] = zmaStatus( $monitors[$i] );
    $monitors[$i]['ZoneCount'] = dbFetchOne( "select count(Id) as ZoneCount from Zones where MonitorId = '".$monitors[$i]['Id']."'", "ZoneCount" );
    $displayMonitors[] = $monitors[$i];
}

$_view = $_REQUEST['view'];
if(empty($_view)) $_view = 'console';

?>

<div id="nav" class="clearfix" style="margin-bottom:1px;position:relative;min-width:750px;">
	<ul>
		<li><a id="dashboard" href="?view=console" class="<?php if($_view=='console') echo 'active';?>">Dashboard</a></li>
		<li><a id="events" href="?view=events" class="<?php if($_view=='events') echo 'active';?>">Events</a></li>
		<li><a id="administration" href="?view=admin" class="<?php if($_view=='admin') echo 'active';?>">Admin.</a></li>
		<li><a id="options" href="?view=options" class="<?php if($_view=='options') echo 'active';?>">Options</a></li>
		<!--<li><a id="performance" href="?view=system" class="<?php if($_view=='system') echo 'active';?>">Logs</a></li>-->
		<li><a id="performance" href="?view=log"    class="<?php if($_view=='log') echo 'active';?>">Logs</a></li>
	</ul>
</div>
<div style="clear:both;"></div>
