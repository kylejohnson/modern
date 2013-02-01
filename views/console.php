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
<style>
#monitors .alarm {
    color: #ff0000;
}

#monitors .alert {
    color: #ffa500;
}

</style>

<script>
var $m = document.id; //mootools no conflict;
var thisUrl = "<?= ZM_BASE_URL.$_SERVER['PHP_SELF'] ?>";
var AJAX_TIMEOUT = <?= ZM_WEB_AJAX_TIMEOUT ?>;
var STATE_IDLE = <?= STATE_IDLE ?>;
var STATE_PREALARM = <?= STATE_PREALARM ?>;
var STATE_ALARM = <?= STATE_ALARM ?>;
var STATE_ALERT = <?= STATE_ALERT ?>;
var STATE_TAPE = <?= STATE_TAPE ?>;
var CMD_QUERY = <?= CMD_QUERY ?>;
var SOUND_ON_ALARM = <?= ZM_WEB_SOUND_ON_ALARM ?>;
var POPUP_ON_ALARM = <?= ZM_WEB_POPUP_ON_ALARM ?>;

var statusRefreshTimeout = <?= 1000*ZM_WEB_REFRESH_STATUS ?>;
var requestQueue = new Request.Queue( { concurrent: 2 } );

function Monitor( index, id, connKey ) {
	this.index = index;
	this.id = id;
	this.connKey = connKey;
	this.status = null;
	this.alarmState = STATE_IDLE;
	this.lastAlarmState = STATE_IDLE;
	this.streamCmdParms = "view=request&request=stream&connkey="+this.connKey;
	this.streamCmdTimer = null;

	
	this.start = function( delay ) {
		this.streamCmdTimer = this.streamCmdQuery.delay( delay, this );

    }
	
	this.setStateClass = function( element, stateClass ) {
		if ( !element.hasClass( stateClass ) ) {
			if ( stateClass != 'alarm' ) element.removeClass( 'alarm' );
			if ( stateClass != 'alert' ) element.removeClass( 'alert' );
			if ( stateClass != 'idle' ) element.removeClass( 'idle' );
			element.addClass( stateClass );
		}
	}

	
	this.getStreamCmdResponse = function( respObj, respText ) {
		if ( this.streamCmdTimer ) this.streamCmdTimer = $clear( this.streamCmdTimer );

		if ( respObj.result == 'Ok' ) {
			this.status = respObj.status;
			this.alarmState = this.status.state;

			var stateClass = "";
			if ( this.alarmState == STATE_ALARM ) stateClass = "alarm";
			else if ( this.alarmState == STATE_ALERT ) stateClass = "alert";
			else stateClass = "idle";

			this.setStateClass( $m('monitor'+this.index), stateClass );

			//Stream could be an applet so can't use moo tools
			var stream = document.getElementById( "liveStream"+this.id );
            stream.className = stateClass;

			var isAlarmed = ( this.alarmState == STATE_ALARM || this.alarmState == STATE_ALERT );
			var wasAlarmed = ( this.lastAlarmState == STATE_ALARM || this.lastAlarmState == STATE_ALERT );

			var newAlarm = ( isAlarmed && !wasAlarmed );
			var oldAlarm = ( !isAlarmed && wasAlarmed );

			if ( newAlarm ) {
				if ( false && SOUND_ON_ALARM ) {
					// Enable the alarm sound
					$m('alarmSound').removeClass( 'hidden' );
				}
				if ( POPUP_ON_ALARM ) { windowToFront(); }
			}
			if ( false && SOUND_ON_ALARM ) {
				if ( oldAlarm ) {
					// Disable alarm sound
					$m('alarmSound').addClass( 'hidden' );
				}
			}
		}
		else {
			console.error( respObj.message );
		}
		var streamCmdTimeout = statusRefreshTimeout;
		if ( this.alarmState == STATE_ALARM || this.alarmState == STATE_ALERT ) streamCmdTimeout = streamCmdTimeout/5;
		this.streamCmdTimer = this.streamCmdQuery.delay( streamCmdTimeout, this );
		this.lastAlarmState = this.alarmState;
    }

    this.streamCmdQuery = function( resent ) {
        //if ( resent )
            //console.log( this.connKey+": Resending" );
        //this.streamCmdReq.cancel();
        this.streamCmdReq.send( this.streamCmdParms+"&command="+CMD_QUERY );
    }

	this.streamCmdReq = new Request.JSON( { url: thisUrl, method: 'post', timeout: AJAX_TIMEOUT, onSuccess: this.getStreamCmdResponse.bind( this ), onTimeout: this.streamCmdQuery.bind( this, true ), link: 'cancel' } );

	requestQueue.addRequest( "cmdReq"+this.id, this.streamCmdReq );
	
}
</script>





<input type="hidden" id="inptRefresh" value="<?= ZM_WEB_REFRESH_MAIN ?>"></input>
<?php require("header.php");  $monitors2 = $monitors?>
<!--<div id="widget_actions" style="position:absolute;top:61px;right:14px;z-index:100">
	<ul>
		<li><button id="add_tab" style="height:25px;">Add Tab</button></li>
		<li><button id="change_view" style="height:25px;">Views</button></li>
		<li><button id="refresh_monitors" style="height:25px;">Refresh</button></li>
	</ul>
</div>-->
<div id="content" class="clearfix">
	<div id="dialog" title="Tab Data">
		<fieldset class="ui-helper-reset">
			<label for="tab_title">Title:</label> <input type="text" name="tab_title" id="tab_title" value="" class="ui-widget-content ui-corner-all" />
			<br />
			<label for="selMonitors">Monitors:</label>
			<select id="selMonitors" multiple>
			<?php foreach($monitors2 as $monitor) echo "<option value=\"$monitor[Id]\">$monitor[Name]</option>"; ?>
			</select>
		</fieldset>
	</div>
	<div id="tabs">
		<ul>
			<li><a href="skins/modern/views/monitors-view.php">All</a></li>
			<li style="float:right;"><button id="refresh_monitors"  style="height:22px;">Refresh</button></li>
			<li style="float:right;"><button id="change_view"  style="height:22px;">Views</button></li>
			<li style="float:right;"><button id="add_tab"  style="height:22px;">Add Tab</button></li>
		</ul>
		<div id="all"><ul id="monitors" class="clearfix"></ul></div>
	</div>
</div>


<div style="display:none;">
	<div id="viewcontrol" >
		<style>.btngridsize, .btnmonitor { width:125px; }</style>
		<table width="100%"><tr valign="top"><td>
			<h2>Views</h2><hr>
			<div align="center">
				<div style="float:left;"><a href="javascript:loadcameraview(1)"><img id="id-console-views-img-1" src="<?=getSkinFile('graphics/view_buttons/view_1_unpressed.png')?>" border="0" ></a></div>
				<div style="float:left;"><a href="javascript:loadcameraview(4)"><img id="id-console-views-img-4" src="<?=getSkinFile('graphics/view_buttons/view_4_unpressed.png')?>" border="0"></a></div>
				<div style="clear:both;"></div>
				<div style="float:left;"><a href="javascript:loadcameraview(6)"><img id="id-console-views-img-6" src="<?=getSkinFile('graphics/view_buttons/view_6_unpressed.png')?>" border="0"></a></div>
				<div style="float:left;"><a href="javascript:loadcameraview(8)"><img id="id-console-views-img-8" src="<?=getSkinFile('graphics/view_buttons/view_8_unpressed.png')?>" border="0"></a></div>
				<div style="clear:both;"></div>
				<div style="float:left;"><a href="javascript:loadcameraview(9)"><img id="id-console-views-img-9" src="<?=getSkinFile('graphics/view_buttons/view_9_unpressed.png')?>" border="0"></a></div>
				<div style="float:left;"><a href="javascript:loadcameraview(10)"><img id="id-console-views-img-10" src="<?=getSkinFile('graphics/view_buttons/view_10_unpressed.png')?>" border="0"></a></div>
				<div style="clear:both;"></div>
				<div style="float:left;"><a href="javascript:loadcameraview(13)"><img id="id-console-views-img-13" src="<?=getSkinFile('graphics/view_buttons/view_13_unpressed.png')?>" border="0"></a></div>
				<div style="float:left;"><a href="javascript:loadcameraview(16)"><img id="id-console-views-img-16" src="<?=getSkinFile('graphics/view_buttons/view_16_unpressed.png')?>" border="0"></a></div>
				<div style="clear:both;"></div>
			</div>
			<br><br><br>

		</td><td>

			<h2>Columns</h2><hr>
			<div>
				<div><button class="btngridsize" type="button" onclick="loadcameragrid(this,2);" number="2">2 Columns</button></div>
				<div><button class="btngridsize" type="button" onclick="loadcameragrid(this,3);" number="3" DISABLED>3 Columns</button></div>
				<div><button class="btngridsize" type="button" onclick="loadcameragrid(this,4);" number="4">4 Columns</button></div>
			</div>

			<br><br><br>
		</td><td>

			<h2>Monitors</h2><hr>
			<div>
				<?php
				foreach( $displayMonitors as $monitor ) {
					echo '<div><button class="btnmonitor" type="button" onclick="loadcameramonitor(this,'.$monitor['Id'].');return false;" number="'.$monitor['Id'].'">'.$monitor['Name'].'</button></div>';
				}
				?>
			</div>	
		
		</td></tr></table>
	</div>




</div>
 
<?php require_once("footer.php"); ?>
