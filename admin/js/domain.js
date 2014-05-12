(function() {
    
var bar = $('.bar');
var percent = $('.percent');
var status = $('#status');
   
$('#collapseImport form').ajaxForm({
    beforeSend: function() {
        status.empty();
        var percentVal = '0%';
        bar.width(percentVal)
        percent.html(percentVal);
    },
    uploadProgress: function(event, position, total, percentComplete) {
        var percentVal = percentComplete + '%';
        bar.width(percentVal)
        percent.html(percentVal);
    },
    success: function() {
        var percentVal = '100%';
        bar.width(percentVal)
        percent.html(percentVal);
    },
    complete: function(xhr) {
        status.html(xhr.responseText);
    }
}); 

})();

function domain_list_init() {
	$('#domains').actions();
	//dbclick event
	$('tr[class^=domain]').dblclick(function(){
		var objid=$(this).attr('class').replace(/[^\d]+/,'');
		InfoSYS.redirect('domain.php?method=edit&id='+objid);
	});
}

function domain_manage_init() {
	//$('form#domainmanage').ajaxSubmit();
	$('form').ajaxSubmit();
	$('.chosen').chosen({no_results_text: "找不到匹配结果："});
}

function group_list_init() {
    // 绑定提交事件
    $('#grouplist').actions();
    //绑定双击事件
    $('tr[id^=group]').dblclick(function(){
        var groupid=$(this).attr('id').replace(/[^\d]+/,'');
        InfoSYS.redirect('domain-group.php?method=edit&groupid='+groupid);
    });
    
}

function group_manage_init(){
    $('form#groupmanage').ajaxSubmit();
}