
$(document).ready(function() {
	if(pv_status != '0'){
		$('#formDetailView input:button[name="Edit"]').remove();
		$('#formDetailView input:submit[name="Delete"]').remove();
	}
});