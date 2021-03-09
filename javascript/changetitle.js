require(['jquery','local_datatablescrud/select2'], function($){
	$(document).ready(function() {
	$('head').append('<link rel="stylesheet" type="text/css" href=../../local/datatablescrud/style/select2.css>');	
	$('#aboutuserid').select2();
	});


    $('#aboutuserid').change(function () {
        $('.mod_smartquest_completepage > h4 > b > u').text($('#aboutuserid option:selected').text())
    });
});
