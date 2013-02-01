 
$(document).ready(function(){ 
z = 1; 
eid = $("#inptEID").val();

loadImages();

$("#btnPlay").click(function(){ // When the play button is clicked
console.log("Playing...");
 start = setInterval(function(){changeClass()}, 200);
 $("#btnPause").css("border", "1px solid #C5DBEC");
 $(this).css('border', "1px solid red");
});


$("#btnPause").button()
$("#btnPause").click(function(){
 clearInterval(start);
 $("#btnPlay").css("border", "1px solid #C5DBEC");
 $(this).css('border', "1px solid red");
});
$("#btnVideo").button();
$("#btnVideo").click(function() { // When btnVideo is clicked
 $("#spinner").html('<img src="skins/modern/graphics/spinner.gif" alt="spinner" />'); // Display the spinner
 $.post("skins/modern/includes/createVideo.php?eid="+eid+"&action=video&path="+path, function(data){ // Create the video file
  $("#spinner").html('<a href="'+path+data+'">'+data+'</a>'); // Display the link to the video file (or whatever info. is returned)
 });
});

$("#btnExport").button();
$("#btnExport").click(function() { // When btnVideo is clicked
	$("#spinner").html('<img src="skins/modern/graphics/spinner.gif" alt="spinner" />'); // Display the spinner
	$.post("skins/modern/includes/export_functions.php?eid="+eid, function(data){ // Create the video file
		//$("#spinner").html('<br /><a href="'+data+'">'+data+'</a>'); // Display the link to the video file (or whatever info. is returned)
		window.open('skins/modern/includes/download.php?file='+encodeURIComponent(data));
		$("#spinner").html('');
	});
});


$("#btnDelete").button();
$("#btnDelete").click(function(){
 $.post("skins/modern/includes/deleteEvent.php?eid=<?= $eid ?>");
 parent.$.fn.colorbox.close();
});

function loadImages() {
console.log("Loading images...")
var src = $('#img_0').attr('src');
console.log("Src: " + src);
var width = $('#img_0').css('width');
var height = $('#img_0').css('height');
var style = 'style="width:' + width + '; height:' + height + ';"';
var pos = src.lastIndexOf('/');
console.log("pos: " + pos);
path = src.substr(0,pos+1); // This is the path to the event image directory
console.log("path: " + path);
var imgs = new Array();

console.log("Getting files...");
$.post("skins/modern/includes/getFiles.php?path=" + path, function(data){ // Get the list of files
 imgs = data.split(" "); // Push the list into the array
 x = imgs.length -1; // Number of images
console.log("Got " +x+ " files...");
 y = 1; // Loaded image counter
console.log("Preloading images...");
 for (var i=1;i<x;i++){
  $.preLoadImages(imgs[i], function(){ // Preload the image, then,
   console.log("Preloaded: " + y);
   y++;
   var percent = (y / x);
   var result = Math.round(percent*100);
   $("#progress").html("Loading... " + result + "%");
   if (x == y){ // All images are loaded; enable btnPlay and btnStills
console.log("Finished preloading!");
    $("#btnPlay").removeAttr('disabled');
    $("#btnPlay").removeClass('ui-button-disabled ui-state-disabled');
    $("#btnStills").removeAttr('disabled');
    $("#btnStills").removeClass('ui-button-disabled ui-state-disabled');
   };
  });
//  $("#imageFeed").append('<a rel="event" href="'+imgs[i]+'"><img class="eventImageHide" id="img_' + i + '" src="' + imgs[i] + '" style="width:'+width+'; height:'+height+';"/></a>'); // This is the actual adding of the iamge of the page
  $("#imageFeed").append('<img class="eventImageHide" id="img_' + i + '" src="' + imgs[i] + '" style="width:'+width+'; height:'+height+';"/>'); // This is the actual adding of the iamge of the page
 };
});
}; // END loadImages END //

function changeClass() { // Change images from hidden to displayed and vise versa 
console.log("Changing class...");
 if (z<x){
  $("#img_" + (z-1)).attr("class", "eventImageHide");
  $("#img_" + z).attr("class", "eventImage");
  z++;
 }
};

});

