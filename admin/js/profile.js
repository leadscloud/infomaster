//Selectors
var webcamC = '#webcam_container';
var uploadC = '#upload_container';

//Ajax file 
var AJAX = 'ajax_calls.php';

//Updating cropping x, y, width and height values
function updateSelection(img, selection) { 	
	$('#x1').val(selection.x1);
	$('#y1').val(selection.y1);
	$('#w').val(selection.width);
	$('#h').val(selection.height);
}

//Save cropped image
function saveImage(selector) {
	
	var alert = $(selector).find('.alert');
	var crop = $(selector).find('.crop');

	if (crop.html()!='') {
		var x1 = $('#x1').val(),
			y1 = $('#y1').val(),
			w = $('#w').val(),
			h = $('#h').val();

		//Check if the crop selection was made, otherwhise set default values
		if (w == "" || w == 0)
			w  = crop.find('img').width();
		if (h == "" || h == 0)
			h  = crop.find('img').height();
		if (x1 == "" || x1 == 0) x1 = Math.round((w-h)/2);
		if (y1 == "") y1 = 0;
		if (w > h) w = h;
		else if(h > w) h = w;
		
		//Hide alert
		alert.removeClass('alert-error').hide().children('span').text('');
		
		var data = {
			'x1' : x1,
			'y1' : y1,
			'w'  : w,
			'h'  : h,
			'call' : 'save_image'
			};

			//Show loading message
		alert.removeClass('alert-error').removeClass('hidden').show().children('span').text('保存图片中...');
		//Disable buttons
		crop.find('button').attr('disabled', 'disabled');
		//Apply image area select 
		crop.find('img').imgAreaSelect({ aspectRatio: '1:1', onSelectChange: updateSelection});

		//Ajax Request to save the cropped image
		$.ajax({
			type: 'POST',
			url: AJAX,
			data: data,
			dataType: 'json',
			beforeSend: function(){
			},
			success: function(response) {
				console.log(response);
				//If error exists or no message display erro message
				if (response.error!='' || response.msg=='')
					alert.addClass('alert-error').removeClass('hidden').show().children('span').text('意外的错误. 请重试一次.');
				else {
					//Else remove cropping
					crop.html('');
					removeSelection();

					//Success message
					alert.removeClass('alert-error').addClass('alert-success').removeClass('hidden').show().children('span').html('你上传的图片已经保存.. ');
					$('.user-avatar img').attr('src', response.msg);
				}
			},
			error: function(data) {
				console.log(data);
				//Ajax fails display image
				alert.addClass('alert-error').removeClass('hidden').show().children('span').text('意外的错误. 请重试一次.');
			},
			complete: function(){
				//Enable buttons
				crop.find('button').removeAttr('disabled');
			}
		});
	}
}

//Callback function when webcam image was uploaded
function webcamOnComplete(response) {
	//parse the json format into object
	var response = jQuery.parseJSON(response);
	if (response.error!='')
		alert.addClass('alert-error').removeClass('hidden').show().children('span').text('意外的错误. 请重试一次.');
	else {
		//Create the cropping html elements
		$(webcamC).find('.controls').hide();
		$(webcamC).find('#webcam').html('');
		$(webcamC).find('.crop').html('<h4>裁剪图像</h4> <div class="thumbnail"><img src="'+response.msg+'"/></div><p><button type="button" class="btn btn-small cancel"> <i class="icon-remove"></i> 取消</button> <button class="btn btn-primary btn-small" onClick="webcamSnapshot();"> <i class="icon-camera icon-white"></i> 新建快照</button> <button type="button" class="btn btn-primary btn-small" onclick="saveImage(\''+webcamC+'\')"> <i class="icon-ok-sign icon-white"></i> 保存图片</button></p>');
		$(webcamC).find('img').imgAreaSelect({ aspectRatio: '1:1', onSelectChange: updateSelection, maxWidth: 300, maxHeight:300 }); 
	}
	//webcam.reset();
}

//Create html for webcam
function webcamSnapshot() {
	removeSelection();
	$(uploadC).hide();
	$(webcamC).find('.crop').html('');
	webcam.set_api_url(AJAX);
	webcam.set_swf_url('assets/webcam/webcam.swf');
	webcam.set_shutter_sound(true, 'assets/webcam/shutter.mp3');  // play shutter click sound
	webcam.set_quality( 90 ); // JPEG quality (1 - 100)
	$(webcamC).find('#webcam').html( '<div class="thumbnail">'+ webcam.get_html(600, 450) +'</div>' );
	webcam.set_hook( 'onComplete', 'webcamOnComplete' );
	$(webcamC).removeClass('hidden').show();
	$(webcamC).find('.controls').removeClass('hidden').show();
	return false;
}

$(function(){
	
	//On click cancel remove crop or webcam 
	$(uploadC+','+webcamC).on('click', '.cancel', function() {
		$(uploadC+' .crop').html('');
		removeSelection();
		$(webcamC).hide();
		$('.alert').removeClass('alert-error').hide();
	});

	/*Image upload*/
	var alert = $(uploadC).find('.alert');
	//Upload button selector
	var btnUpload = $('#uploadimage');
	//Create new AjaxUpload
	new AjaxUpload(btnUpload, {
		action: AJAX,
		data: {'call': 'uploadimg'},
		name: 'uploadimage',
		responseType: 'json',
		onSubmit: function(file, ext){
			removeSelection();
			$(webcamC).hide();
			$(uploadC).removeClass('hidden').show();
			//Display a loding message
			alert.removeClass('alert-error').removeClass('hidden').show().children('span').text('上传图片中...');
		},
		onComplete: function(data, response){
			if(response.error!='' || response.msg=='') {
				if( response.error.ext )
					alert.addClass('alert-error').removeClass('hidden').show().children('span').text('File extension not allowed.' + response.error.ext );
				else if( response.error == 'big' )
					alert.addClass('alert-error').removeClass('hidden').show().children('span').text('The image file size is to big.');
				else alert.addClass('alert-error').removeClass('hidden').show().children('span').text('Unexpected Error.'+response.error);
			}
			else {
				alert.hide().children('span').text();
				$(uploadC).find('.crop').html('<h4>裁剪图像</h4> <div class="thumbnail"><img src="'+response.msg+'"/></div><p><button type="button" class="btn btn-small cancel"> <i class="icon-remove"></i> 取消</button> <button type="button" class="btn btn-primary btn-small" onclick="saveImage(\''+uploadC+'\')"><i class="icon-ok-sign icon-white"></i> 保存图片</button></p>');
				removeSelection();
				$(uploadC).find('img').imgAreaSelect({ aspectRatio: '1:1', onSelectChange: updateSelection, maxWidth: 300, maxHeight:300});  
			}
		}
	});
	

	//Simple hide on click 
	$('.alert').on('click', '[data-dismiss="alert"]', function(){
		$(this).parent().hide();
	});

});

//This function will remove the selection elements for cropping (those borders that you select)
function removeSelection(){
	$('.imgareaselect-outer, .imgareaselect-selection, .imgareaselect-border1, .imgareaselect-border2').remove();
}