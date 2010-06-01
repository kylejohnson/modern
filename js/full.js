$(document).ready(function(){
 var url = location.search;
 $("#scale").change(function(){
  var scale = $(this).val()
  $("#imageFeed").load("/" + url + "&scale=" + scale + " #liveStream");
 });
});
