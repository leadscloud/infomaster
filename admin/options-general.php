<?php
// 加载公共文件
include dirname(__FILE__).'/admin.php';
// 查询管理员信息
$_USER = user_current();
// 权限验证
current_user_can('option-general');
$referer = referer(PHP_FILE,false);

// 动作
$method = isset($_REQUEST['method'])?$_REQUEST['method']:null;

$posts_per_page = isset($_POST['posts_per_page'])?$_POST['posts_per_page']:10;
$username = isset($_USER['name'])?$_USER['name']:'System';
$tzstring = isset($_POST['system']['Timezone'])?$_POST['system']['Timezone']:C('System.Timezone');


if (validate_is_post()) {
	$options = $_POST;
	if(isset($options['system'])){
		foreach($options['system'] as $key=>$val){
			$options['System.'.$key] = $val;
		}
		unset($options['system']);
	}
	//保存时间偏移量
	if($tzstring){
		$allowed_zones = timezone_identifiers_list();
		if ( in_array( $tzstring, $allowed_zones) ) {
			$date_time_zone_selected = new DateTimeZone($tzstring);
			$tz_offset = timezone_offset_get($date_time_zone_selected, date_create());
			$options['System.gmt_offset'] = $tz_offset/3600;
		}
	}
    foreach($options as $k=>$v) {
        if (!strncasecmp($k,'eselect_',8)) {
            unset($options[$k]); continue;
        }
    }
	unset($options['referer'],$options['auction_site']);
	validate_check(array(
		array('posts_per_page',VALIDATE_EMPTY, '每页显示数不能为空。'),
		array('posts_per_page',VALIDATE_IS_NUMERIC,'每页显示数必须是数字。'),
    ));
	if (validate_is_ok()) {
		//cookie_set('language',$options['language'],365*86400);
		C($options);
	}
	ajax_success('设置已保存。', "InfoSYS.redirect('".$referer."');");
} else {
	// 标题
	system_head('title',  '一般设置');
	system_head('scripts',array('js/options'));
	
	include ADMIN_PATH.'/admin-header.php';
	echo '<div class="module-header">';
	echo   '<h3><i class="icon-wrench"></i> '.system_head('title').'</h3>';
	echo '</div>';
	echo '<div class="tabbable">';
	echo   '<ul class="nav nav-tabs">';
	echo     '<li class="active"><a href="#viewinfo" data-toggle="tab">界面设置</a></li>';
	echo     '<li><a href="#upversion" data-toggle="tab">升级</a></li>';
	echo   '</ul>';
	echo   '<div class="tab-content">';
	echo     '<div class="tab-pane active" id="viewinfo">';
	echo       '<form class="form-horizontal form-horizontal-small" id="updateViewInfo" onsubmit="return false">';
	if(current_user_can('ALL',false)) {
	echo	     '<div class="control-group control-group-mini">';
	echo		   '<label class="control-label" for="home">系统地址(URL)</label>';
	echo		   '<div class="controls">';
	echo		     '<input name="system[home]" class="span4" type="text" value="'.get_home_url().'">';
	echo		   '</div>';
	echo		 '</div>';
	echo	     '<div class="control-group control-group-mini">';
	echo		   '<label class="control-label" for="home">时区</label>';
	echo		   '<div class="controls">';
	echo		     '<select id="timezone_string" name="system[Timezone]">';
	echo 				timezone_choice($tzstring);
	echo             '</select> ';
	if ( C('System.Timezone') ){
	echo             '<span id="utc-time">'.sprintf('<abbr title="Coordinated Universal Time">UTC</abbr> 时间是 <code>%s</code>', gmdate("Y-m-d H:i:s", time())).'</span>';
	echo             '<span id="local-time">'.sprintf('本地时间是 <code>%1$s</code>', gmdate("Y-m-d H:i:s", current_time( 'timestamp' ))).'</span>';
	}
	//date_default_timezone_set('UTC');
	//echo gmdate( 'Y-m-d H:i:s',time()) . '  -- ';
	//echo date( 'Y-m-d H:i:s',time()) . '  -- ';
	//echo C( '.gmt_offset' );
	echo		   '</div>';
	echo		 '</div>';
	}
	echo         '<legend>询盘信息界面设置</legend>';
	echo	     '<div class="control-group control-group-mini">';
	echo		   '<label class="control-label" for="posts_per_page">界面字段显示</label>';
	list( $columns ) = get_column_info();
	$current_hide = get_hidden_columns();
	$count = 0;
	foreach ( $columns as $column_key => $column_display_name ) {
		if($count%7==0) echo '<div class="controls span2" style="margin-left:20px">';
		
		$checked ='';
		if ( in_array( $column_key, $current_hide ) )
			$checked = 'checked="checked"';
		if($column_key =='cb') 
			echo '<input type="hidden" name="columnshidden[]" value="cb">';
		else
			echo "<label class='checkbox'><input $checked name='columnshidden[]' type='checkbox' value='$column_key'> $column_display_name</label>";
		
		++$count;
		if($count%7==0) echo '</div>';
		
	}
	if($count%7)
	echo 				'</div>';
	echo		 '</div>';
	echo	     '<div class="control-group control-group-mini">';
	echo		   '<label class="control-label" for="posts_per_page">每页最多显示</label>';
	echo		   '<div class="controls">';
	echo		     '<input name="posts_per_page" class="input-mini" type="text" value="'.(C($username.'.posts_per_page')==null?10:C($username.'.posts_per_page')).'">';
	echo             '<span class="help-inline">个询盘信息</span>';
	echo		   '</div>';
	echo		 '</div>';
	if(current_user_can('ALL',false)) {
	echo         '<legend>SMTP/Mail 设置</legend>';
	$mailtype	= C('.mailtype');
	$smtphost	= C('.smtphost');
	$smtpport	= C('.smtpport')?C('.smtpport'):25;
	$smtpuser	= C('.smtpuser');
	$smtppass	= C('.smtppass');
	echo	     '<div class="control-group control-group-mini">';
	echo		   '<label class="control-label" for="mailtype">Mail类型</label>';
	echo		   '<div class="controls">';
	echo             '<select name="system[mailtype]" id="mailtype">';
	echo               '<option '.($mailtype=='php'?'selected="selected"':'').' value="php">PHP</option>';
	echo               '<option '.($mailtype=='smtp'?'selected="selected"':'').' value="smtp">SMTP</option>';									
	echo             '</select>';
	echo		   '</div>';
	echo		 '</div>';
	echo	     '<div class="control-group control-group-mini">';
	echo		   '<label class="control-label" for="smtphost">SMTP 服务器</label>';
	echo		   '<div class="controls">';
	echo		     '<input name="system[smtphost]" id="smtphost" class="input-large" type="text" value="'.$smtphost.'">';
	echo		   '</div>';
	echo		 '</div>';
	echo	     '<div class="control-group control-group-mini">';
	echo		   '<label class="control-label" for="smtpport">SMTP 端口</label>';
	echo		   '<div class="controls">';
	echo		     '<input name="system[smtpport]" id="smtpport" class="input-mini" type="text" value="'.$smtpport.'">';
	echo		   '</div>';
	echo		 '</div>';
	echo	     '<div class="control-group control-group-mini">';
	echo		   '<label class="control-label" for="smtpuser">SMTP 用户名</label>';
	echo		   '<div class="controls">';
	echo		     '<input name="system[smtpuser]" id="smtpuser" class="input-large" type="text" value="'.$smtpuser.'">';
	echo             '<span class="help-inline"><i class="icon-question-sign" data-toggle="tooltip" data-title="请填写全称,比如test@qq.com,否则可能会发送失败." data-original-title=""></i></span>';
	echo		   '</div>';
	echo		 '</div>';
	echo	     '<div class="control-group control-group-mini">';
	echo		   '<label class="control-label" for="smtppass">SMTP 密码</label>';
	echo		   '<div class="controls">';
	echo		     '<input name="system[smtppass]" id="smtppass" class="input-large" type="password" value="'.$smtppass.'">';
	echo		   '</div>';
	echo		 '</div>';
	}
	echo		 '<div class="control-group">';
	echo		   '<div class="controls">';
	echo		     '<button type="submit" class="btn"><span>保存设置</span></button>';
	echo		   '</div>';
	echo		 '</div>';
	echo       '</form>';
	echo     '</div>';

	echo     '<div class="tab-pane" id="upversion">';
	echo       '<table class="table table-condensed" style="width:350px">';
	echo         '<thead>';
	echo           '<tr>';
	echo             '<th colspan="2">当前版本</th>';
	echo           '</tr>';
	echo         '</thead>';
	echo         '<tbody>';
	echo           '<tr>';
	echo             '<td>版本</td>';
	echo             '<td>'.SYS_VERSION.'</td>';
	echo           '</tr>';
	echo           '<tr>';
	echo             '<td>发布日期</td>';
	echo             '<td>2013-05-02 18:00:00 CST</td>';
	echo           '</tr>';
	echo           '<tr>';
	echo             '<td>更新日志</td>';
	echo             '<td><a href="'.ROOT.'changelog.html" target="_blank">查看版本更新信息.</a></td>';
	echo           '</tr>';
	echo         '</tbody>';
	echo       '</table>';
	echo       '<div class="well well-small" style="width:350px">';
	echo         '<span>无更新!</span>';
	echo         '<p style="display: none;">';
	echo           '<button class="btn btn-small"></button>';
	echo         '</p>';
	echo       '</div>';
	echo     '</div>';
	echo   '</div>';
	echo '</div>';
	include ADMIN_PATH.'/admin-footer.php';
}
	    
?>