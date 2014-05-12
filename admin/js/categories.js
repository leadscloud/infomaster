function sort_list_init() {
	// 绑定提交事件
	$('#categories').actions();
	//绑定双击事件
	$('tr[id^=category]').dblclick(function(){
		var taxonomyid=$(this).attr('id').replace(/[^\d]+/,'');
		InfoSYS.redirect('categories.php?method=edit&taxonomyid='+taxonomyid);
	});
}

function sort_manage_init() {
    // 提交事件
    $('form#sortmanage').ajaxSubmit();
}
