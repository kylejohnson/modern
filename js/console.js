$(document).ready(function(){
//var consoleRefreshTimeout = <?= 1000*ZM_WEB_REFRESH_MAIN ?>;

    $("#monitors").load("/skins/new/views/monitors.php");
    var refreshId = setInterval(function() {
       $("#monitors").load('/skins/new/views/monitors.php');
    }, consoleRefreshTimeout);

  $("#monitors").sortable({ opacity: 0.6, cursor: 'move', update: function() {
    var order = $(this).sortable("serialize") + '&action=sequence';
    $.post("skins/new/includes/updateSequence.php", order);
   }});

});
