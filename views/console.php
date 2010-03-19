<?php
//
// ZoneMinder web console file, $Date: 2009-02-19 10:05:31 +0000 (Thu, 19 Feb 2009) $, $Revision: 2780 $
// Copyright (C) 2001-2008 Philip Coombes
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
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

$eventCounts = array(
    array(
        "title" => $SLANG['Events'],
        "filter" => array(
            "terms" => array(
            )
        ),
    ),
    array(
        "title" => $SLANG['Hour'],
        "filter" => array(
            "terms" => array(
                array( "attr" => "Archived", "op" => "=", "val" => "0" ),
                array( "cnj" => "and", "attr" => "DateTime", "op" => ">=", "val" => "-1 hour" ),
            )
        ),
    ),
    array(
        "title" => $SLANG['Day'],
        "filter" => array(
            "terms" => array(
                array( "attr" => "Archived", "op" => "=", "val" => "0" ),
                array( "cnj" => "and", "attr" => "DateTime", "op" => ">=", "val" => "-1 day" ),
            )
        ),
    ),
    array(
        "title" => $SLANG['Week'],
        "filter" => array(
            "terms" => array(
                array( "attr" => "Archived", "op" => "=", "val" => "0" ),
                array( "cnj" => "and", "attr" => "DateTime", "op" => ">=", "val" => "-7 day" ),
            )
        ),
    ),
    array(
        "title" => $SLANG['Month'],
        "filter" => array(
            "terms" => array(
                array( "attr" => "Archived", "op" => "=", "val" => "0" ),
                array( "cnj" => "and", "attr" => "DateTime", "op" => ">=", "val" => "-1 month" ),
            )
        ),
    ),
    array(
        "title" => $SLANG['Archived'],
        "filter" => array(
            "terms" => array(
                array( "attr" => "Archived", "op" => "=", "val" => "1" ),
            )
        ),
    ),
);

$running = daemonCheck();
$status = $running?$SLANG['Running']:$SLANG['Stopped'];

if ( $group = dbFetchOne( "select * from Groups where Id = '".(empty($_COOKIE['zmGroup'])?0:dbEscape($_COOKIE['zmGroup']))."'" ) )
    $groupIds = array_flip(split( ',', $group['MonitorIds'] ));

noCacheHeaders();

$maxWidth = 0;
$maxHeight = 0;
$cycleCount = 0;
$minSequence = 0;
$maxSequence = 1;
$seqIdList = array();
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
    $monitors[$i]['Show'] = true;
    if ( empty($minSequence) || ($monitors[$i]['Sequence'] < $minSequence) )
    {
        $minSequence = $monitors[$i]['Sequence'];
    }
    if ( $monitors[$i]['Sequence'] > $maxSequence )
    {
        $maxSequence = $monitors[$i]['Sequence'];
    }
    $monitors[$i]['zmc'] = zmcStatus( $monitors[$i] );
    $monitors[$i]['zma'] = zmaStatus( $monitors[$i] );
    $monitors[$i]['ZoneCount'] = dbFetchOne( "select count(Id) as ZoneCount from Zones where MonitorId = '".$monitors[$i]['Id']."'", "ZoneCount" );
    $counts = array();
    for ( $j = 0; $j < count($eventCounts); $j++ )
    {
        $filter = addFilterTerm( $eventCounts[$j]['filter'], count($eventCounts[$j]['filter']['terms']), array( "cnj" => "and", "attr" => "MonitorId", "op" => "=", "val" => $monitors[$i]['Id'] ) );
        parseFilter( $filter );
        $counts[] = "count(if(1".$filter['sql'].",1,NULL)) as EventCount$j";
        $monitors[$i]['eventCounts'][$j]['filter'] = $filter;
    }
    $sql = "select ".join($counts,", ")." from Events as E where MonitorId = '".$monitors[$i]['Id']."'";
    $counts = dbFetchOne( $sql );
    if ( $monitors[$i]['Function'] != 'None' )
    {
        $cycleCount++;
        $scaleWidth = reScale( $monitors[$i]['Width'], $monitors[$i]['DefaultScale'], ZM_WEB_DEFAULT_SCALE );
        $scaleHeight = reScale( $monitors[$i]['Height'], $monitors[$i]['DefaultScale'], ZM_WEB_DEFAULT_SCALE );
        if ( $maxWidth < $scaleWidth ) $maxWidth = $scaleWidth;
        if ( $maxHeight < $scaleHeight ) $maxHeight = $scaleHeight;
    }
    $monitors[$i] = array_merge( $monitors[$i], $counts );
    $seqIdList[] = $monitors[$i]['Id'];
    $displayMonitors[] = $monitors[$i];
}
$lastId = 0;
$seqIdUpList = array();
foreach ( $seqIdList as $seqId )
{
    if ( !empty($lastId) )
        $seqIdUpList[$seqId] = $lastId;
    else
        $seqIdUpList[$seqId] = $seqId;
    $lastId = $seqId;
}
$lastId = 0;
$seqIdDownList = array();
foreach ( array_reverse($seqIdList) as $seqId )
{
    if ( !empty($lastId) )
        $seqIdDownList[$seqId] = $lastId;
    else
        $seqIdDownList[$seqId] = $seqId;
    $lastId = $seqId;
}

$cycleWidth = $maxWidth;
$cycleHeight = $maxHeight;

$eventsView = ZM_WEB_EVENTS_VIEW;
$eventsWindow = 'zm'.ucfirst(ZM_WEB_EVENTS_VIEW);

$eventCount = 0;
for ( $i = 0; $i < count($eventCounts); $i++ )
{
    $eventCounts[$i]['total'] = 0;
}
$zoneCount = 0;
foreach( $displayMonitors as $monitor )
{
    for ( $i = 0; $i < count($eventCounts); $i++ )
    {
        $eventCounts[$i]['total'] += $monitor['EventCount'.$i];
    }
    $zoneCount += $monitor['ZoneCount'];
}

$seqUpFile = getSkinFile( 'graphics/seq-u.gif' );
$seqDownFile = getSkinFile( 'graphics/seq-d.gif' );

xhtmlHeaders( __FILE__, $SLANG['Console'] );
?>
<body>
  <div id="page">
    <form name="monitorForm" method="get" action="<?= $_SERVER['PHP_SELF'] ?>">
    <input type="hidden" name="view" value="<?= $view ?>"/>
    <input type="hidden" name="action" value=""/>
    <div id="header">
      <h3 id="systemTime"><?= preg_match( '/%/', DATE_FMT_CONSOLE_LONG )?strftime( DATE_FMT_CONSOLE_LONG ):date( DATE_FMT_CONSOLE_LONG ) ?></h3>
      <h3 id="systemStats"><?= $SLANG['Load'] ?>: <?= getLoad() ?> / <?= $SLANG['Disk'] ?>: <?= getDiskPercent() ?>%</h3>
      <h2 id="title"><a href="http://www.zoneminder.com" target="ZoneMinder">ZoneMinder</a> <?= $SLANG['Console'] ?> - <?= makePopupLink( '?view=state', 'zmState', 'state', $status, canEdit( 'System' ) ) ?> - <?= makePopupLink( '?view=version', 'zmVersion', 'version', "v".ZM_VERSION, canEdit( 'System' ) ) ?></h2>
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
if ( canView( 'Stream' ) && $cycleCount > 1 )
{
    $cycleGroup = isset($_COOKIE['zmGroup'])?$_COOKIE['zmGroup']:0;
?>
      <div id="cycleMontage"><?= makePopupLink( '?view=cycle&group='.$cycleGroup, 'zmCycle'.$cycleGroup, array( 'cycle', $cycleWidth, $cycleHeight ), $SLANG['Cycle'], $running ) ?>&nbsp;/&nbsp;<?= makePopupLink( '?view=montage&group='.$cycleGroup, 'zmMontage'.$cycleGroup, 'montage', $SLANG['Montage'], $running ) ?></div>
<?php
}
else
{
?>
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
    <div id="content">
<?php
    if ( !$monitor['zmc'] )
        $dclass = "errorText";
    else
    {
        if ( !$monitor['zma'] )
            $dclass = "warnText";
        else
            $dclass = "infoText";
    }
    if ( $monitor['Function'] == 'None' )
        $fclass = "errorText";
    elseif ( $monitor['Function'] == 'Monitor' )
        $fclass = "warnText";
    else
        $fclass = "infoText";
    if ( !$monitor['Enabled'] )
        $fclass .= " disabledText";
    $scale = max( reScale( SCALE_BASE, $monitor['DefaultScale'], ZM_WEB_DEFAULT_SCALE ), SCALE_BASE );
?>
<ul id="monitors">
<?php
$scale = "20%";
foreach( $displayMonitors as $monitor )
{
?>
     <li id="monitor_<?php echo $monitor['Id'] ?>">
<?php
 if ($_COOKIE['zmBandwidth'] == 'low' || $_COOKIE['zmBandwidth'] == "medium") {
  $streamSrc = getStreamSrc( array( "mode=single", "monitor=".$monitor['Id'], "scale=".$scale ) );
  outputImageStill( "liveStream", $streamSrc, reScale( $monitor['Width'], $scale ), reScale( $monitor['Height'], $scale ), $monitor['Name'] );
 } elseif ($_COOKIE['zmBandwidth'] == 'high') {
   if ( ZM_STREAM_METHOD == 'mpeg' && ZM_MPEG_LIVE_FORMAT ) {
    $streamMode = "mpeg";
    $streamSrc = getStreamSrc( array( "mode=".$streamMode, "monitor=".$monitor['Id'], "scale=".$scale, "bitrate=".ZM_WEB_VIDEO_BITRATE, "maxfps=".ZM_WEB_VIDEO_MAXFPS, "format=".ZM_MPEG_LIVE_FORMAT, "buffer=".$monitor['StreamReplayBuffer'] ) );
} elseif ( canStream() ) {
    $streamMode = "jpeg";
    $streamSrc = getStreamSrc( array( "mode=".$streamMode, "monitor=".$monitor['Id'], "scale=".$scale, "maxfps=".ZM_WEB_VIDEO_MAXFPS, "buffer=".$monitor['StreamReplayBuffer'] ) );
  }
  outputImageStill( "liveStream", $streamSrc, reScale( $monitor['Width'], $scale ), reScale( $monitor['Height'], $scale ), $monitor['Name'] );
 }
?>
 
      <p><?= makePopupLink( '?view=watch&mid='.$monitor['Id'], 'zmWatch'.$monitor['Id'], array( 'watch', reScale( $monitor['Width'], $scale ), reScale( $monitor['Height'], $scale ) ), $monitor['Name'], $running && ($monitor['Function'] != 'None') && canView( 'Stream' ) ) ?> (<?php echo $monitor['Id'] ?>)</p>
      <p>Function: <?= makePopupLink( '?view=function&mid='.$monitor['Id'], 'zmFunction', 'function', '<span class="'.$fclass.'">'.$monitor['Function'].'</span>', canEdit( 'Monitors' ) ) ?></p>
<p>Source:
<?php if ( $monitor['Type'] == "Local" ) { ?>
            <?= makePopupLink( '?view=monitor&mid='.$monitor['Id'], 'zmMonitor'.$monitor['Id'], 'monitor', '<span class="'.$dclass.'">'.$monitor['Device'].' ('.$monitor['Channel'].')</span>', canEdit( 'Monitors' ) ) ?>
<?php } elseif ( $monitor['Type'] == "Remote" ) { ?>
            <?= makePopupLink( '?view=monitor&mid='.$monitor['Id'], 'zmMonitor'.$monitor['Id'], 'monitor', '<span class="'.$dclass.'">'.preg_replace( '/^.*@/', '', $monitor['Host'] ).'</span>', canEdit( 'Monitors' ) ) ?>
<?php } elseif ( $monitor['Type'] == "File" ) { ?>
            <?= makePopupLink( '?view=monitor&mid='.$monitor['Id'], 'zmMonitor'.$monitor['Id'], 'monitor', '<span class="'.$dclass.'">'.preg_replace( '/^.*\//', '', $monitor['Path'] ).'</span>', canEdit( 'Monitors' ) ) ?>
<?php } elseif ( $monitor['Type'] == "Ffmpeg" ) { ?>
            <?= makePopupLink( '?view=monitor&mid='.$monitor['Id'], 'zmMonitor'.$monitor['Id'], 'monitor', '<span class="'.$dclass.'">'.preg_replace( '/^.*\//', '', $monitor['Path'] ).'</span>', canEdit( 'Monitors' ) ) ?>
<?php } else { ?>
            <p>&nbsp;</p>
<?php
}
?>
</p>
            <p>Zones: <?= makePopupLink( '?view=zones&mid='.$monitor['Id'], 'zmZones', array( 'zones', $monitor['Width'], $monitor['Height'] ), $monitor['ZoneCount'], canView( 'Monitors' ) ) ?></p>
	 <p>Delete:
<?php
    if ( canEdit('Monitors') )
    {
?>
<?php
    }
?>
            <input type="checkbox" name="markMids[]" value="<?= $monitor['Id'] ?>" onclick="setButtonStates( this )"<?php if ( !canEdit( 'Monitors' ) || $user['MonitorIds'] ) {?> disabled="disabled"<?php } ?>/>

	</p>

<dl>
<?php
    for ( $i = 0; $i < count($eventCounts); $i++ )
    {
     echo "<dt>" . $eventCounts[$i]["title"] . ":</dt> ";
?>
            <dd><?= makePopupLink( '?view='.$eventsView.'&page=1'.$monitor['eventCounts'][$i]['filter']['query'], $eventsWindow, $eventsView, $monitor['EventCount'.$i], canView( 'Events' ) ) ?></dd>
<?php
    }
?>
</dl>
</li>
<?php } ?>
</ul>
<table style="clear:both;">
<?php
    if ( canEdit('Monitors') )
    {
?>
<?php
    }
?>
<tfoot>
<?php
for ( $i = 0; $i < count($eventCounts); $i++ )
{
    parseFilter( $eventCounts[$i]['filter'] );
?>
<!--        <td class="colEvents"><?= makePopupLink( '?view='.$eventsView.'&page=1'.$eventCounts[$i]['filter']['query'], $eventsWindow, $eventsView, $eventCounts[$i]['total'], canView( 'Events' ) ) ?></td>-->
<?php
}
?>
          <tr>
            <td class="colLeftButtons" colspan="3">
              <input type="button" value="<?= $SLANG['Refresh'] ?>" onclick="location.reload(true);"/>
              <?= makePopupButton( '?view=monitor', 'zmMonitor0', 'monitor', $SLANG['AddNewMonitor'], (canEdit( 'Monitors' ) && !$user['MonitorIds']) ) ?>
              <?= makePopupButton( '?view=filter&filter[terms][0][attr]=DateTime&filter[terms][0][op]=%3c&filter[terms][0][val]=now', 'zmFilter', 'filter', $SLANG['Filters'], canView( 'Events' ) ) ?>
            <input type="button" name="deleteBtn" value="<?= $SLANG['Delete'] ?>" onclick="deleteMonitor( this )"/>
            </td>
          </tr>
        </tfoot>
</table>
    </div>
    </form>
  </div>
</body>
</html>
