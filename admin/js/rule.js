function rule_list_init() {
	// 绑定提交事件
	$('#userlist').actions();
	//绑定双击事件
	$('tr[id^=rule]').dblclick(function(){
		var ruleid=$(this).attr('id').replace(/[^\d]+/,'');
		InfoSYS.redirect('rules.php?method=edit&ruleid='+ruleid);
	});
}
function rule_manage_init() {
	$('form#rulemanage').ajaxSubmit();
	//
	$('.add-rules').click(function(e){
		 e.preventDefault();
		 var id = $(this).parent().prev().find('a').attr('href');
		 if(id!=undefined)
		 	id = id.substring(6,7);
		 else 
		 	id =-1;
		 id++;
		 $(this).parent().before('<li><a href="#rule_'+id+'" data-toggle="tab">规则'+(id+1)+'</a></li>');
		 $(this).closest('div').find('.tab-content').append('<div class="tab-pane" id="rule_'+id+'"><textarea type="text" name="pattern[]" class="span4" rows="4"></textarea></div>');
		 if( $(this).parent().parent().find('li').hasClass('active')==false ){
			 $(this).parent().prev().find('a').tab('show');
		 }
	});
	$('.rm-rules').click(function(e){
		e.preventDefault();
		$(this).parent().prevAll('li.active').remove();
		$(this).closest('div').find('.tab-content div.active').remove();
		$(this).parent().prev().find('a').tab('show');
	});

	//
	$('#add-domain').click(function(e){
		e.preventDefault();
		button = $(this);
		$.ajax({
			url: "ajax_calls.php?call=checkurl",
			type: "POST",
			data: { 'url': $('#domain').val(), 'type': '网站所属人'},
			dataType: "json",
			beforeSend: function() {
				button.html('<i class="icon-check-empty"></i>检测中');
			},
			success: function(data) {
				if(data==false) InfoSYS.alert('错误,你输入的网址可能不合规范.请以http://开头');
				if(data!=null) InfoSYS.alert($('#domain').val()+' 已经有匹配结果: '+data);
				else button.html('<i class="icon-ok"></i>正常');
				
			}
		});
	});
	//
	
	$('#domain').keyup( generate_regex );
	$('#domain').change( generate_regex );
	
	
	var $togglers = $('[data-toggle="collapse"]');
	$togglers.each(function() {
		var $this = $(this);
		var $collapsible = $($this.data('target'));
		$collapsible.on('hidden', function() {
			var text = $this.data('on-hidden');
			text && $this.html(text);
		}).on('shown', function() {
			var text = $this.data('on-active');
			text && $this.html(text);
		});
	});
	
	$('#collapsible').collapse();
	
	 $("#domain").change(function() {
        if (!/^http:\/\//.test(this.value)) {
            this.value = "http://" + this.value;
        }
    });
	
	
	
}
$('#sub_domain').change(function(){
    var c = this.checked ? generate_regex($('#domain').val(),true) : generate_regex($('#domain').val(),false);
});

function generate_regex(url,is_sub){
	var pattern;
	url = $('#domain').val();
	if(is_sub==null)
		is_sub = $('#sub_domain').is(':checked');
	if(url=='') return null;
	if (!url.match("^http")) {
		url = 'http://'+url;
	}
	var host = url.replace(/\/\s*$/,'').split('/');
	host =host[2];
	host = host.replace(/^www\./,'');
	host = host.replace(/^\s+|\s+$/g, '');
	///(?<!\w)crusher.com/i
	if(is_sub)
		pattern = '/^'+host+'$/i';
	else
		pattern = '/(?<!\\w|-)'+host+'$/i';
		//pattern = '/^'+host+'|^www.'+host+'/i';
	pattern = pattern.toLowerCase();
	$('#pattern').val(pattern);
	return pattern;
}

$('#just-current').click(function(e){
	e.preventDefault();
	$(this).hasClass('active')?generate_regex($('#domain').val(),false):generate_regex($('#domain').val(),true);
	
});
$("#start-intro").click(function(){
	bootstro.start(".bootstro", {
		nextButton: '<a class="btn btn-primary btn-mini bootstro-next-btn">下一步 »</a>',
		prevButton: '<a class="btn btn-primary btn-mini bootstro-prev-btn">上一步 «</a>',
		finishButton: '<a class="btn btn-mini btn-success bootstro-finish-btn"><i class="icon-ok" ></i> 好的,我已经完成所有教程.</a>',
		onComplete : function(params)
		{
			alert("Reached end of introduction with total " + (params.idx + 1)+ " slides");
		},
		onExit : function(params)
		{
			alert("Introduction stopped at slide #" + (params.idx + 1));
		},
	});    
});
//export data
$('#ExportDomain').click(function(){
	window.location='rules.php?method=export';
});