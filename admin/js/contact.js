function contact_list_init() {
	// 绑定提交事件
	$('#contactlist').actions();
	//绑定双击事件
	$('tr[id^=contact]').dblclick(function(){
		var contactid=$(this).attr('id').replace(/[^\d]+/,'');
		InfoSYS.redirect('contact.php?method=edit&id='+contactid);
	});
    
}
function contact_manage_init() {
	$('form#contactmanage').ajaxSubmit();
}