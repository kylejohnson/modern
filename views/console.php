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


noCacheHeaders();

$maxWidth = 0;
$maxHeight = 0;
$cycleCount = 0;

$cycleWidth = $maxWidth;
$cycleHeight = $maxHeight;

xhtmlHeaders( __FILE__, $SLANG['Console'] );
?>
<body>
<input type="hidden" id="inptRefresh" value="<?= ZM_WEB_REFRESH_MAIN ?>"></input>
    <?php require("header.php");  $monitors2 = $monitors?>
    <div id="content" class="clearfix">
     <div id="dialog" title="Tab Data">
       <fieldset class="ui-helper-reset">
        <label for="tab_title">Title:</label> <input type="text" name="tab_title" id="tab_title" value="" class="ui-widget-content ui-corner-all" />
	<br />
	<label for="selMonitors">Monitors:</label>
	<select id="selMonitors" multiple>
<?php
 foreach($monitors2 as $monitor){
  echo "<option value=\"$monitor[Id]\">$monitor[Name]</option>";
 }
?>
	</select>
        </fieldset>
     </div>
     <div id="infobar">
      <div id="pagetitle" class="left">
       <h2>Dashboard</h2>
      </div>
      <div id="widget_actions" class="right">
       <ul>
        <li><button id="add_tab">Add Tab</button></li>
        <li><button id="add_widget">Add Widget</button></li>
       </ul>
      </div>
     </div>
     <div id="tabs">
      <ul>
       <li><a href="#all">All</a></li>
      </ul>
     <div id="all">
      <ul id="monitors" class="clearfix">
      </ul>
     </div>
     </div>
  </div>
<?php require_once("footer.php"); ?>
