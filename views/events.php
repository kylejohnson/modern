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


$monitorsSql = "select Name, Id from Monitors";
$monitors = array();
dbFetchAll($monitorsSql);

$maxShortcuts = 5;
$maxWidth = 0;
$maxHeight = 0;
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
      <div id="loading">
      </div>
     </div>
   </div>
<div id="sidebarHistory">
 <h2>Search</h2>
 <fieldset>
 <legend>Monitors</legend>
 <ul>
<?php foreach ($monitors as $monitor) { ?>
  <li>
   <input type="checkbox" name="monitorName" id="<?= $monitor['Name'] ?>" /> <label for="<?= $monitor['Name'] ?>"><?= $monitor['Name'] ?></label>
  </li>
<?php } ?>
 </ul>
 </fieldset>
 
 <fieldset>
  <legend>Date</legend>
 </fieldset>
 
 <fieldset>
  <legend>Presets</legend>
 </fieldset>

</div>
   <?php require("footer.php"); ?>
  </div>
</body>
</html>
