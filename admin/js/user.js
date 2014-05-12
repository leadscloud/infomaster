function user_list_init() {
	// 绑定提交事件
	$('#userlist').actions();
	//绑定双击事件
	$('tr[id^=user]').dblclick(function(){
		var userid=$(this).attr('id').replace(/[^\d]+/,'');
		InfoSYS.redirect('user.php?method=edit&userid='+userid);
	});
    
}
function group_list_init() {
	// 绑定提交事件
	$('#grouplist').actions();
	//绑定双击事件
	$('tr[id^=group]').dblclick(function(){
		var groupid=$(this).attr('id').replace(/[^\d]+/,'');
		InfoSYS.redirect('user-group.php?method=edit&groupid='+groupid);
	});
    
}
function user_manage_init() {
    // 绑定全选事件
    $('button[rel=select]').click(function(){
        $('.role-list input[name^=roles]:checkbox').each(function(){
            this.checked = !this.checked; user_role_checked($(this).attr('rel'));
        });
    });
    // 密码强度验证
    $('#password1').val('').keyup( user_check_pass_strength );
    $('#password2').val('').keyup( user_check_pass_strength );
    // 初始化权限列表
    $('.role-list input[name^=parent]:checkbox').click(function(){
        $('.role-list input[name^=roles][rel=' + this.value + ']:checkbox').attr('checked',this.checked);
    });

    $('.role-list input[name^=roles]:checkbox').click(function(){
        user_role_checked($(this).attr('rel'));
    });
	$('form#usermanage').ajaxSubmit();
}
/**
 * 权限选择
 * 
 * @param rel
 */
function user_role_checked(rel) {
    var length   = $('.role-list input[rel=' + rel + ']').size(),
        cklength = $('.role-list input[rel=' + rel + ']:checked').size(),
        checked  = cklength >= length;
    $('.role-list input.parent-' + rel + ':checkbox').attr('checked',checked);
}

function user_profile_init(){
    // 密码强度验证
    $('#password1').val('').keyup( user_check_pass_strength );
    $('#password2').val('').keyup( user_check_pass_strength );
	$('form#profile').ajaxSubmit();
}
// 验证密码强弱
function user_check_pass_strength() {
    $('#pass-strength-result').check_pass_strength(
            $('#username').val(),
            $('#password1').val(),
            $('#password2').val()
    );
}

function group_manage_init(){
	
	$('tr[id^=group]').dblclick(function(){
		var groupid=$(this).attr('id').replace(/[^\d]+/,'');
		InfoSYS.redirect('user-group.php?method=edit&groupid='+groupid);
	});
	$('form#groupmanage').ajaxSubmit();
	/*		Colour Picker	*/
	var f = $.farbtastic('#groupmanage .colorpicker').linkTo('#groupmanage .groupcolour');
    $("#groupmanage .groupcolour").live("click", function() {
        $('#groupmanage .colorpicker').fadeIn();
        $('#groupmanage .colorpicker').parents('div').mouseleave(function() {
            $('#groupmanage .colorpicker').fadeOut();
        });
    })
	
	// 绑定全选事件
    $('button[rel=select]').click(function(){
        $('.role-list input[name^=roles]:checkbox').each(function(){
            this.checked = !this.checked; user_role_checked($(this).attr('rel'));
        });
    });
    // 初始化权限列表
    $('.role-list input[name^=parent]:checkbox').click(function(){
        $('.role-list input[name^=roles][rel=' + this.value + ']:checkbox').attr('checked',this.checked);
    });

    $('.role-list input[name^=roles]:checkbox').click(function(){
        user_role_checked($(this).attr('rel'));
    });
}