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

$monitors = dbFetchAll( "select * from Monitors order by Sequence asc" );
$displayMonitors = array();
for ( $i = 0; $i < count($monitors); $i++ )
{
    $monitors[$i]['Show'] = true;
    $monitors[$i]['zmc'] = zmcStatus( $monitors[$i] );
    $monitors[$i]['zma'] = zmaStatus( $monitors[$i] );
    $monitors[$i]['ZoneCount'] = dbFetchOne( "select count(Id) as ZoneCount from Zones where MonitorId = '".$monitors[$i]['Id']."'", "ZoneCount" );
    $counts = array();
    $monitors[$i] = array_merge( $monitors[$i], $counts );
    $seqIdList[] = $monitors[$i]['Id'];
    $displayMonitors[] = $monitors[$i];
}


noCacheHeaders();

$maxWidth = 0;
$maxHeight = 0;
$cycleCount = 0;

$cycleWidth = $maxWidth;
$cycleHeight = $maxHeight;

xhtmlHeaders( __FILE__, "Admin" );
?>
<body>
 <div id="page">
 <?php require_once("header.php"); ?>
 <div id="content">
  <table id="tblMonitors">
   <thead>
    <th>Name</th>
    <th>Function</th>
    <th>Source</th>
    <th>Zones</th>
    <th></th>
   </thead>
   <tbody>
   <?php
    foreach($displayMonitors as $monitor) {
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
    <tr>
     <td><?= $monitor['Name']; ?></td>
     <td class="colFunction"><?= makePopupLink( '?view=function&mid='.$monitor['Id'], 'zmFunction', 'function', '<span class="'.$fclass.'">'.$monitor['Function'].'</span>', canEdit( 'Monitors' ) ) ?></td>
     <?php if ( $monitor['Type'] == "Local" ) { ?>
      <td class="colSource"><?= makePopupLink( '?view=monitor&mid='.$monitor['Id'], 'zmMonitor'.$monitor['Id'], 'monitor', '<span class="'.$dclass.'">'.$monitor['Device'].' ('.$monitor['Channel'].')</span>', canEdit( 'Monitors' ) ) ?></td>
     <?php } elseif ( $monitor['Type'] == "Remote" ) { ?>
      <td class="colSource"><?= makePopupLink( '?view=monitor&mid='.$monitor['Id'], 'zmMonitor'.$monitor['Id'], 'monitor', '<span class="'.$dclass.'">'.preg_replace( '/^.*@/', '', $monitor['Host'] ).'</span>', canEdit( 'Monitors' ) ) ?></td>
     <?php } elseif ( $monitor['Type'] == "File" ) { ?>
      <td class="colSource"><?= makePopupLink( '?view=monitor&mid='.$monitor['Id'], 'zmMonitor'.$monitor['Id'], 'monitor', '<span class="'.$dclass.'">'.preg_replace( '/^.*\//', '', $monitor['Path'] ).'</span>', canEdit( 'Monitors' ) ) ?></td>
     <?php } elseif ( $monitor['Type'] == "Ffmpeg" ) { ?>
      <td class="colSource"><?= makePopupLink( '?view=monitor&mid='.$monitor['Id'], 'zmMonitor'.$monitor['Id'], 'monitor', '<span class="'.$dclass.'">'.preg_replace( '/^.*\//', '', $monitor['Path'] ).'</span>', canEdit( 'Monitors' ) ) ?></td>
     <?php } else { ?>
      <td class="colSource">&nbsp;</td>
     <?php } ?>
     <td class="colZones"><?= makePopupLink( '?view=zones&mid='.$monitor['Id'], 'zmZones', array( 'zones', $monitor['Width'], $monitor['Height'] ), $monitor['ZoneCount'], canView( 'Monitors' ) ) ?></td>
     <td class="colMark"><input type="checkbox" name="markMids[]" value="<?= $monitor['Id'] ?>" onclick="setButtonStates( this )"<?php if ( !canEdit( 'Monitors' ) || $user['MonitorIds'] ) {?> disabled="disabled"<?php } ?>/></td>
    </tr>
   <?php
    }
   ?>
   </tbody>
   <tfoot>
    <td colspan="2"><a id="addMonitor" href="/?view=monitor">Add Monitor</a></td>
    <td colspan="3"><a id="delMonitor" href="/?view=monitor">Delete Monitor</a></td>
   </tfoot>
  </table>
  <ul>
  </ul>
 </div> 
 <?php require_once("footer.php"); ?>
 </div>
