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

    <div id="header">
      <h2 id="title"><a href="http://www.zoneminder.com" target="ZoneMinder">ZoneMinder</a> <?= $SLANG['Console'] ?> - <?= makePopupLink( '?view=state', 'zmState', 'state', $status, canEdit( 'System' ) ) ?></h2>
      <div class="clear"></div>
      <div id="monitorSummary"><?= makePopupLink( '?view=groups', 'zmGroups', 'groups', sprintf( $CLANG['MonitorCount'], count($displayMonitors), zmVlang( $VLANG['Monitor'], count($displayMonitors) ) ).($group?' ('.$group['Name'].')':''), canView( 'System' ) ); ?></div>
<?php
if ( ZM_OPT_X10 && canView( 'Devices' ) )
{
?>
      <div id="devices"><?= makePopupLink( '?view=devices', 'zmDevices', 'devices', $SLANG['Devices'] ) ?></div>
<?php
}
if ( canView( 'System' ) )
{
?>
      <div id="options"><?= makePopupLink( '?view=options', 'zmOptions', 'options', $SLANG['Options'] ) ?></div>
<?php
}
?>
      <h3 id="loginBandwidth"><?php
if ( ZM_OPT_USE_AUTH )
{
?><?= $SLANG['LoggedInAs'] ?> <?= makePopupLink( '?view=logout', 'zmLogout', 'logout', $user['Username'], (ZM_AUTH_TYPE == "builtin") ) ?>, <?= strtolower( $SLANG['ConfiguredFor'] ) ?><?php
}
else
{
?><?= $SLANG['ConfiguredFor'] ?><?php
}
?>&nbsp;<?= makePopupLink( '?view=bandwidth', 'zmBandwidth', 'bandwidth', $bwArray[$_COOKIE['zmBandwidth']], ($user && $user['MaxBandwidth'] != 'low' ) ) ?> <?= $SLANG['Bandwidth'] ?></h3>
    </div>
 <div id="nav" class="clearfix">
     <ul>
      <li><a href="/">Cameras</a></li>
      <li><a href="/?view=events&amp;page=1">Events</a></li>
      <li><a href="/?view=timeline&amp;page=1">Timeline</a></li>
      <li><a href="/?view=admin">Admin.</a></li>
      <li><a href="/?view=options">Options</a></li>
      <li><a href="/?view=system">System</a></li>
     </ul>
    </div>
