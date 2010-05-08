<?php
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

$focusWindow = true;

xhtmlHeaders(__FILE__, $SLANG['Events'] );

?>
<body>
  <div id="page">
   <?php require("header.php"); ?>
    <div id="content">
     <div id="contentcolumn">
      <div id="gallery" class="ad-gallery">
       <div class="ad-image-wrapper">
       </div>
       <div class="ad-controls">
	<div id="video-controls">
         <input type="submit" value="Play" id="btnPlay" disabled="disabled"></input>
         <input type="submit" value="Pause" id="btnPause"></input>
	 <input type="submit" value="Delete" id="btnDelete"></input>
	 <span id="export"><input type="submit" value="Export" id="btnExport"></input></span>
	</div>
       </div>
       <div class="ad-nav">
        <div class="ad-thumbs">
         <ul class="ad-thumb-list">
         </ul>
        </div>
       </div>
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
  <ul id="filterSpecificDate" class="filter">
   <li><label id="lblFrom" for="inptDateFrom">From:</label> <input type="text" id="inptDateFrom" /></li>
   <li><label id="lblTo" for="inptDateTo">To:</label> <input type="text" id="inptDateTo" /></li>
  </ul>
 </fieldset>
 <fieldset>
  <legend>Time</legend>
  <ul id="filterSpecificTime" class="filter">
   <li><label id="lblTimeFrom" for="inptTimeFrom">From:</label> <input type="text" id="inptTimeFrom" /></li>
   <li><label id="lblTimeTo" for="inptTimeTo">To:</label> <input type="text" id="inptTimeTo" /></li>
  </ul>
 </fieldset>
 <fieldset>
  <legend>Event</legend>
  <ul class="filter">
   <li><label id="lblEventID" for="inptEventID">Event ID:</label> <input type="text" id="inptEventID" /></li>
  </ul>
 </fieldset>
 <div id="filterSubmit">
  <input value="Submit" type="submit" id="btnSubmit"></input>
 </div>
 
 <div class="spinner">
 </div>

</div>
   <?php require("footer.php"); ?>
