  $(document).ready(function(){
   var refresh = $("#inptRefresh").val();
   refresh = (refresh * 1000);
   $("#footer a").colorbox({iframe:true, width:'25%', height:'25%'});
   $("#monitors").load("skins/new/views/monitors.php", function(){post_load()});
   $("#tabs").tabs({

			tabTemplate: '<li><a href="#{href}">#{label}</a> <span class="ui-icon ui-icon-close">Remove Tab</span></li>'

});
   $("#add_widget").button();
   load_tabs();

   function load_tabs() {
    $.post("skins/new/includes/updateGroups.php?action=select", function(data) {
     saved_tabs = data.split(",");
     saved_tabs.pop();
     var x = saved_tabs.length;
     for (var i=0;i<x;i++){
      $("#tabs").tabs('add', '#tabs-'+saved_tabs[i], saved_tabs[i]);
     }
    });
   };

   function post_load() {
    $("a[rel='monitor']").colorbox({
     iframe:true,
     photo:true,
     preloading:false,
     current:'{current} of {total}',
     width:'85%',
     height:'75%'
    });
    
    $(".minimize").click(function() {
     $(this).parent().parent().find('.mon').toggle('blind');
    });

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
				$tab_title_input.focus();
			}
		});

		// addTab form: calls addTab function on submit and closes the dialog
		var $form = $('form',$dialog).submit(function() {
			addTab();
			$dialog.dialog('close');
			return false;
		});

		// actual addTab function: adds new tab using the title input from the form above
		function addTab() {
			tab_title = $tab_title_input.val();
			postIt();
		}

function postIt() {
var arysel  = [];
$("#selMonitors :selected").each(function(i, selected) {
 arysel[i] = $(selected).val();
});
var mids = arysel.toString();
$.post("skins/new/includes/updateGroups.php?groupName=" + tab_title + "&mids=" + mids + "&action=insert");
$("#tabs").tabs('add', '#tabs-'+tab_title, tab_title);
}

		// addTab button: just opens the dialog
		$('#add_tab')
			.button()
			.click(function() {
				$dialog.dialog('open');
			});

		// close icon: removing the tab on click
		// note: closable tabs gonna be an option in the future - see http://dev.jqueryui.com/ticket/3924
		$('#tabs span.ui-icon-close').click(function() {
			var index = $('li',$("#tabs")).index($(this).parent());
			$.post("skins/new/includes/updateGroups.php?action=delete&groupName=" + $(this).parent().find('a').text());
			$("#tabs").tabs('remove', index);
		});

   }

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

  $("#monitors").sortable({ opacity: 0.6, cursor: 'move', update: function() {
  var order = $(this).sortable("serialize") + '&action=sequence';
    $.post("skins/new/includes/updateSequence.php", order);
   }});

 });
