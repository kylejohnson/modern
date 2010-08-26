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

?>

 <div id="nav" class="clearfix">
     <ul>
      <li><a id="dashboard" href="?view=console">Dashboard</a></li>
      <li><a id="events" href="?view=events">Events</a></li>
      <li><a id="administration" href="?view=admin">Admin.</a></li>
      <li><a id="options" href="?view=options">Options</a></li>
      <li><a id="performance" href="?view=system">Performance</a></li>
     </ul>
    </div>
