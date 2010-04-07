<?php
//})_
//});
//
// ZoneMinder web events view file, $Date: 2008-10-20 09:25:24 +0100 (Mon, 20 Oct 2008) $, $Revision: 2669 $
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

if ( !canView( 'Events' ) || (!empty($_REQUEST['execute']) && !canEdit('Events')) )
{
$view = "error";
return;
}

if ( !empty($_REQUEST['execute']) )
{
executeFilter( $tempFilterName );
}

$countSql = "select count(E.Id) as EventCount from Monitors as M inner join Events as E on (M.Id = E.MonitorId) where";
$eventsSql = "select E.Id,E.MonitorId,M.Name As MonitorName,M.Width,M.Height,M.DefaultScale,E.Name,E.MaxScore,E.StartTime,E.Length,E.Archived from Monitors as M inner join Events as E on (M.Id = E.MonitorId) where";
if ( $user['MonitorIds'] )
{
$countSql .= " M.Id in (".join( ",", preg_split( '/["\'\s]*,["\'\s]*/', $user['MonitorIds'] ) ).")";
$eventsSql .= " M.Id in (".join( ",", preg_split( '/["\'\s]*,["\'\s]*/', $user['MonitorIds'] ) ).")";
}
else
{
$countSql .= " 1";
$eventsSql .= " 1";
}

parseSort();
parseFilter( $_REQUEST['filter'] );
$filterQuery = $_REQUEST['filter']['query'];

if ( $_REQUEST['filter']['sql'] )
{
$countSql .= $_REQUEST['filter']['sql'];
$eventsSql .= $_REQUEST['filter']['sql'];
}
$eventsSql .= " order by $sortColumn $sortOrder";

if ( isset($_REQUEST['page']) )
$page = validInt($_REQUEST['page']);
else
$page = 0;
if ( isset($_REQUEST['limit']) )
$limit = validInt($_REQUEST['limit']);
else
$limit = 0;

$nEvents = dbFetchOne( $countSql, 'EventCount' );
if ( !empty($limit) && $nEvents > $limit )
{
$nEvents = $limit;
}
$pages = (int)ceil($nEvents/ZM_WEB_EVENTS_PER_PAGE);
if ( $pages > 1 )
{
if ( !empty($page) )
{
if ( $page < 0 )
    $page = 1;
if ( $page > $pages )
    $page = $pages;
}
}
if ( !empty($page) )
{
$limitStart = (($page-1)*ZM_WEB_EVENTS_PER_PAGE);
if ( empty( $limit ) )
{
$limitAmount = ZM_WEB_EVENTS_PER_PAGE;
}
else
{
$limitLeft = $limit - $limitStart;
$limitAmount = ($limitLeft>ZM_WEB_EVENTS_PER_PAGE)?ZM_WEB_EVENTS_PER_PAGE:$limitLeft;
}
$eventsSql .= " limit $limitStart, $limitAmount";
}
elseif ( !empty( $limit ) )
{
$eventsSql .= " limit 0, ".dbEscape($limit);
}

$maxWidth = 0;
$maxHeight = 0;
$archived = false;
$unarchived = false;
$events = array();
foreach ( dbFetchAll( $eventsSql ) as $event )
{
$events[] = $event;
$scale = max( reScale( SCALE_BASE, $event['DefaultScale'], ZM_WEB_DEFAULT_SCALE ), SCALE_BASE );
$eventWidth = reScale( $event['Width'], $scale );
$eventHeight = reScale( $event['Height'], $scale );
if ( $maxWidth < $eventWidth ) $maxWidth = $eventWidth;
if ( $maxHeight < $eventHeight ) $maxHeight = $eventHeight;
if ( $event['Archived'] )
$archived = true;
else
$unarchived = true;
}

$monitorsSql = "select Name, Id from Monitors";
$monitors = array();
dbFetchAll($monitorsSql);

$maxShortcuts = 5;

$focusWindow = true;

xhtmlHeaders(__FILE__, $SLANG['Events'] );

?>
<body>
  <div id="page">
   <?php require("header.php"); ?>
    <div id="content">
     <div id="contentcolumn">
      <div id="events">
      </div>
<?php
$per_page = ZM_WEB_EVENTS_PER_PAGE;

//Calculating no of pages
$Sql = "select count(E.Id) as EventCount from Monitors as M inner join Events as E on (M.Id = E.MonitorId) where M.Id = 1";
$result = dbFetchOne( $Sql, 'EventCount' );
$pages = ceil($result/$per_page)
?>
<div id="loading" ></div>
<ul id="pagination">
<?php
//Pagination Numbers
for($i=1; $i<=$pages; $i++)
{
echo '<li id="'.$i.'">'.$i.'</li>';
}
?>
</ul>

     </div>
   </div>
<div id="sidebarHistory">
 <h2>Search</h2>
 <h3>Monitors</h3>
 <ul>
<?php foreach ($monitors as $monitor) { ?>
  <li>
   <input type="checkbox" name="monitorName" id="monitor_<?= $monitor['Id'] ?>" /> <label for="monitor_<?= $monitor['Id'] ?>"><?= $monitor['Name'] ?></label>
  </li>
<?php } ?>
 </ul>

 <h3>Date</h3>

 <h3>Presets</h3>
</div>
   <?php require("footer.php"); ?>
  </div>
</body>
</html>
