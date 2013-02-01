function setButtonState( element, butClass ) {
	//element.className = butClass;
	//element.disabled = (butClass != 'inactive');
	
	element.removeClass().addClass(butClass);
	if(butClass != 'inactive') element.attr("disabled","disabled");
	else element.removeAttr("disabled");
}

function changeScale(){
	var scale = $j('#scale').val();
    var baseWidth = event.Width;
    var baseHeight = event.Height;
    var newWidth = ( baseWidth * scale ) / SCALE_BASE;
    var newHeight = ( baseHeight * scale ) / SCALE_BASE;

    streamScale( scale );

    /*Stream could be an applet so can't use moo tools*/ 
    var streamImg = document.getElementById('evtStream');
    streamImg.style.width = newWidth + "px";
    streamImg.style.height = newHeight + "px";
}

var streamParms = "view=request&request=stream&connkey="+connKey;
var streamCmdTimer = null;

var streamStatus = null;
var lastEventId = 0;

function getCmdResponse( respObj, respText ) {
    if ( checkStreamForErrors( "getCmdResponse" ,respObj ) )
        return;

    if ( streamCmdTimer ) streamCmdTimer = $clear( streamCmdTimer );

    streamStatus = respObj.status;

    var eventId = streamStatus.event;
    if ( eventId != lastEventId ) {
        eventQuery( eventId );
        lastEventId = eventId;
    }
    if ( streamStatus.paused == true ) {
		$j('span.modeValue').text("Paused" );
		$j('span.rate').addClass( 'hidden' );
        streamPause( false );
    }
    else {
		$j('span.modeValue').text( "Replay" );
		$j('span.rateValue').text( streamStatus.rate );
		$j('span.rate').removeClass( 'hidden' );
        streamPlay( false );
    }
	$j('span.progressValue').text( secsToTime( parseInt(streamStatus.progress) ) );
	$j('span.zoomValue').text( streamStatus.zoom );
    if ( streamStatus.zoom == "1.0" ) setButtonState( $j('#eventzoomOutBtn'), 'unavail' );
    else setButtonState( $j('#eventzoomOutBtn'), 'inactive' );

    updateProgressBar();

    streamCmdTimer = streamQuery.delay( streamTimeout );
}
function streamPause( action ) {
	setButtonState( $j('#eventpauseBtn'), 'active' );
	setButtonState( $j('#eventplayBtn'), 'inactive' );
    setButtonState( $j('#eventfastFwdBtn'), 'unavail' );
	setButtonState( $j('#eventslowFwdBtn'), 'inactive' );
	setButtonState( $j('#eventslowRevBtn'), 'inactive' );
	setButtonState( $j('#eventfastRevBtn'), 'unavail' );
 
 if ( action )
    {
        var streamReq = new Request.JSON( { url: thisUrl, method: 'post', timeout: AJAX_TIMEOUT, data: streamParms+"&command="+CMD_PAUSE, onSuccess: getCmdResponse } );
        streamReq.send();
    }
}
function streamPlay( action ) {
	setButtonState( $j('#eventpauseBtn'), 'inactive' );
    if (streamStatus)
		setButtonState( $j('#eventplayBtn'), streamStatus.rate==1?'active':'inactive' );
    setButtonState( $j('#eventfastFwdBtn'), 'inactive' );
	setButtonState( $j('#eventslowFwdBtn'), 'unavail' );
 	setButtonState( $j('#eventslowRevBtn'), 'unavail' );
	setButtonState( $j('#eventfastRevBtn'), 'inactive' );
    if ( action )
    {
        var streamReq = new Request.JSON( { url: thisUrl, method: 'post', timeout: AJAX_TIMEOUT, data: streamParms+"&command="+CMD_PLAY, onSuccess: getCmdResponse } );
        streamReq.send();
    }
}
function streamFastFwd( action ) {
	setButtonState( $j('#eventpauseBtn'), 'inactive' );
	setButtonState( $j('#eventplayBtn'), 'inactive' );
    setButtonState( $j('#eventfastFwdBtn'), 'inactive' );
	setButtonState( $j('#eventslowFwdBtn'), 'unavail' );
	setButtonState( $j('#eventslowRevBtn'), 'unavail' );
 	setButtonState( $j('#eventfastRevBtn'), 'inactive' );
   if ( action )
    {
        var streamReq = new Request.JSON( { url: thisUrl, method: 'post', timeout: AJAX_TIMEOUT, data: streamParms+"&command="+CMD_FASTFWD, onSuccess: getCmdResponse } );
        streamReq.send();
    }
}
function streamSlowFwd( action ) {
	setButtonState( $j('#eventpauseBtn'), 'inactive' );
	setButtonState( $j('#eventplayBtn'), 'inactive' );
    setButtonState( $j('#eventfastFwdBtn'), 'unavail' );
	setButtonState( $j('#eventslowFwdBtn'), 'active' );
 	setButtonState( $j('#eventslowRevBtn'), 'inactive' );
	setButtonState( $j('#eventfastRevBtn'), 'unavail' );
    if ( action )
    {
        var streamReq = new Request.JSON( { url: thisUrl, method: 'post', timeout: AJAX_TIMEOUT, data: streamParms+"&command="+CMD_SLOWFWD, onSuccess: getCmdResponse } );
        streamReq.send();
    }
	setButtonState( $j('#eventpauseBtn'), 'active' );
	setButtonState( $j('#eventslowFwdBtn'), 'inactive' );
}
function streamSlowRev( action ) {
	setButtonState( $j('#eventpauseBtn'), 'inactive' );
	setButtonState( $j('#eventplayBtn'), 'inactive' );
    setButtonState( $j('#eventfastFwdBtn'), 'unavail' );
	setButtonState( $j('#eventslowFwdBtn'), 'inactive' );
	setButtonState( $j('#eventslowRevBtn'), 'active' );
	setButtonState( $j('#eventfastRevBtn'), 'unavail' );
    if ( action )
    {
        var streamReq = new Request.JSON( { url: thisUrl, method: 'post', timeout: AJAX_TIMEOUT, data: streamParms+"&command="+CMD_SLOWREV, onSuccess: getCmdResponse } );
        streamReq.send();
    }
	setButtonState( $j('#eventpauseBtn'), 'active' );
	setButtonState( $j('#eventslowRevBtn'), 'inactive' );
}
function streamFastRev( action ) {
	setButtonState( $j('#eventpauseBtn'), 'inactive' );
	setButtonState( $j('#eventplayBtn'), 'inactive' );
    setButtonState( $j('#eventfastFwdBtn'), 'inactive' );
	setButtonState( $j('#eventslowFwdBtn'), 'unavail' );
	setButtonState( $j('#eventslowRevBtn'), 'unavail' );
	setButtonState( $j('#eventfastRevBtn'), 'inactive' );
    if ( action )
    {
        var streamReq = new Request.JSON( { url: thisUrl, method: 'post', timeout: AJAX_TIMEOUT, data: streamParms+"&command="+CMD_FASTREV, onSuccess: getCmdResponse } );
        streamReq.send();
    }
}
function streamPrev( action ) {
    streamPlay( false );
    if ( action )
    {
        var streamReq = new Request.JSON( { url: thisUrl, method: 'post', timeout: AJAX_TIMEOUT, data: streamParms+"&command="+CMD_PREV, onSuccess: getCmdResponse } );
        streamReq.send();
    }
}
function streamNext( action ) {
    streamPlay( false );
    if ( action )
    {
//alert(streamParms+"&command="+CMD_NEXT);
        var streamReq = new Request.JSON( { url: thisUrl, method: 'post', timeout: AJAX_TIMEOUT, data: streamParms+"&command="+CMD_NEXT, onSuccess: getCmdResponse } );
        streamReq.send();
    }
}
function streamZoomIn( x, y ) {
    var streamReq = new Request.JSON( { url: thisUrl, method: 'post', timeout: AJAX_TIMEOUT, data: streamParms+"&command="+CMD_ZOOMIN+"&x="+x+"&y="+y, onSuccess: getCmdResponse } );
    streamReq.send();
}
function streamZoomOut() {
    var streamReq = new Request.JSON( { url: thisUrl, method: 'post', timeout: AJAX_TIMEOUT, data: streamParms+"&command="+CMD_ZOOMOUT, onSuccess: getCmdResponse } );
    streamReq.send();
}
function streamScale( scale ) {
    var streamReq = new Request.JSON( { url: thisUrl, method: 'post', timeout: AJAX_TIMEOUT, data: streamParms+"&command="+CMD_SCALE+"&scale="+scale, onSuccess: getCmdResponse } );
    streamReq.send();
}
function streamPan( x, y ) {
    var streamReq = new Request.JSON( { url: thisUrl, method: 'post', timeout: AJAX_TIMEOUT, data: streamParms+"&command="+CMD_PAN+"&x="+x+"&y="+y, onSuccess: getCmdResponse } );
    streamReq.send();
}
function streamSeek( offset ) {
    var streamReq = new Request.JSON( { url: thisUrl, method: 'post', timeout: AJAX_TIMEOUT, data: streamParms+"&command="+CMD_SEEK+"&offset="+offset, onSuccess: getCmdResponse } );
    streamReq.send();
}
function streamQuery() {       
    var streamReq = new Request.JSON( { url: thisUrl, method: 'post', timeout: AJAX_TIMEOUT, data: streamParms+"&command="+CMD_QUERY, onSuccess: getCmdResponse } );
    streamReq.send();
}       

var slider = null;
var scroll = null;

function getEventResponse( respObj, respText ) {
    if ( checkStreamForErrors( "getEventResponse", respObj ) )
		return;

    event = respObj.event;
    if ( !$j('div.eventStills').hasClass( 'hidden' ) && currEventId != event.Id )
        resetEventStills();
    currEventId = event.Id;

    $j('#eventdataId').text(event.Id);
    if ( event.Notes ) $j("#eventdataCause").attr({ title: event.Notes });
    else $j("#eventdataCause").attr({ title: causeString });

	$j('#eventdataCause').text(event.Cause);
	$j('#eventdataTime').text(event.StartTime);
	$j('#eventdataDuration').text(event.Length);
	$j('#eventdataFrames').text(event.Frames+"/"+event.AlarmFrames);
	$j('#eventdataScore').text(event.TotScore+"/"+event.AvgScore+"/"+event.MaxScore);
	$j('#eventName').val(event.Name);


	if ( parseInt(event.Archived) )
    {
		$j("div.archiveEvent").addClass('hidden');
		$j("div.unarchiveEvent").removeClass('hidden');
    }
    else
    {
		$j("div.archiveEvent").removeClass('hidden');
		$j("div.unarchiveEvent").addClass('hidden');
    }
    //var eventImg = $('eventImage');
    //eventImg.setStyles( { 'width': event.width, 'height': event.height } );
    drawProgressBar();
    nearEventsQuery( event.Id );
}

function eventQuery( eventId ) {
    var eventParms = "view=request&request=status&entity=event&id="+eventId;
//alert(thisUrl+'?'+eventParms);
    var eventReq = new Request.JSON( { url: thisUrl, method: 'post', timeout: AJAX_TIMEOUT, data: eventParms, onSuccess: getEventResponse } );
    eventReq.send();
}

var prevEventId = 0;
var nextEventId = 0;

function getNearEventsResponse( respObj, respText ) {
    if ( checkStreamForErrors( "getNearEventsResponse", respObj ) )
        return;
    prevEventId = respObj.nearevents.PrevEventId;
    nextEventId = respObj.nearevents.NextEventId;

    $j('input.prevEventBtn').attr('disabled', !prevEventId?'disabled':'');
    $j('input.nextEventBtn').attr('disabled',!nextEventId?'disabled':'');
}

function nearEventsQuery( eventId ) {
    var parms = "view=request&request=status&entity=nearevents&id="+eventId;
    var query = new Request.JSON( { url: thisUrl, method: 'post', timeout: AJAX_TIMEOUT, data: parms, onSuccess: getNearEventsResponse } );
    query.send();
}

var frameBatch = 40;

function loadEventThumb( event, frame, loadImage ) {
    var thumbImg = $('eventThumb'+frame.FrameId);
    if ( !thumbImg )
    {
        console.error( "No holder found for frame "+frame.FrameId );
        return;
    }

    //var img = new Asset.image( frame.Image.imagePath,
    var img = new Asset.image( imagePrefix+frame.Image.imagePath,
        {
            'onload': ( function( loadImage )
                {
                    thumbImg.setProperty( 'src', img.getProperty( 'src' ) );
                    thumbImg.removeClass( 'placeholder' );
                    thumbImg.setProperty( 'class', frame.Type=='Alarm'?'alarm':'normal' );
                    thumbImg.setProperty( 'title', frame.FrameId+' / '+((frame.Type=='Alarm')?frame.Score:0) );
                    thumbImg.removeEvents( 'click' );
                    thumbImg.addEvent( 'click', function() { locateImage( frame.FrameId, true ); } );
                    if ( loadImage )
                        loadEventImage( event, frame );
                } ).pass( loadImage )
        }
    );
}
function updateStillsSizes( noDelay ) {
    var containerDim = $('eventThumbs').getSize();

    var containerWidth = containerDim.x;
    var containerHeight = containerDim.y;
    var popupWidth = parseInt($('eventImage').getStyle( 'width' ));
    var popupHeight = parseInt($('eventImage').getStyle( 'height' ));

    var left = (containerWidth - popupWidth)/2;
    if ( left < 0 ) left = 0;
    var top = (containerHeight - popupHeight)/2;
    if ( top < 0 ) top = 0;
    if ( popupHeight == 0 && !noDelay ) // image not yet loaded lets give it another second
    {
        updateStillsSizes.pass( true ).delay( 50 );
        return;
    }
	$j('#eventImagePanel').css( {'left': left,'top': top } );

}
function loadEventImage( event, frame ) {
    console.debug( "Loading "+event.Id+"/"+frame.FrameId );

    var eventImg = $('eventImage');
    var thumbImg = $('eventThumb'+frame.FrameId);
    if ( eventImg.getProperty( 'src' ) != thumbImg.getProperty( 'src' ) )
    {
        var eventImagePanel = $('eventImagePanel');

        if ( eventImagePanel.getStyle( 'display' ) != 'none' ) {
            var lastThumbImg = $('eventThumb'+eventImg.getProperty( 'alt' ));
            lastThumbImg.removeClass('selected');
            lastThumbImg.setOpacity( 1.0 );
        }

        eventImg.setProperties( {
            'class': frame.Type=='Alarm'?'alarm':'normal',
            'src': thumbImg.getProperty( 'src' ),
            'title': thumbImg.getProperty( 'title' ),
            'alt': thumbImg.getProperty( 'alt' ),
            'width': event.Width,
           'height': event.Height
        } );

        $j('div.eventImageBar').css( {'width': event.Width} );
        if ( frame.Type=='Alarm' ) $j('div.eventImageStats').removeClass( 'hidden' );
        else $j('div.eventImageStats').addClass( 'hidden' );
        thumbImg.addClass( 'selected' );
        thumbImg.setOpacity( 0.5 );

		if ( eventImagePanel.getStyle( 'display' ) == 'none' ) {
            eventImagePanel.setOpacity( 0 );
		    updateStillsSizes();
            eventImagePanel.setStyle( 'display', 'block' );
            new Fx.Tween( eventImagePanel, { duration: 500, transition: Fx.Transitions.Sine } ).start( 'opacity', 0, 1 );
        }

        $('eventImageNo').set( 'text', frame.FrameId );
        $j('input.prevImageBtn').attr('disabled', frame.FrameId==1?'disabled':'');
        $j('input.nextImageBtn').attr('disabled',frame.FrameId==event.Frames?'disabled':'');
    }
}
function hideEventImageComplete() {
    var thumbImg = $('eventThumb'+$j('#eventImage').attr( 'alt' ));
    thumbImg.removeClass('selected');
    thumbImg.setOpacity( 1.0 );
    $j('input.prevImageBtn').attr('disabled','disabled');
    $j('input.nextImageBtn').attr('disabled','disabled');
    $j('#eventImagePanel').css( 'display', 'none' );
    $j('div.eventImageStats').addClass( 'hidden' );
}
function hideEventImage() {
	if ( $j('#eventImagePanel').css( 'display' ) != 'none' )
        new Fx.Tween( $('eventImagePanel'), { duration: 500, transition: Fx.Transitions.Sine, onComplete: hideEventImageComplete } ).start( 'opacity', 1, 0 );
}
function resetEventStills() {
    hideEventImage();
    $('eventThumbs').empty();

    if ( true || !slider )
    {
        slider = new Slider( $('thumbsSlider'), $('thumbsKnob'), {
            /*steps: event.Frames,*/
            onChange: function( step )
            {
                if ( !step )
                    step = 0;
                var fid = parseInt((step * event.Frames)/this.options.steps);
                if ( fid < 1 )
                    fid = 1;
                else if ( fid > event.Frames )
                    fid = event.Frames;
                checkFrames( event.Id, fid );
                scroll.toElement( 'eventThumb'+fid );
            }
        } ).set( 0 );
    }
    if ( $('eventThumbs').getStyle( 'height' ).match( /^\d+/ ) < (parseInt(event.Height)+80) )
        $('eventThumbs').setStyle( 'height', (parseInt(event.Height)+80)+'px' );
}
function getFrameResponse( respObj, respText ) {

    if ( checkStreamForErrors( "getFrameResponse", respObj ) ) return;

    var frame = respObj.frameimage;

    if ( !event ) {
		console.error( "No event "+frame.EventId+" found" );
		return;
	}

	if ( !event['frames'] ) event['frames'] = new Hash();

	event['frames'][frame.FrameId] = frame;
    
	loadEventThumb( event, frame, respObj.loopback=="true" );
}


var frameReq = new Request.JSON( { url: thisUrl, method: 'post', timeout: AJAX_TIMEOUT, link: 'chain', onSuccess: getFrameResponse } );

function frameQuery( eventId, frameId, loadImage ) {


    var parms = "view=request&request=status&entity=frameimage&id[0]="+eventId+"&id[1]="+frameId+"&loopback="+loadImage;
    frameReq.send( parms );



    //var parms = "view=request&request=status&entity=frameimage&id[0]="+eventId+"&id[1]="+frameId+"&loopback="+loadImage;
    //var req = new Request.JSON( { url: thisUrl, method: 'post', timeout: AJAX_TIMEOUT, data: parms, onSuccess: getFrameResponse } );
    //req.send();
}

var currFrameId = null;

function checkFrames( eventId, frameId, loadImage ) {
    if ( !event )
    {
        console.error( "No event "+eventId+" found" );
        return;
    }

    if ( !event['frames'] )
        event['frames'] = new Hash();

	currFrameId = frameId;

    var loFid = frameId - frameBatch/2;
    if ( loFid < 1 )
        loFid = 1;
    var hiFid = loFid + (frameBatch-1);
    if ( hiFid > event.Frames )
        hiFid = event.Frames;

 //alert(eventId+' '+loFid+' '+hiFid);
    for ( var fid = loFid; fid <= hiFid; fid++ )
    {
        if ( !$('eventThumb'+fid) )
        {
            var img = new Element( 'img', { 'id': 'eventThumb'+fid, 'src': 'graphics/transparent.gif', 'alt': fid, 'class': 'placeholder' } );
   //alert((img));

	img.addEvent( 'click', function () { event['frames'][fid] = null; checkFrames( eventId, fid ) } );
            frameQuery( eventId, fid, loadImage && (fid == frameId) );
            var imgs = $('eventThumbs').getElements( 'img' );
            var injected = false;
            if ( fid < imgs.length )
            {
                img.injectBefore( imgs[fid-1] );
                injected = true;
            }
            else
            {
                injected = imgs.some(
                    function( thumbImg, index )
                    {
                        if ( parseInt(img.getProperty( 'alt' )) < parseInt(thumbImg.getProperty( 'alt' )) )
                        {
                            img.injectBefore( thumbImg );
                            return( true );
                        }
                        return( false );
                    }
                );
            }
            if ( !injected )
            {
                img.injectInside( $('eventThumbs') );
            }

            var scale = parseInt(img.getStyle('height'));
            img.setStyles( {
                'width': parseInt((event.Width*scale)/100),
                'height': parseInt((event.Height*scale)/100)
            } );
        }
        else if ( event['frames'][fid] )
        {
		//alert(loadImage+' '+fid+' '+frameId);
            if ( loadImage && (fid == frameId) )
            {
		//alert(event['frames'][fid]);
                loadEventImage( event, event['frames'][fid], loadImage );
            }
        }
    }
   $j('input.prevThumbsBtn').attr('disabled',(frameId==1)?'disabled':'');
    $j('input.nextThumbsBtn').attr('disabled',frameId==event.Frames?'disabled':'');
}
function locateImage( frameId, loadImage ) {
	if ( slider ) slider.fireEvent( 'tick', slider.toPosition( parseInt((frameId-1)*slider.options.steps/event.Frames) ));
	checkFrames( event.Id, frameId, loadImage );
	scroll.toElement( 'eventThumb'+frameId );
}
function prevImage() {  if ( currFrameId > 1 ) locateImage( parseInt(currFrameId)-1, true ); }
function nextImage() { if ( currFrameId < event.Frames ) locateImage( parseInt(currFrameId)+1, true ); }
function prevThumbs() {
    if ( currFrameId > 1 )
		locateImage( parseInt(currFrameId)>10?(parseInt(currFrameId)-10):1, $j('#eventImagePanel').css('display')!="none" );
}
function nextThumbs() {
    if ( currFrameId < event.Frames )
		locateImage( parseInt(currFrameId)<(event.Frames-10)?(parseInt(currFrameId)+10):event.Frames, $j('#eventImagePanel').css('display')!="none" );
}
function prevEvent() {
	if ( prevEventId ) {
		eventQuery( prevEventId );
		streamPrev( true );
	}
}
function nextEvent(){
	if ( nextEventId ) {
		eventQuery( nextEventId );
		streamNext( true );
	}
}



function getActResponse( respObj, respText ) {
	if ( checkStreamForErrors( "getActResponse", respObj ) ) return;
    //if ( respObj.refreshParent ) refreshParentWindow();

	if ( respObj.refreshEvent ) eventQuery( event.Id );
}
function actQuery( action, parms ) {
	var actParms = "view=request&request=event&id="+event.Id+"&action="+action;
	if ( parms != null ) actParms += "&"+Hash.toQueryString( parms );
	var actReq = new Request.JSON( { url: thisUrl, method: 'post', timeout: AJAX_TIMEOUT, data: actParms, onSuccess: getActResponse } );
	actReq.send();
}
//function deleteEvent() { actQuery( 'delete' ); streamNext( true ); }
function renameEvent() { actQuery( 'rename', { eventName: $j('#eventName').val() } ); }
//function editEvent() { createPopup( '?view=eventdetail&eid='+event.Id, 'zmEventDetail', 'eventdetail' ); }
//function exportEvent() { createPopup( '?view=export&eid='+event.Id, 'zmExport', 'export' ); }
//function archiveEvent() { actQuery( 'archive' ); }
//function unarchiveEvent() { actQuery( 'unarchive' ); }
function showEventFrames() { 
//createPopup( '?view=frames&eid='+event.Id, 'zmFrames', 'frames' ); 
	$j('div.eventStills, div.eventStream').addClass( 'hidden' );
	
	$j('a.linkstreamEvent,a.linkstillsEvent,a.linkvideoEvent').removeClass( 'selectedlink' );
	$j('a.linkframesEvent').addClass( 'selectedlink' );

    streamPause( true );

	
	$j('#eventmain-content').removeClass('hidden').empty().load('index.php?request=eventframes',{eid:event.Id});
}
function showEventFrame(fid) { 
//index.php?view=frame&eid=4714&fid=6
//makePopupLink( '?view=frame&eid='.$event['Id'].'&fid='.$frame['FrameId'], 'zmImage', array( 'image', $event['Width'], $event['Height'] ), $frame['FrameId'] ) ?></a></td>

	$j('#eventmain-content').removeClass('hidden').empty().load('index.php?request=eventframe',{eid:event.Id,fid:fid});
}
function showStream() {
	$j('a.linkframesEvent,a.linkstillsEvent,a.linkvideoEvent').removeClass( 'selectedlink' );
	$j('a.linkstreamEvent').addClass( 'selectedlink' );
	
	$j('div.eventStills, #eventmain-content').addClass( 'hidden' );
    $j('div.eventStream').removeClass( 'hidden' );
	
}
function showStills() {
	$j('a.linkframesEvent,a.linkstreamEvent,a.linkvideoEvent').removeClass( 'selectedlink' );
	$j('a.linkstillsEvent').addClass( 'selectedlink' );

    $j('div.eventStream, #eventmain-content').addClass( 'hidden' );
	$j('div.eventStills').removeClass( 'hidden' );
	
	
	
	//$j('div.stillsEvent').addClass( 'hidden' );
    //$j('div.streamEvent').removeClass( 'hidden' );
    streamPause( true );
    if ( !scroll )
    {
        scroll = new Fx.Scroll( 'eventThumbs', {
            wait: false,
            duration: 500,
            offset: { 'x': 0, 'y': 0 },
            transition: Fx.Transitions.Quad.easeInOut
            }
        );
    }
    resetEventStills();
    //$(window).addEvent( 'resize', updateStillsSizes );
//	alert('f');
}
function showFrameStats() {
    var fid = $('eventImageNo').get('text');
    createPopup( '?view=stats&eid='+event.Id+'&fid='+fid, 'zmStats', 'stats', event.Width, event.Height );
}
function showVideo() {
	//createPopup( '?view=video&eid='+event.Id, 'zmVideo', 'video', event.Width, event.Height );
	$j('div.eventStills, div.eventStream').addClass( 'hidden' );
	
	$j('a.linkstreamEvent,a.linkstillsEvent,a.linkframesEvent').removeClass( 'selectedlink' );
	$j('a.linkvideoEvent').addClass( 'selectedlink' );

    streamPause( true );

	
	$j('#eventmain-content').removeClass('hidden').empty().load('index.php?request=eventvideo',{eid:event.Id,width:event.Width,height:event.Height});
}
function viewVideo (index,width,height) {
	$j('#eventmain-content').removeClass('hidden').empty().load('index.php?request=eventvideo',{eid:event.Id,width:width,height:height,showIndex:index});
}
function deleteVideo( index ) {
	$j('#eventmain-content').removeClass('hidden').empty().load('index.php?request=eventvideo',{eid:event.Id,deleteIndex:index});
//window.location.replace( thisUrl+'?view='+currentView+'&eid='+eventId+'&deleteIndex='+index );
}
function downloadVideo( index ) {
    window.location.replace( thisUrl+'?view='+currentView+'&eid='+eventId+'&downloadIndex='+index );
}

var generateVideoTimer = null;

function generateVideoProgress() {
	var tickerText = $('videoProgressTicker').get('text');
	if ( tickerText.length < 1 || tickerText.length > 4 ) $('videoProgressTicker').set( 'text', '.' );
	else $('videoProgressTicker').appendText( '.' );
}
function generateVideoResponse( respObj, respText ) {
	response = 0;
	if(respObj != undefined && respObj.result=='Ok') response = 1;
	$j('#eventmain-content').removeClass('hidden').empty().load('index.php?request=eventvideo',{eid:event.Id,generated:response});
    //window.location.replace( thisUrl+'?view='+currentView+'&eid='+eventId+'&generated='+((respObj.result=='Ok')?1:0) );
}
function generateVideo( form ) {
	var parms = 'view=request&request=event&action=video';
	parms += '&'+$(form).toQueryString();
	var query = new Request.JSON( { url: thisUrl, method: 'post', data: parms, onComplete: generateVideoResponse } );
	query.send();
	$('videoProgress').removeClass( 'hidden' );
	$('videoProgress').setProperty( 'class', 'warnText' );
	$('videoProgressText').set( 'text', videoGenProgressString );
	generateVideoProgress();
	generateVideoTimer = generateVideoProgress.periodical( 500 );
}


function drawProgressBar() {
    var barWidth = 0;
	$j('#progressBar').addClass( 'invisible' );
	numberdivs = $j('#progressBar').children('div').size();
    var cellWidth = parseInt( event.Width/numberdivs );
	$j("#progressBar").children('div').each(function(index,data) {
		var child = $j(this);
		
		if ( index == 0 ) child.css( { 'left': barWidth, 'width': cellWidth, 'borderLeft': 0 } );
		else child.css( { 'left': barWidth, 'width': cellWidth } );
		
		var offset = parseInt((index*event.Length)/numberdivs);
		child.attr( 'title', '+'+secsToTime(offset)+'s' );
		child.unbind( 'click' );
		child.bind( 'click', function(){ streamSeek( offset ); } );
		barWidth += child.width();

	});
    //var cells = $('progressBar').getElements( 'div' );
    //var cellWidth = parseInt( event.Width/$$(cells).length );
    //$$(cells).forEach(
     //   function( cell, index )
     //   {
     //       if ( index == 0 )
     //           $(cell).setStyles( { 'left': barWidth, 'width': cellWidth, 'borderLeft': 0 } );
     //       else
     //           $(cell).setStyles( { 'left': barWidth, 'width': cellWidth } );
     //       var offset = parseInt((index*event.Length)/$$(cells).length);
     //       $(cell).setProperty( 'title', '+'+secsToTime(offset)+'s' );
     //       $(cell).removeEvent( 'click' );
     //       $(cell).addEvent( 'click', function(){ streamSeek( offset ); } );
     //       barWidth += $(cell).getCoordinates().width;
     //   }
    //);
    $j('#progressBar').css( {'width':barWidth} );
    $j('#progressBar').removeClass( 'invisible' );
}
function updateProgressBar() {
    if ( event && streamStatus )
    {
        var cells = $('progressBar').getElements( 'div' );
        var completeIndex = parseInt((($$(cells).length+1)*streamStatus.progress)/event.Length);
        $$(cells).forEach(
            function( cell, index )
            {
                if ( index < completeIndex )
                {
                    if ( !$(cell).hasClass( 'complete' ) )
                    {
                        $(cell).addClass( 'complete' );
                    }
                }
                else
                {
                    if ( $(cell).hasClass( 'complete' ) )
                    {
                        $(cell).removeClass( 'complete' );
                    }
                }
            }
        );
    }
}
function handleClick( event ) {
    var target = event.target;
    var x = event.page.x - $(target).getLeft();
    var y = event.page.y - $(target).getTop();
    
    if ( event.shift )
        streamPan( x, y );
    else
        streamZoomIn( x, y );
}
function initPage() {
    streamCmdTimer = streamQuery.delay( 250 );
    eventQuery.pass( event.Id ).delay( 500 );

    if ( canStreamNative ) {
	
		$j("div.imageFeed :first-child").click(function(){ handleClick.bindWithEvent( $j(this) )  });

		//var streamImg = $('imageFeed').getElement('img');
		//if ( !streamImg ) streamImg = $('imageFeed').getElement('object');
		//$(streamImg).addEvent( 'click', handleClick.bindWithEvent( $(streamImg) ) );
	}
}
















/* ===============================
=========== seyi_code ============ 
================================== */

function reloadme() {
	$j("#id-console-popup")
			.css( { display: 'block'} )
			.empty()
			.load('index.php?request=eventmain',{"eventlist":eventlist});
}
function loadsingleevent(id) {
	$j("#id-console-popup")
			.css( { display: 'block'} )
			.empty()
			.load('index.php?request=eventmain',{"eid":id,"eventlist":eventlist});
}

function toggleCheckbox( element, name ) {
    var form = element.form;
    var checked = element.checked;
    for (var i = 0; i < form.elements.length; i++)
        if (form.elements[i].name.indexOf(name) == 0) form.elements[i].checked = checked;
    form.editBtn.disabled = !checked;
    form.archiveBtn.disabled = unarchivedEvents?!checked:true;
    form.unarchiveBtn.disabled = archivedEvents?!checked:true;
    form.exportBtn.disabled = !checked;
    form.deleteBtn.disabled = !checked;

}
function configureButton( element, name ) {
    var form = element.form;
    var checked = element.checked;
    if ( !checked )
    {
        for (var i = 0; i < form.elements.length; i++)
        {
            if ( form.elements[i].name.indexOf(name) == 0)
            {
                if ( form.elements[i].checked )
                {
                    checked = true;
                    break;
                }
            }
        }
    }
    if ( !element.checked )
        form.toggleCheck.checked = false;
    //form.viewBtn.disabled = !checked;
    form.editBtn.disabled = !checked;
    form.archiveBtn.disabled = (!checked)||(!unarchivedEvents);
    form.unarchiveBtn.disabled = (!checked)||(!archivedEvents);
    form.exportBtn.disabled = !checked;
    form.deleteBtn.disabled = !checked;
	
//    form.archiveBtn.disabled = true;
//    form.unarchiveBtn.disabled = true;
//    form.deleteBtn.disabled = true;

}

function editEvents( element, name ){
    var form = element.form;
    var eids = new Array();
    for (var i = 0; i < form.elements.length; i++) {
        if (form.elements[i].name.indexOf(name) == 0) {
            if ( form.elements[i].checked ) eids[eids.length] = 'eids[]='+form.elements[i].value;
        }
    }
    createPopup( '?view=eventdetail&'+eids.join( '&' ), 'zmEventDetail', 'eventdetail' );
}
function exportEvents( element, name ) {
    var form = element.form;
    var eids = new Array();
    for (var i = 0; i < form.elements.length; i++) {
        if (form.elements[i].name.indexOf(name) == 0) {
            if ( form.elements[i].checked ) eids[eids.length] = 'eids[]='+form.elements[i].value;
        }
    }
    createPopup( '?view=export&'+eids.join( '&' ), 'zmExport', 'export' );
}
function deleteEvents( element, name ) {

    var form = element.form;
    var eids = new Array();
    for (var i = 0; i < form.elements.length; i++) {
        if (form.elements[i].checked && form.elements[i].name.indexOf(name) == 0) {
			var actParms = "view=request&request=event&id="+form.elements[i].value+"&action=delete";
			var actReq = new Request.JSON( { url: thisUrl, method: 'post', timeout: AJAX_TIMEOUT, data: actParms} );
			actReq.send();
        }
    }
	reloadme();

}
function archiveEvents( element, name ) {

    var form = element.form;
    var eids = new Array();
    for (var i = 0; i < form.elements.length; i++) {
        if (form.elements[i].checked && form.elements[i].name.indexOf(name) == 0) {
			var actParms = "view=request&request=event&id="+form.elements[i].value+"&action=archive";
			var actReq = new Request.JSON( { url: thisUrl, method: 'post', timeout: AJAX_TIMEOUT, data: actParms } );
			actReq.send();
        }
    }

}
function unarchiveEvents( element, name ) {

    var form = element.form;
    var eids = new Array();
    for (var i = 0; i < form.elements.length; i++) {
        if (form.elements[i].checked && form.elements[i].name.indexOf(name) == 0) {
			var actParms = "view=request&request=event&id="+form.elements[i].value+"&action=unarchive";
			var actReq = new Request.JSON( { url: thisUrl, method: 'post', timeout: AJAX_TIMEOUT, data: actParms } );
			actReq.send();
        }
    }

}







// Kick everything off
window.addEvent( 'domready', initPage );
