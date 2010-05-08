$(function() {
 $('#inptDateFrom').datepicker();
 $('#inptDateTo').datepicker();
 $('input:submit').button();

 // Exporting Event to Image //
$("#btnExport").button();
$("#btnExport").click(function() { // When btnExport is clicked
 var src = $(".ad-image img").attr('src');
 var pos = src.lastIndexOf('/');
 var path = src.substr(0,pos+1); // This is the path to the event image directory
 var eid = src.split('/');
 eid = eid[2]; // This is the event ID
 $("#export").html('<img src="skins/new/graphics/spinner.gif" alt="spinner" />');
 $.post("skins/new/includes/createVideo.php?eid=" + eid + "&action=video&path=" + path, function(data){ // Create the video file
  $("#export").html(data); // Display the link to the video file (or whatever info. is returned)
 });
});
 // Exporting Event to Image //
});
