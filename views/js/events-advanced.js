
function clearValue( element, line ) {
    var form = element.form;
    var val = form.elements['filter[terms]['+line+'][val]'];
    val.value = '';
}

function submitToFilter( element, reload ) {
    var form = element.form;
    form.target = window.name;
    form.view.value = 'events-advanced';
    form.reload.value = reload;
    form.submit();
}


function submitToEvents( element ) {
    var form = element.form;
    if ( validateForm( form ) ) {
	
		query = $('#contentForm').serialize();
		parent.advancedsearch(query.replace('page=1',''));
		parent.$.fn.colorbox.close();

	/*obj = $('#contentForm').serializeArray();
	$(':input').each(function(i){ 
		//alert(this.name+' '+this.value);
	}) */
    }
}


function addTerm( element, line ) {
    var form = element.form;
    form.target = window.name;
    form.view.value = currentView;
    form.action.value = 'filter';
    form.subaction.value = 'addterm';
    form.line.value = line;
    form.submit();
}

function delTerm( element, line ) {
    var form = element.form;
    form.target = window.name;
    form.view.value = currentView;
    form.action.value = 'filter';
    form.subaction.value = 'delterm';
    form.line.value = line;
    form.submit();
}

