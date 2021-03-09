require(['jquery'], function($) {
    $('select#id_role_id').change(function() {
        var url = M.cfg.wwwroot + '/mod/smartquest/ajax/get_users_role.php';
        var roleid = $(this).val();
        var courseid = $(this).data('courseid');

        $.ajax({
            url: url,
            type: 'POST',
            data: {
                'roleid': roleid,
                'courseid': courseid
            },
            dataType: 'json',
            success: function(data) {
                var users_select = $('select#id_user_id');
                users_select.empty();
                $.each(data, function(key, value) {
                    users_select.append($('<option></option>').attr('value', key).text(value));
                })
            },
            error: function(xhr) {
                console.log(xhr);
            }
        });
    })
	
	var body = $("body#page-mod-evaluation-mod");
	if(body.length && !$(body).is('[class*="cmid"]')) {
			$("select#id_visible").val(0);
	};
	
	$("table.question_rate td.raterow").click(function() {
		$(this).find('input:radio').attr('checked', true);
	});
	
	$("table.indicatortable td.relevantraterow").click(function() {
		$(this).find('input:radio').attr('checked', true);
	});
	
	$("table.relevanttable td.relevantraterow").click(function() {
		$(this).find('input:radio').attr('checked', true);
	});

    $('input[type="radio"][value=-1]').bind('deselect', function() {
        $('input[type="text"][name="nr'+$(this).attr('name')+'"]').css('visibility', 'hidden');
    });

    $('input[type="radio"]').bind('click', function () {
        if ($(this).attr('value') == -1) {
            $('input[type="text"][name="nr'+$(this).attr('name')+'"]').css("visibility", "visible");
        }
        $('input[name="'+$(this).attr('name')+'"]').not($(this)).trigger('deselect');
    });
})
