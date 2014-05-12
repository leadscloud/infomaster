$('form').ajaxSubmit();
/*		Clear Error Log	*/
$("#clearErrLog").live("click", function(e) {
	button = $(this);
	button.attr('disabled', 'disabled').html('删除中..');
	$.ajax({
		url: "../ajax_calls.php",
		type: 'POST',
		dataType: "json",
		data: 'call=clearlog',
		success: function(data) {
			if (data.status) {
				$("#error-log textarea").html('已清空错误记录！');
				$("#error-log textarea").css('height','50px');
			}
			button.attr('disabled', false).html('清除错误记录');
		}
	});
	e.preventDefault();
});

$("#clearCache").click(function(e) {
	button = $(this);
	button.attr('disabled', 'disabled').html('删除中..');
	$.ajax({
		url: "../ajax_calls.php",
		type: 'POST',
		dataType: "json",
		data: 'call=clearcache',
		success: function(data) {
			if (data.status) {
				$("#clear-cache .well").prepend('<p class="text-success">缓存已清除。</p>');
			}else{
				$("#clear-cache .well").prepend('<p class="text-error">缓存删除失败，请重试！</p>');
			}
			button.attr('disabled', false).html('删除缓存');
		}
	});
	e.preventDefault();
});

$("#redetermine").click(function(e) {
	button = $(this);
	$('.redetermineMessage').text('正在重新判定所属人，请稍候...');
	button.attr('disabled', 'disabled');
	$.ajax({
		url: "../ajax_calls.php",
		type: 'POST',
		dataType: "json",
		data: 'call=redetermineurl',
		beforeSend: function(){
		},
		success: function(data) {
			if (data.status) {
				$('.redetermineMessage').text('重新判定完成.');
			}else{
				$('.redetermineMessage').text('重新判定失败.');
			}
			button.attr('disabled', false);
		}
	});
	e.preventDefault();
});

//history


