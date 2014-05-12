$(document).ready(function(){
	$('input[type=checkbox]').click(function(){
		if(this.checked){
			$('.alert-remember').show();
		}else{
			$('.alert-remember').hide();
		}
	});
	document.cookie = "cookieid=1; expires=60";
    var result = document.cookie.indexOf("cookieid=") != -1;
	if (!result) {
		$('#alertNoCookie').show();
	}

	$('form[id!="loginform"]').each(function(callback){
		var _this = $(this);
		_this.submit(function(){
			var email = $('input[type=email]',this).val();
			if(email=='') {
				$('.alertmsg').text('邮箱不能为空.').removeClass('blue').addClass('red').addClass('alert');
				return false;
			}

			var button = $('button',this).attr('disabled',true);
			// get action url
            var url = _this.attr('action'); if (url==''||typeof url=='undefined') { url = self.location.href; }
			//console.log(url);
			
			// ajax submit
			$.ajax({
				cache: false, url: url, dataType:'json',
				type: _this.attr('method') && _this.attr('method').toUpperCase() || 'POST',
				data: _this.serializeArray(),
				success: function(data, status, xhr){
					//console.log(data, status, xhr);

					if(status == "success") {
						var code = xhr.getResponseHeader('X-InfoMaster-Code');
						switch (code) {
							case 'Validate':
								var s = '<ul class="unstyled">';
								$.each(data,function(i){
									s+= '<li>' + this.text + '</li>';
								});
								s+= '</ul>';
								$('.alertmsg').html(s).addClass('alert');
								break;
							case 'Success':
								$('.alertmsg').html(data).addClass('alert alert-success');
								break;
							case 'Error': case 'Alert':
								$('.alertmsg').html(data).addClass('alert alert-error');
								break;
						}
						
					}

				},
				error: function(e){
					console.log('意外的错误发生:',e.responseText,e);
				},
				complete: function(){
					button.attr('disabled',false); 
				}
			});
			return false;
		});
	});
});