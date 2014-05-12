function issue_list_init() {
}
function issue_manage_init() {
	$('form#issuemanage').ajaxSubmit();
	$('#close-issue').click(function(e){
		e.preventDefault();
		InfoSYS.confirm('你确定要关闭该问题吗?',function(r){
			console.log(r);
			if (r) {
				$.ajax({
					url: "ajax_calls.php?call=closeissue",
					type: "POST",
					data: { 'issueid': $('input[name=parent]').val()},
					dataType: "json",
					beforeSend: function() {	
					},
					success: function(data) {
						InfoSYS.alert(data['message']);
					}
				});
			}
		});
	});
}