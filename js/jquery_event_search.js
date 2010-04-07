$(document).ready(function(){
 $('#sidebarHistory input').change(function() {
  $("#events").load("/skins/new/views/pagination_data.php?page=1&filter[terms][0][attr]=MonitorName&filter[terms][0][op]==&filter[terms][0][val]=" + this.id);
 });
});
