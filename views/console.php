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
var consoleRefreshTimeout = <?= 1000*ZM_WEB_REFRESH_MAIN ?>;
$(document).ready(function() {

    $("#monitors").load("/skins/new/views/monitors.php");
    var refreshId = setInterval(function() {
       $("#monitors").load('/skins/new/views/monitors.php?randval='+ Math.random());
    }, consoleRefreshTimeout);

  $("#monitors").sortable({ opacity: 0.6, cursor: 'move', update: function() {
    var order = $(this).sortable("serialize") + '&action=sequence';
    $.post("skins/new/includes/updateSequence.php", order);
   }});

});
</script>
  <div id="page">
    <?php require_once("header.php"); ?>
    <div id="content" class="clearfix">
<ul id="monitors">
</ul>
  </div>
<?php require_once("footer.php"); ?>
