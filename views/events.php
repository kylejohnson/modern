<?php

xhtmlHeaders(__FILE__, $SLANG['Events'] );

?>
<body>
<input type="hidden" value="<?=$tab?>" id="inptTab"/>
   <?php require("header.php"); ?>
 <div id="content">
  <div id="yui-b">
   <div id="tabs_events">
    <ul></ul>
    <div></div>
   </div> <!-- tabs ends -->
  </div>
  <div id="sidebar">
   <div id="sidebarHistory">
    <h2>Events</h2>
    <fieldset>
     <legend>Monitors</legend>
     <ul id="monitors_search"></ul>
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
<br />
    <input type="submit" id="btnDelete" value="Delete" />
    </div>

   </div> <!-- sidebarHistory ends -->
 </div> <!-- Sidebar ends -->
</div> <!-- Content ends -->
   <?php require("footer.php"); ?>
