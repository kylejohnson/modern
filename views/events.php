<?php

xhtmlHeaders(__FILE__, $SLANG['Events'] );

?>
<body>
<!--<div id="widget_actions" style="position:absolute;top:61px;right:14px;z-index:100">
	<ul>
		<li><button id="btn_graphs" style="height:25px;">Graphs</button></li>
		<li><button id="btn_advanced" style="height:25px;">Advanced</button></li>
	</ul>
</div>-->

<input type="hidden" value="<?=$tab?>" id="inptTab"/>
   <?php require("header.php"); ?>
<div id="content">
	<div id="yui-b">
		<div id="tabs_events">
			<ul>
				<!-- tabs go here -->
				<li style="float:right;"><button id="btn_advanced" style="height:22px;">Advanced</button></li>
				<li style="float:right;"><button id="btn_graphs" style="height:22px;">Graphs</button></li>
			</ul>
			<div ></div>
		</div> <!-- tabs ends -->
	</div>
	
<div id="sidebar">
	<div id="sidebarHistory">
		<h2>Events</h2>
		<fieldset><legend>Monitors</legend>
			<ul id="monitors_search"></ul>
		</fieldset>
 
		<fieldset><legend>Date</legend>
			<ul id="filterSpecificDate" class="filter">
				<li><label id="lblFrom" for="inptDateFrom">From:</label> <input type="text" id="inptDateFrom" value="<?php echo date('m/d/Y'); ?>" /></li>
				<li><label id="lblTo" for="inptDateTo">To:</label> <input type="text" id="inptDateTo" value="<?php echo date('m/d/Y'); ?>" /></li>
			</ul>
		</fieldset>

		<fieldset><legend>Time</legend>
			<ul id="filterSpecificTime" class="filter">
				<li><label id="lblTimeFrom" for="inptTimeFrom">From:</label> <input type="text" id="inptTimeFrom" value="00:00"/></li>
				<li><label id="lblTimeTo" for="inptTimeTo">To:</label> <input type="text" id="inptTimeTo" value="23:59" /></li>
			</ul>
		</fieldset>

		<fieldset><legend>Event</legend>
			<ul class="filter">
				<li><label id="lblEventID" for="inptEventID">Event ID:</label> <input type="text" id="inptEventID" /></li>
			</ul>
		</fieldset>

		<div id="filterSubmit">
			<div><input value="Submit" type="submit" id="btnSubmit"></input></div>
			<br />
			<br />
			<hr />
			<div><input type="checkbox" id="btnSelectall" /> &nbsp;Select All</div>
			<br />
			<div><input type="button" id="btnExportall" value="Export Selected" /></div>
			<br />
			<div><input type="submit" id="btnDelete" value="Delete" /></div>
			<br />
			<div>Sort By
				<select id="selSortBy">
					<option></option>
					<option value="date">Date Asc</option>
					<option value="date_desc">Date Desc</option>
					<option value="number">Frames Asc</option>
					<option value="number_desc">Frames Desc</option>
					<option value="duration">Duration Asc</option>
					<option value="duration_desc">Duration Desc</option>
					<option value="score">Score Asc</option>
					<option value="score_desc">Score Desc</option>
				</select>
			</div>
		</div>

   </div> <!-- sidebarHistory ends -->
 </div> <!-- Sidebar ends -->
</div> <!-- Content ends -->


<div id='spinner'></div>
<div id='screen'></div>
<style>
#spinner{
	width: 75px;
	height: 40px;
	background: #cccccc;
	border: #777777 solid 5px;
	text-align: center;
	position: absolute;
	margin-left: -75px;
	margin-top: -75px;
	left: 50%;
	top: 50%;
	z-index: 120;
	display: none;
}

#screen {
	position: absolute;
	left: 0;
	top: 0;
	z-index: 110;
	background: #000;
}
</style>

   <?php require("footer.php"); ?>
