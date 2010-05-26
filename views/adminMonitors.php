<?php
require_once("../../../includes/config.php");
require_once("../../../includes/database.php");
require_once("../../../includes/functions.php");

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
?>
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
     <td class="colFunction"><?= makePopupLink( '?view=function&mid='.$monitor['Id'], 'zmFunction', 'function', '<span class="'.$fclass.'">'.$monitor['Function'].'</span>' ) ?></td>
     <?php if ( $monitor['Type'] == "Local" ) { ?>
      <td class="colSource"><?= makePopupLink( '?view=monitor&mid='.$monitor['Id'], 'zmMonitor'.$monitor['Id'], 'monitor', '<span class="'.$dclass.'">'.$monitor['Device'].' ('.$monitor['Channel'].')</span>' ) ?></td>
     <?php } elseif ( $monitor['Type'] == "Remote" ) { ?>
      <td class="colSource"><?= makePopupLink( '?view=monitor&mid='.$monitor['Id'], 'zmMonitor'.$monitor['Id'], 'monitor', '<span class="'.$dclass.'">'.preg_replace( '/^.*@/', '', $monitor['Host'] ).'</span>' ) ?></td>
     <?php } elseif ( $monitor['Type'] == "File" ) { ?>
      <td class="colSource"><?= makePopupLink( '?view=monitor&mid='.$monitor['Id'], 'zmMonitor'.$monitor['Id'], 'monitor', '<span class="'.$dclass.'">'.preg_replace( '/^.*\//', '', $monitor['Path'] ).'</span>' ) ?></td>
     <?php } elseif ( $monitor['Type'] == "Ffmpeg" ) { ?>
      <td class="colSource"><?= makePopupLink( '?view=monitor&mid='.$monitor['Id'], 'zmMonitor'.$monitor['Id'], 'monitor', '<span class="'.$dclass.'">'.preg_replace( '/^.*\//', '', $monitor['Path'] ).'</span>' ) ?></td>
     <?php } else { ?>
      <td class="colSource">&nbsp;</td>
     <?php } ?>
     <td class="colZones"><?= makePopupLink( '?view=zones&mid='.$monitor['Id'], 'zmZones', array( 'zones', $monitor['Width'], $monitor['Height'] ), $monitor['ZoneCount'] ) ?></td>
     <td class="colMark"><input type="checkbox"  value="<?= $monitor['Id'] ?>"/></td>
    </tr>
   <?php
    }
   ?>
   </tbody>
   <tfoot>
    <td colspan="2"><a id="addMonitor" href="/?view=monitor">Add Monitor</a></td>
    <td colspan="2"><a id="delMonitor" href="#">Delete Monitor</a></td>
    <td class="spinner"></td>
   </tfoot>
  </table>
 </div> 
