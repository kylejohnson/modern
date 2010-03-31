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

$running = daemonCheck();
$status = $running?$SLANG['Running']:$SLANG['Stopped'];

if ( $group = dbFetchOne( "select * from Groups where Id = '".(empty($_COOKIE['zmGroup'])?0:dbEscape($_COOKIE['zmGroup']))."'" ) )
    $groupIds = array_flip(split( ',', $group['MonitorIds'] ));

noCacheHeaders();

$maxWidth = 0;
$maxHeight = 0;
$cycleCount = 0;
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

$cycleWidth = $maxWidth;
$cycleHeight = $maxHeight;


xhtmlHeaders( __FILE__, $SLANG['Console'] );

?>
<body>
<script type="text/javascript" src="skins/new/js/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="skins/new/js/jquery-ui-1.8rc3.custom.min.js"></script>
<script type="text/javascript">
$(document).ready(function(){
        $(function() {
                $("#monitors").sortable({ opacity: 0.6, cursor: 'move', update: function() {
                        var order = $(this).sortable("serialize") + '&action=sequence';
                        $.post("skins/new/includes/updateSequence.php", order);
                }
                });
        });

});
</script>
  <div id="page">
    <form name="monitorForm" method="get" action="<?= $_SERVER['PHP_SELF'] ?>">
    <input type="hidden" name="view" value="<?= $view ?>"/>
    <input type="hidden" name="action" value=""/>
    <div id="header">
      <h3 id="systemTime"><?= preg_match( '/%/', DATE_FMT_CONSOLE_LONG )?strftime( DATE_FMT_CONSOLE_LONG ):date( DATE_FMT_CONSOLE_LONG ) ?></h3>
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
      <li><a href="/?view=monitor">Add New Camera</a></li>
      <li><a href="/?view=options">Options</a></li>
      <li><a href="/?view=system">System</a></li>
     </ul>
    </div>
    <div id="content" class="clearfix">
<ul id="monitors">
<?php
$scale = "35%";
foreach( $displayMonitors as $monitor ){
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
?>
     <li id="monitor_<?php echo $monitor['Id'] ?>">
<?php
 if ($_COOKIE['zmBandwidth'] == 'low' || $_COOKIE['zmBandwidth'] == "medium") {
  $streamSrc = getStreamSrc( array( "mode=single", "monitor=".$monitor['Id'], "scale=".$scale ) );
 } elseif ($_COOKIE['zmBandwidth'] == 'high') {
   if ( ZM_STREAM_METHOD == 'mpeg' && ZM_MPEG_LIVE_FORMAT ) {
    $streamMode = "mpeg";
    $streamSrc = getStreamSrc( array( "mode=".$streamMode, "monitor=".$monitor['Id'], "scale=".$scale, "bitrate=".ZM_WEB_VIDEO_BITRATE, "maxfps=".ZM_WEB_VIDEO_MAXFPS, "format=".ZM_MPEG_LIVE_FORMAT, "buffer=".$monitor['StreamReplayBuffer'] ) );
} elseif ( canStream() ) {
    $streamMode = "jpeg";
    $streamSrc = getStreamSrc( array( "mode=".$streamMode, "monitor=".$monitor['Id'], "scale=".$scale, "maxfps=".ZM_WEB_VIDEO_MAXFPS, "buffer=".$monitor['StreamReplayBuffer'] ) );
  }
 }
?>
 
      <?= makePopupImage( '?view=watch&mid='.$monitor['Id'], 'zmWatch'.$monitor['Id'], array( 'watch', reScale( $monitor['Width'], $scale ), reScale( $monitor['Height'], $scale ) ), $streamSrc, $monitor['Name'], $running && ($monitor['Function'] != 'None') && canView( 'Stream' ) ) ?>

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

</li>
<?php } ?>
</ul>

  </div>
<div id="footer">

              <input type="button" value="<?= $SLANG['Refresh'] ?>" onclick="location.reload(true);"/>
              <?= makePopupButton( '?view=monitor', 'zmMonitor0', 'monitor', $SLANG['AddNewMonitor'], (canEdit( 'Monitors' ) && !$user['MonitorIds']) ) ?>
              <?= makePopupButton( '?view=filter&filter[terms][0][attr]=DateTime&filter[terms][0][op]=%3c&filter[terms][0][val]=now', 'zmFilter', 'filter', $SLANG['Filters'], canView( 'Events' ) ) ?>
            <input type="button" name="deleteBtn" value="<?= $SLANG['Delete'] ?>" onclick="deleteMonitor( this )"/>
</div>
    </div>
    </form>
</body>
</html>
