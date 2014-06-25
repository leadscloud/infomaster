function validateEmpty(val){
	val = val.replace(/\s/g, "");
	return (val==null || val=='')?false:true;
}
function is_empty(el){
	var val = el.val();
	ret = {
		status: true
	};
	if (!validateEmpty(val)) {
		ret.status = false;
		ret.msg = "字段不能留空。";
	}
	return ret;
}
// 改变数据库类型
function install_change_dbtype(type) {
	console.info(type);
    // sqlite
    if (type.substr(0,6)=='sqlite' || type.substr(0,10)=='pdo_sqlite') {
        $('input#dbuname,input#dbpwd,input#dbhost').parents('.control-group').hide();
		$('input#dbuname,input#dbpwd,input#dbhost').removeAttr('data-validate');
        var dbname = $('input#dbname').val();
        if (dbname.substr(-3) != '.db') {
            $('input#dbname').val($('input#dbname').attr('rel') + '.db');
        }
    }
    // mysql
    else {
        $('input#dbuname,input#dbpwd,input#dbhost').parents('.control-group').show();
		$('input#dbuname,input#dbpwd,input#dbhost').attr('data-validate','is_empty');
        var dbname = $('input#dbname').val();
        if (dbname.substr(-3) == '.db') {
            $('input#dbname').val('test');
        }
    }
}
$(function() {
	//$.fn.wizard.logging = true;
	
	$('button[rel=phpinfo]').click(function(){
		$(this).toggleClass('active');
        $('div.center').toggle();
    });
	$('button[rel=refresh]').click(function(){
        window.location.replace(self.location.href);
    });

	var wizard = $("#wizard-setup").wizard({
		width: 800,
		increaseHeight: 50,
		buttons: {nextText:'下一步',backText:'上一步',submitText:'提交',submittingText:'提交中...'},
		progressBarCurrent: true
	});
	
	$('input#dbuname,input#dbpwd,input#dbhost').parents('.control-group').hide();

	var dbtype = $('select#dbtype');
    if (dbtype.is('select')) {
        dbtype.change(function(){
            install_change_dbtype(this.value);
        });
        install_change_dbtype(dbtype.val());
    }


	//提交表单
	wizard.on("submit", function(wizard) {
		$.ajax({
			url: self.location.href,
			type: "POST",
			data: wizard.serialize()+'&setup=install',
			beforeSend: function(){
			},
			success: function(data, status, xhr) {
				//console.log(data, status, xhr);
				var code = xhr.getResponseHeader('X-InfoMaster-Code');
				switch (code) {
					case 'Validate':
						$.each(data,function(i){
							elm = $('#'+this.id);
							if (elm.length > 0 ) {
								elm.parents('.control-group').addClass('error');
								$('.wizard-success').append('<div class="alert alert-success">'+this.text+'</div>');
							}
						});
						break;
					case 'Success': case 'Error': case 'Alert':
						$('.alert-success').html(data);
						break;
					case 'Redirect':
						window.location.replace(data.Location);
						break;
				}
				wizard.submitSuccess(); // displays the success card
				wizard.hideButtons(); // hides the next and back buttons
				wizard.updateProgressBar(0); // sets the progress meter to 0
			},
			error: function(e) {
				wizard.submitError(); // display the error card
				wizard.hideButtons(); // hides the next and back buttons
			}
    	});
	});

	wizard.on("reset", function(wizard) {
		wizard.setSubtitle("");
		//wizard.el.find("#dbname").val("");
		//wizard.el.find("#new-server-name").val("");
	});

	wizard.el.find(".wizard-success .im-done").click(function() {
		wizard.reset().close();
	});

	wizard.el.find(".wizard-success .create-another-config").click(function() {
		wizard.reset();
	});

	$("#open-wizard").click(function() {
		wizard.show();
	});

	//wizard.show();
});