<?php
// 加载公共文件
include dirname(__FILE__).'/admin.php';
// 查询管理员信息
$_USER = user_current();
// 动作
$referer = referer(PHP_FILE,false);
// 保存我的配置

if (validate_is_post()) {
    $userid    = isset($_USER['userid'])?$_USER['userid']:null;
    $password  = isset($_POST['password1'])?$_POST['password1']:null;
    $password2 = isset($_POST['password2'])?$_POST['password2']:null;
    $nickname  = isset($_POST['nickname'])?$_POST['nickname']:null;
    $email     = isset($_POST['email'])?$_POST['email']:null;
    $url       = isset($_POST['url'])?$_POST['url']:null;
    $desc      = isset($_POST['description'])?$_POST['description']:null;
    // 验证email
    validate_check(array(
        array('email',VALIDATE_EMPTY,'请输入你的邮箱地址.'),
        array('email',VALIDATE_IS_EMAIL,'不是一个合法的邮箱地址.')
    ));
    // 验证密码
    if ($password || $password2) {
        validate_check('password1',VALIDATE_EQUAL,'你两次输入的密码不匹配,请重试.','password2');
    }

    // 验证通过
    if (validate_is_ok()) {
        $user_info = array(
            'url'  => esc_html($url),
            'mail' => esc_html($email),
            'nickname' => esc_html($nickname),
            'description' => esc_html($desc),
        );
        // 修改暗号
        if ($password) {
            if (isset($_USER['BanChangePassword']) && $_USER['BanChangePassword']=='Yes') {
                ajax_alert('禁止更改密码,请联系管理员.');
            } else {
                $user_info = array_merge($user_info,array(
                   'pass' => md5($password.$_USER['authcode'])
                ));
            }

        }
        user_edit($userid,$user_info);
        ajax_success('个人信息更新成功',"InfoSYS.redirect('".$referer."');");
    }
} else {
    // 标题
    system_head('title','个人资料');
    //system_head('styles', array('css/user'));
	system_head('scripts',array('js/imgareaselect'));
	system_head('scripts',array('js/ajaxupload'));
	system_head('scripts',array('js/webcam'));
    system_head('scripts',array('js/user'));
	system_head('scripts',array('js/profile'));
    system_head('loadevents','user_profile_init');
    $username = isset($_USER['name'])?$_USER['name']:null;
    $nickname = isset($_USER['nickname'])?$_USER['nickname']:null;
    $email    = isset($_USER['mail'])?$_USER['mail']:null;
    $url      = isset($_USER['url'])?$_USER['url']:null;
    $desc     = isset($_USER['description'])?$_USER['description']:null;
    include ADMIN_PATH.'/admin-header.php';
	echo	'<div class="module-header">';
	echo    	'<h3><i class="icon-user-md"></i> '.system_head('title').'</h3>';
	echo	'</div>';
    echo '<div class="wrap form-horizontal">';
    echo   '<form action="'.PHP_FILE.'?method=save" method="post" name="profile" id="profile">';
    echo     '<fieldset>';
	
	echo 		'<div class="widget">';
	echo			'<div class="widget-header"><i class="icon-cog"></i><h3>基本资料</h3></div>';
	echo			'<div class="widget-content">';
	echo              '<div class="row-fluid">';
	echo                '<div class="span5">';
	echo				'<div class="control-group"><label class="control-label" for="username">用户名</label>';
    echo					'<div class="controls">';
	echo						'<input type="text" name="username" id="username" placeholder="注册名（必填）" value="'.$username.'">';
	echo					'</div>';
  	echo				'</div>';
	echo				'<div class="control-group"><label class="control-label" for="nickname">昵称</label>';
    echo					'<div class="controls">';
	echo						'<input type="text" name="nickname" id="nickname" placeholder="昵称" value="'.$nickname.'">';
	echo					'</div>';
  	echo				'</div>';
	echo				'<div class="control-group"><label class="control-label" for="email">电子邮箱</label>';
    echo					'<div class="controls">';
	echo						'<input type="text" name="email" id="email" placeholder="电子邮箱（必填）" value="'.$email.'">';
	echo					'</div>';
  	echo				'</div>';
	echo				'<div class="control-group"><label class="control-label" for="url">网站</label>';
    echo					'<div class="controls">';
	echo						'<input type="text" name="url" id="url" placeholder="个人主页" value="'.$url.'">';
	echo					'</div>';
  	echo				'</div>';
	echo				'<div class="control-group"><label class="control-label" for="url">个人说明</label>';
    echo					'<div class="controls">';
	echo						'<textarea class="text" cols="70" rows="5" id="description" name="description">'.$desc.'</textarea>';
	echo					'</div>';
  	echo				'</div>';
	echo				'<div class="control-group"><label class="control-label" for="password1">密码</label>';
    echo					'<div class="controls">';
	echo						'<input type="password" name="password1" id="password1" placeholder="密码"> <br />  <br />';
	echo						'<input type="password" name="password2" id="password2" placeholder="密码（再输一次）">';
	echo					'</div>';
  	echo				'</div>';
	echo				'<div class="control-group">';
    echo					'<div class="controls">';
	echo						'<div class="control-group"><span id="pass-strength-result" class="label">密码强度指示器</span></div>';
	echo					'</div>';
  	echo				'</div>';
	echo				'</div><!--span-->';
	echo                '<div class="span7">';
	echo                  '<div class="thumbnail user-avatar pull-right" >';
	if(isset($_USER['avatar']))
		echo                '<img src="'.rel_root($_USER['avatar']).'">';
	echo                  '</div>';
	echo                  '<div class="btn-group" style="margin:8px 0;">';
	echo                    '<a type="button" class="btn btn-small      " id="uploadimage">上传图片</a>';
	echo                    '<a type="button" class="btn btn-small" onclick="webcamSnapshot()">捕捉摄像头</a>';
	echo                  '</div>';
	echo                  '<div id="upload_container" class="hidden">';
	echo                    '<div class="alert hidden"> <button type="button" class="close" data-dismiss="alert">×</button> <span></span> </div>';
	echo                    '<div class="crop"></div>';
	echo                  '</div>';
	echo                  '<div id="webcam_container" class="hidden">
			<div class="alert hidden"> <button type="button" class="close" data-dismiss="alert">&times;</button> <span></span> </div>
			<p id="webcam">
			</p>
			<div class="crop">
			</div>
			<p class="controls">
				<button type="button" class="btn btn-small cancel"> <i class="icon-remove"></i> Cancel</button>
				<button class="btn btn-primary btn-small" onClick="webcam.snap()"> <i class="icon-camera icon-white"></i> Take Snapshot</button>
			</p>
		</div>';
	echo                  '<input type="hidden" name="x1" value="" id="x1" />';
	echo                  '<input type="hidden" name="y1" value="" id="y1" />';
	echo                  '<input type="hidden" name="w" value="" id="w" />';
	echo                  '<input type="hidden" name="h" value="" id="h" />';
	echo				'</div><!--span-->';
	echo              '</div><!--row-->';
	echo			'</div>';
	echo 		'</div>';
	
    echo   '</fieldset>';
	echo   '<input type="hidden" name="referer" value="'.$referer.'" />';
    echo   '<p class="submit">';
    echo   '<button type="submit" class="btn btn-primary">更新资料</button>';

    echo       '  <button type="button" class="btn" onclick="InfoSYS.redirect(\''.$referer.'\')">返回</button>';
    echo   '</p>';
    echo  '</form>';
    echo '</div>';
    include ADMIN_PATH.'/admin-footer.php';
}


