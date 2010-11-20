$(document).ready(function(){
 var refresh = $("#inptRefresh").val();
 refresh = (refresh * 1000);
 $("#add_widget").button();

 $("#tabs").tabs({ // First, Initialize tabs and the tab template
  tabTemplate: '<li><a href="#{href}">#{label}</a> <span class="ui-icon ui-icon-close">Remove Tab</span></li>',
  load: function(event, ui){ // When the tab is loaded
   add_features();  // Add features
  },
  select: function(event, ui){ // When a tab is selected
   $("#monitors").remove(); // Remove old monitors 
  }
 });
 load_tabs(); // Then load the tabs into #tabs
 add_tab_click(); // Finally, setup the add tab feature

 function load_tabs() {
  $.post("skins/new/includes/updateGroups.php?action=select", function(data) { // Load tabs (groups)
   saved_tabs = data.split(",");
   saved_tabs.pop();
   var x = saved_tabs.length;
   for (var i=0;i<x;i++) {
    var name = saved_tabs[i];
    $("#tabs").tabs('add', 'skins/new/views/monitors.php?groupName='+name, name); // Add the tab
    }
   init_delete_tab();
   }
  );
 };

 function init_delete_tab() {
  // close icon: removing the tab on click
  // note: closable tabs gonna be an option in the future - see http://dev.jqueryui.com/ticket/3924
  $('#tabs .ui-icon-close').click(function() {
   var index = $('li',$("#tabs")).index($(this).parent());
   $.post("skins/new/includes/updateGroups.php?action=delete&groupName=" + $(this).parent().find('a').text());
   $("#tabs").tabs('remove', index);
  });
 }

 function add_features() {
  // This function is called whenever cameras are loaded (via the tab load event)
  // It setups up Minimize, Sortable, Colorbox features for the newly-loaded monitors
  $(".minimize").click(function() { // Minimize
   $(this).parent().parent().parent().find('.mon').toggle('blind');
  });

  $("#monitors").sortable({ opacity: 0.6, cursor: 'move', update: function() { // Sortable
   var order = $(this).sortable("serialize") + '&action=sequence';
   $.post("skins/new/includes/updateSequence.php", order);
  }});

  $("a[rel='monitor']").colorbox({ // Colorbox
   iframe:true,
   preloading:false,
   current:'{current} of {total}',
   width:'85%',
   height:'95%'
  });
 };  // END ADD_FEATURES //

 function add_tab_click() { // Call this last
  // This whole shabang is used for adding tabs.
  // We get the tab title and contents, setup the dialog box, create the functions
  // for adding the tab, deleting tab, closing and opening dialog box, etc.

  var $tab_title_input = $('#tab_title'), $tab_content_input = $('#tab_content');

  // modal dialog init: custom buttons and a "close" callback reseting the form inside
  var $dialog = $('#dialog').dialog({
   autoOpen: false,
   modal: true,
   buttons: {
    'Add': function() {
      addTab();
      $(this).dialog('close');
     },
    'Cancel': function() {
     $(this).dialog('close');
    }
   },
   open: function() {
    tab_title_input.focus();
   }
  }); // END DIALOG //

  // addTab form: calls addTab function on submit and closes the dialog
  var $form = $('form',$dialog).submit(function() {
  addTab();
  $dialog.dialog('close');
  return false;
 });

  // actual addTab function: adds new tab using the title input from the form above
  function addTab() {
   tab_title = $tab_title_input.val(); // groupName
   var arysel  = []; // An array for the monitorIds
   $("#selMonitors :selected").each(function(i, selected) { // For each selected MonitorId
    arysel[i] = $(selected).val(); // Put it into the array
   });
   var mids = arysel.toString(); // Make a comman-separated list of the select MonitorsIds
   $.post("skins/new/includes/updateGroups.php?groupName=" + tab_title + "&mids=" + mids + "&action=insert"); // Add the new Group
   $("#tabs").tabs('add', 'skins/new/views/monitors.php?groupName='+tab_title, tab_title); // Add the actual tab
   init_delete_tab();
  }

  // addTab button: just opens the dialog
  $('#add_tab').button().click(function() {
   $dialog.dialog('open');
  });

 } // END ADD_TAB_CLICK() //

  setInterval(function() {
   $("#monitors li").each(function() {
   var _this = $(this);
   $(".spinner",_this).html("<img width='15px' src='skins/new/graphics/spinner.gif' />");
   var mid = $(this).attr("id");
   mid = mid.split("_");
   $(".mon",this).load("skins/new/views/monitors.php?mid=" + mid[1] + " .mon", function () { 
    $(".spinner",_this).fadeOut('slow');
   });
  });
 }, refresh);


 });
