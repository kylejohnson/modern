<?php
//
// ZoneMinder web event view file, $Date: 2009-05-08 12:21:28 +0100 (Fri, 08 May 2009) $, $Revision: 2866 $
// Copyright (C) 2001-2008 Philip Coombes
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
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

if ( !canView( 'Events' ) )
{
    $view = "error";
    return;
}

$eid = validInt( $_REQUEST['eid'] );
$fid = !empty($_REQUEST['fid'])?validInt($_REQUEST['fid']):1;

if ( $user['MonitorIds'] )
    $midSql = " and MonitorId in (".join( ",", preg_split( '/["\'\s]*,["\'\s]*/', dbEscape($user['MonitorIds']) ) ).")";
else
    $midSql = '';

$sql = "select E.*,M.Name as MonitorName,M.Id as mid, M.Width,M.Height,M.DefaultRate,M.DefaultScale from Events as E inner join Monitors as M on E.MonitorId = M.Id where E.Id = '".dbEscape($eid)."'".$midSql;
$event = dbFetchOne( $sql );

if ( isset( $_REQUEST['rate'] ) )
    $rate = validInt($_REQUEST['rate']);
else
    $rate = reScale( RATE_BASE, $event['DefaultRate'], ZM_WEB_DEFAULT_RATE );
if ( isset( $_REQUEST['scale'] ) )
    $scale = validInt($_REQUEST['scale']);
else
    $scale = reScale( SCALE_BASE, $event['DefaultScale'], ZM_WEB_DEFAULT_SCALE );

$replayModes = array(
    'single' => $SLANG['ReplaySingle'],
    'all' => $SLANG['ReplayAll'],
    'gapless' => $SLANG['ReplayGapless'],
);

if ( isset( $_REQUEST['streamMode'] ) )
    $streamMode = validHtmlStr($_REQUEST['streamMode']);
else
    $streamMode = canStream()?'stream':'stills';

if ( isset( $_REQUEST['replayMode'] ) )
    $replayMode = validHtmlStr($_REQUEST['replayMode']);
else
    $replayMode = array_shift( array_keys( $replayModes ) );

parseSort();
parseFilter( $_REQUEST['filter'] );
$filterQuery = $_REQUEST['filter']['query'];

$connkey = generateConnKey();

xhtmlHeaders(__FILE__, $SLANG['Event'] );
?>
<body>
 <input type="hidden" value="<?=$eid?>" id="inptEID" />
  <div id="page">
    <div id="content">
      <div id="eventStream">
	<table style="width:600px; margin:0 auto;">
	 <tr>
	  <td class="left"><span id="dataTime" title="<?= $SLANG['Time'] ?>"><?= strftime( STRF_FMT_DATETIME_SHORT, strtotime($event['StartTime'] ) ) ?></span></td>
          <td class="right"><span id="dataDuration" title="<?= $SLANG['Duration'] ?>"><?= $event['Length'] ?></span>s</td>
	 </tr>
	</table>
        <div id="imageFeed">
         <img src="events/<?=$event['mid']?>/<?=$eid?>/001-capture.jpg" id="img_0" alt="" />
        </div>
       <div id="videoExport">
	<span id="progress"></span>
        <input type="submit" value="Play" id="btnPlay" disabled="disabled"></input>
        <input type="submit" value="Pause" id="btnPause"></input>
	<input type="submit" value="Delete" id="btnDelete"></input>
	<input type="submit" value="Export" id="btnExport"></input>
	<span id="spinner"></span>
       </div>
       <div id="eventStills"></div>
      </div>
     </div>
    </div>
  </div>
</body>
</html>
