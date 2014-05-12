<?php
// 文件名
$php_file = isset($php_file) ? $php_file : 'user.php';
// 加载公共文件
include dirname(__FILE__).'/admin.php';
// 查询管理员信息
$_USER = user_current();
// 标题
system_head('title',  '网址规则管理');

//echo preg_match('/(?<!\w)zhishaji.org.cn/i', 'zhishaji.org.cn');
//system_head('styles', array('css/user'));
system_head('scripts',array('js/rule'));
// 动作
$method = isset($_REQUEST['method'])?$_REQUEST['method']:null;

switch ($method) {
    // 强力插入
	case 'new':
        // 重置标题
	    system_head('title', '添加新规则');
        // 权限检查
	    current_user_can('rule-new');
	    // 添加JS事件
		system_head('scripts',array('js/bootstro'));
		system_head('styles',array('css/bootstro'));
	    system_head('loadevents','rule_manage_init');
	    include ADMIN_PATH.'/admin-header.php';
        // 显示页面
	    rule_manage_page('add');
        include ADMIN_PATH.'/admin-footer.php';
	    break;
	case 'edit':
	    // 所属
        $parent_file = 'rules.php';
        // 重置标题
	    system_head('title', '编辑规则');
	    // 权限检查
	    current_user_can('rule-edit');
	    // 添加JS事件
		system_head('scripts',array('js/bootstro'));
		system_head('styles',array('css/bootstro'));
	    system_head('loadevents','rule_manage_init');
	    include ADMIN_PATH.'/admin-header.php';
	    rule_manage_page('edit');
        include ADMIN_PATH.'/admin-footer.php';
	    break;
	// 保存用户
	case 'save':
	    $ruleid = isset($_POST['ruleid'])?$_POST['ruleid']:null;
	    current_user_can($ruleid?'rule-edit':'rule-new');
	    
        if (validate_is_post()) {
			$referer  = referer(PHP_FILE,false);
            $rulename	= isset($_POST['rulename'])?$_POST['rulename']:null;
            $type		= isset($_POST['type'])?$_POST['type']:null;
            $pattern	= isset($_POST['pattern'])?$_POST['pattern']:null;
            $result		= isset($_POST['result'])?$_POST['result']:null;
            $content	= isset($_POST['content'])?$_POST['content']:null;
            $state		= isset($_POST['activity'])?$_POST['activity']:null;
			$is_sub		= isset($_POST['sub_domain'])?1:0;
			
			$domain 	= isset($_POST['domain'])?$_POST['domain']:null;
			if(is_array($domain))
				$domain = implode(",", $domain);
            if ($ruleid) {
            	$rule = rule_get_byid($ruleid); $is_exist = true;
				if ($pattern != $rule['pattern']) {
					$rule2 = rule_get_bypattern($pattern,$type);
					$is_exist = ($rule2 && $rule2['type']==$type)?false:true;
            	}
            } else {
				$rule2 = rule_get_bypattern($pattern,$type);
				$is_exist = ($rule2 && $rule2['type']==$type)?false:true;
            }
			if(!current_user_can('ALL',false)){
				$result=$_USER['nickname'];
				validate_check(array('domain',VALIDATE_EMPTY,'域名不能为空。'),
					array('domain',VALIDATE_IS_URL,'请输入正确的网址。')
				);
				//检测当前域名是否已有匹配
				if($ruleid!=null){
					$match_result = check_url_matched(get_url_host($domain),$ruleid);
				}else{
					$match_result = check_url_matched(get_url_host($domain),null);
				}
				if($match_result!==null&&$match_result!==false){
					ajax_error($domain.' 已经有匹配结果: '.$match_result);
				}
				//检测输入的正则表达式
				$db = get_conn();
				$where = "WHERE `state`='enabled'";
				if($ruleid!=null){
					$where .= " AND `ruleid`<>$ruleid AND `domain`<>''";
				}
				$rs = $db->query("SELECT `domain` FROM `#@_rule` {$where} ;");
				$match_is_exist = false;
				$matched = null;
				while($results = $db->fetch($rs)){
					//使用当前规则检测所有网址
					foreach($pattern as $regex) {
						if (@preg_match($regex, get_url_host($results['domain']))) {
							$match_is_exist = true;
						}else{
							$match_is_exist = false;
						}
					}
					if($match_is_exist){
						$matched = $results['domain'];
						ajax_error('当前规则匹配其它域名: '.$matched);
						break;
					}
				}
				//当前规则匹配当前域名
				$is_matched = true;
				foreach($pattern as $regex) {
					if (@preg_match($regex, get_url_host($domain))==false) {
						$is_matched = false;
					}
				}
				if(!$is_matched)
					ajax_error('当前规则不匹配当前域名');
			}
			//验证正则表达式是否正确
			foreach($pattern as $regex) {
				if (@preg_match($regex, 'testurl')===false) {
					ajax_error('你输入的正则表达式有错误!');
				}
			}
            // 验证
            validate_check(array(
                // 不能为空
                array('rulename',VALIDATE_EMPTY,'规则名称不能为空。'),
				array('type',VALIDATE_EMPTY,'规则类型不能为空。'),
				array('result',VALIDATE_EMPTY,'规则匹配结果不能为空。'),
                // 规则长度必须是2-255个字符
                //array('pattern',VALIDATE_LENGTH,The username field length must be %d-%d characters.,2,255),
                // 规则已存在
                //array('pattern',$is_exist,'The pattern already exists.'),	
            ));
			
			if(!$is_exist) {
				//ajax_alert('此类型下已有相同规则.');
			}
            // 验证通过
            if (validate_is_ok()) {
                $rulename = esc_html($rulename);
				
                $rule_info = array(
                    'name'		=> esc_html($rulename),
					'type'		=> esc_html($type),
                    'content'	=> esc_html($content),
					'pattern'	=> serialize($pattern),
                    'result'	=> esc_html($result),
					'domain'	=> $domain,
					'subdomain' => $is_sub
                );
                // 编辑
                if ($ruleid) {
					$rule_info['edittime'] = date('Y-m-d H:i:s',time());
					$rule_info['state']    = $state==null?'disabled':'enabled';
                    rule_edit($ruleid,$rule_info);
                    ajax_success('规则更新成功。',"InfoSYS.redirect('".$referer."');");
                } 
                // 强力插入
                else {
					$rule_info = array_merge($rule_info,array(
						'datetime' => date('Y-m-d H:i:s',time()),
                        'edittime' => date('Y-m-d H:i:s',time())
                    ));
                    rule_add($rule_info);
                    ajax_success('规则添加成功。',"InfoSYS.redirect('".referer()."');");
                }
            }
        }
	    break;
	case 'delete':
		$listids = isset($_POST['listids'])?$_POST['listids']:null;
	    if (empty($listids)) {
	    	ajax_error('你没有选择任何项目。');
	    }
		current_user_can('rule-delete');
		foreach ($listids as $ruleid) {
			rule_delete($ruleid);
		}
		ajax_success('规则已删除。',"InfoSYS.redirect('".referer()."');");
		break;
	//导出域名数据
	case 'export':
		export_domain("网站所属人");
		break;
	default:
	//echo determine_url('http://www.so.com','搜索引擎来源');
	//echo preg_match('/^((?!soso).)*$/i', 'http://www.soso.com');
	    current_user_can('rule-list');
		system_head('scripts',array('js/jquery.dataTables'));
	    system_head('loadevents','rule_list_init');
		$search   = isset($_REQUEST['query'])?$_REQUEST['query']:'';
		$search	  = esc_html(trim($search));
		$query    = array('page' => '$');
		$conditions = array();
		$where = "WHERE 1";
		$is_grp_admin = isset($_USER['GroupAdmin'])?$_USER['GroupAdmin']==0:false;
		if ($search) {
			$query['query'] = $search;
			$fields = array('name','pattern','type','result','content','domain');
			foreach($fields as $field) {
				$conditions[] = sprintf("BINARY UCASE(`r`.`%s`) LIKE UCASE('%%%%%s%%%%')",$field,esc_sql($search));
			}
            $where.= ' AND ('.implode(' OR ', $conditions).')';
            if(!current_user_can('ALL',false)){
            	$nickname = $_USER['nickname'];
				$where.= " AND `r`.`result`='{$nickname}'";
				//默认为网站所属人
				$where.= " AND `r`.`type`='网站所属人'";
            }
			//SEO技术人员
			/*
			if($_USER['usergroup']=='SEO技术人员'&&!$is_grp_admin){
				$nickname = $_USER['nickname'];
				$where.= " AND `r`.`result`='{$nickname}'";
			}
			if($_USER['usergroup']=='SEO技术人员'){
				$where.= " AND `r`.`type`='网站所属人'";
			}
			*/
			$sql = "SELECT DISTINCT(`r`.`ruleid`) FROM `#@_rule` as `r` {$where} ORDER BY `r`.`ruleid` ASC";
		} else {
			if(!current_user_can('ALL',false)){
            	$nickname = $_USER['nickname'];
				$where.= " AND `result`='$nickname'";
				//默认为网站所属人
				$where.= " AND `type`='网站所属人'";
            }
			//SEO技术人员
			/*
			if($_USER['usergroup']=='SEO技术人员' && !$is_grp_admin){
				$nickname = $_USER['nickname'];
				$where.= " AND `result`='$nickname'";
			}
			if($_USER['usergroup']=='SEO技术人员'){
				$where.= " AND `type`='网站所属人'";
			}
			*/
			$sql = "SELECT `ruleid` FROM `#@_rule` {$where} ORDER BY `ruleid` ASC";
		}

		$result = pages_query($sql);
		// 分页地址
        $page_url   = PHP_FILE.'?'.http_build_query($query);
        include ADMIN_PATH.'/admin-header.php';
		
		echo '<div class="module-header">';
		echo	'<h3><i class="icon-magic"></i> 网址正则匹配</h3>';
		echo '</div>';
		
		if(!current_user_can('ALL',false)){
			echo '<div class="alert alert-info fade in"><button type="button" class="close" data-dismiss="alert">×</button> ';
			echo   '<i class="icon-info-sign"></i> 双击每行进入编辑模式. 查看所有人的域名信息,请移步到 <a href="'.ROOT.'pages/" target="_blank">这儿<i class="icon-external-link"></i></a> , 如果你第一次登陆,请先把默认添加的规则修改或删除,一个域名只对应一个规则.';
			echo '</div>';
		}
		
		echo '<div id="userlist">';
		table_nav('top',$page_url);
		//echo	'<form action="'.PHP_FILE.'?method=bulk" method="post" name="sortlist" id="sortlist">';
		echo 		'<table class="table table-striped table-hover table-bordered table-rules">';
		echo 			'<thead>';
		table_thead();
		echo			'</thead>';
		echo           	'<tfoot>';
        table_thead();
        echo           	'</tfoot>';
		echo			'<tbody>';
		if ($result) {
			while ($data = pages_fetch($result)) {
				$rule  = rule_get_byid($data['ruleid']);
				$href = PHP_FILE.'?method=edit&ruleid='.$rule['ruleid'];
				$patterns = implode(",", $rule['pattern']);
				echo           '<tr id="rule-'.$rule['ruleid'].'">';
				echo               '<td class="check-column"><input type="checkbox" name="listids[]" value="'.$rule['ruleid'].'" /></td>';
				echo               '<td> <a class="black" href="'.$href.'">'.$rule['name'].'</a></td>';
				//echo               '<td><i class="icon-magic"></i> <a class="black" href="'.$href.'" title="'.$patterns.'">'.mb_substr(clear_space(strip_tags($patterns)),0,70,'UTF-8').'</a></td>';
				echo               '<td><i class="icon-link"></i> <a class="black" href="'.$href.'" title="'.$rule['domain'].'">'.$rule['domain'].'</a></td>';
				echo               '<td> '.$rule['type'].'</td>';
				echo               '<td> '.$rule['result'].'</td>';
				echo               '<td>'.($rule['state']=='enabled'?'<span class="label label-success">可用</span>':'<span class="label label-important">不可用</span>').'</td>';
				echo               '<td><i class="icon-time"></i> '.$rule['datetime'].'</td>';
				echo           '</tr>';
			}
		} else {
            echo           '<tr><td colspan="7" class="tc">无记录！</td></tr>';
        }
		echo			'</tbody>';
		echo 		'</table>';
		//echo   '</form>';
		table_nav('bottom',$page_url);
		echo '</div>';
		
        include ADMIN_PATH.'/admin-footer.php';
        break;
}

/**
 * 批量操作
 *
 */
function table_nav($side='top',$url) {
	global $search;
	echo '<div class="table-nav clearfix">';
	echo 	'<div class="pull-left btn-group">';
	echo		'<button class="btn btn-small" onclick="javascript:;InfoSYS.redirect(\''.referer().'\');return false;"><i class="icon-arrow-up"></i> 返回</button> ';
	//echo		'<button class="btn btn-small" id="select" onclick="javascript:;return false;" data-toggle="button"><i class="icon-check"></i> 全选</button> ';
	echo		'<a class="btn btn-small" href="'.PHP_FILE.'?method=new"><i class="icon-plus"></i> 添加新规则</a> ';
	echo		'<button class="btn btn-small" name="delete" onclick="return false;"><i class="icon-trash"></i> 删除</button> ';
	echo		'<input type="hidden" name="referer" value="'.referer().'"> ';
	echo		'<button class="btn btn-small" name="refresh" onclick="javascript:;return false;"><i class="icon-refresh"></i> 刷新</button> ';
	
	echo	'</div>';
	
	echo    '<div class="btn-group">';
	echo	  '<a data-toggle="tooltip" data-original-title="导出为Excel (xls)格式" class="btn btn-small" id="ExportDomain" href="#"><i class="icon-download"></i> 导出所有域名</a>';
	echo    '</div>';
	
	if ($side == 'top') {
	echo 	'<div class="pull-right form-search btn-group"><form action="" method="get">';
	echo		'<div class="input-append"> <input class="span2 search-query" style="padding:2px 14px; height:21px;" name="query" type="text" value="'.esc_html($search).'"> <button class="btn  btn-small" type="submit" onclick="javascript:;">搜索</button></div></form>';
	
	echo 	'</div>';
	} else {
        echo pages_list($url);
    }
	echo '</div>';
}
/**
 * 表头
 *
 */
function table_thead() {
	echo '<tr>';
	echo     '<th style="width:20px" class="td-right"><input type="checkbox" name="select" value="all"></th>';
	echo     '<th>名称</th>';
	//echo     '<th>匹配规则</th>';
	echo     '<th>域名</th>';
	echo     '<th>类型</th>';
	echo     '<th>匹配结果</th>';
	echo     '<th>状态</th>';
	echo     '<th>添加日期</th>';
	echo '</tr>';
}

/**
 * 规则管理页面
 *
 * @param string $action
 */
function rule_manage_page($action) {
	global $php_file,$_USER;
    $referer = referer(PHP_FILE);
	
    $ruleid  = isset($_GET['ruleid'])?$_GET['ruleid']:0;
    if ($action!='add') {
    	$rule  = rule_get_byid($ruleid);
    }
    $rulename = isset($rule['name'])?$rule['name']:null;
    $pattern  = isset($rule['pattern'])?$rule['pattern']:null;
    $type     = isset($rule['type'])?$rule['type']:null;
    $result   = isset($rule['result'])?$rule['result']:null;
    $content  = isset($rule['content'])?$rule['content']:null;
    $state    = isset($rule['state'])?$rule['state']:null;
	$domain   = isset($rule['domain'])?$rule['domain']:null;
	$subdomain= isset($rule['subdomain'])?$rule['subdomain']:null;
	
	$is_grp_admin = isset($_USER['GroupAdmin'])?$_USER['GroupAdmin']==0:false;
	
	if(!current_user_can('ALL',false)){
		if($result==null){
			$result=$_USER['nickname'];
		}
	}
	
	
	echo	'<div class="module-header">';
	echo    	'<h3>'.($action=='add'?'<i class="icon-plus"></i> ':'<i class="icon-magic"></i> ').system_head('title').' <a href="#" class="btn btn-success hidden-phone hidden-tablet" id="start-intro">新手指引</a></h3>';
	echo	'</div>';
	
    echo '<div class="wrap form-horizontal">';
    echo   '<form action="'.PHP_FILE.'?method=save" method="post" name="rulemanage" id="rulemanage">';
    echo     '<fieldset>';
	
	echo 		'<div class="widget">';
	echo			'<div class="widget-header"><i class="icon-cog"></i><h3>基本设置</h3></div>';
	echo			'<div class="widget-content">';
	echo				'<div class="control-group"><label class="control-label" for="rulename">规则名</label>';
    echo					'<div class="controls">';
	echo						'<input class="bootstro" type="text" name="rulename" id="rulename" placeholder="起个名字吧" value="'.$rulename.'" data-bootstro-title="规则名称,必填的,名字最好统一使用 网站所属人" data-bootstro-placement="bottom"  data-bootstro-step="0">';
	echo						'<span class="help-inline"><i class="icon-question-sign" data-toggle="tooltip" data-title="为规则起个名字,必填项."></i></span>';
	echo					'</div>';
  	echo				'</div>';
	echo				'<div class="control-group"><label class="control-label" for="domain">域名</label>';
    echo					'<div class="controls input-append" style="display:block">';
	echo						'<input  class="bootstro" type="text" id="domain" value="'.$domain.'" name="domain" placeholder="请输入顶级域名,以http://开头" data-bootstro-title="输入的域名请以http://开头,如果是顶级域名不必带www." data-bootstro-placement="bottom"  data-bootstro-step="1">';
	echo						'<span class="add-on btn bootstro" data-bootstro-title="如果是二级域名请选择,每个二级域名都对应一个规则,除非所有的二级域名都属于你的." data-bootstro-placement="bottom"  data-bootstro-step="2"><label class="checkbox" style="font-weight:bold;font-size:13px;"><input type="checkbox" name="sub_domain" id="sub_domain" '.($subdomain==1?'checked="checked"':'').'>二级域名</label></span>';
	echo						'<button id="just-current" class="btn" data-toggle="button">仅匹配当前域名</button>';
	echo						'<button id="add-domain" class="btn">检测域名</button>';
	echo						'<span class="help-inline"><i class="icon-question-sign" data-toggle="tooltip" data-title="请输入你的顶级域名,如果是二级请选择是否二级域名.如果顶级域名下所有二级域名都属于你,可以不必再添加此域名下的二级域名."></i></span>';
	echo					'</div>';
  	echo				'</div>';
	echo				'<div class="control-group"><label class="control-label" for="pattern">正则表达式</label>';
    echo					'<div class="controls">';
	echo					  '<ul class="nav nav-tabs">';
	if($pattern) {
		foreach ($pattern as $i=>$field) {
			echo '<li '.($i==0?'class="active"':'').'><a href="#rule_'.$i.'" data-toggle="tab">规则'.(++$i).'</a></li>';
		}
	} else {
		echo '<li class="active"><a href="#rule_0" data-toggle="tab">规则1</a></li>';
	}
	echo						'<li class="bootstro" data-bootstro-title="添加或删除一个选中的规则,一般情况下是不需要添加的.除非一条规则无法确定你的网站.增加的规则与上一条规则是并且的关系." data-bootstro-placement="bottom"  data-bootstro-step="3">
											<button class="btn btn-small add-rules" type="button" style="margin-left:20px;margin-top:5px">
												<i class="icon-plus"></i> 添加规则
											</button>
											<button class="btn btn-small rm-rules" type="button" style="margin-left:10px;margin-top:5px">
												<i class="icon-minus"></i> 删除规则
											</button>
											<span class="help-inline"><i class="icon-question-sign" data-toggle="tooltip" data-title="如果你想修改,请查看下面关于正则表达式的详细说明." data-original-title=""></i></span>
										</li>';
	echo					  '</ul>';
	echo					  '<div class="tab-content bootstro" data-bootstro-title="系统会根据你的输入自动填写规则,正则表达式一般是不需要修改的,提交时如果出现问题并且你对正则表达式比较了解,可以修改.一条规则只能对应一个域名,不能再匹配其它域名." data-bootstro-placement="bottom"  data-bootstro-step="4">';
	if($pattern) {
		foreach ($pattern as $i=>$field) {
			echo '<div class="tab-pane '.($i==0?'active':'').'" id="rule_'.$i.'">';
			echo   '<textarea type="text" name="pattern[]" id="pattern" placeholder="输入正则表达式" class="span4" rows="4">'.$field.'</textarea>';
			echo '</div>';
		}
	} else {
		echo '<div class="tab-pane active" id="rule_0">';
		echo  '<textarea type="text" name="pattern[]" id="pattern" placeholder="输入正则表达式" class="span4" rows="4">'.$pattern.'</textarea>';
		echo '</div>';
	}
	echo					  '</div>';
	
	//echo						'<input type="text" name="pattern[]" id="pattern" placeholder="" value="'.$pattern.'">';
	echo					'</div>';
  	echo				'</div>';
	if(!current_user_can('ALL',false)){
		echo			'<input type="hidden" name="type" value="网站所属人">';
	}else{
		echo				'<div class="control-group"><label class="control-label" for="type">类型</label>';
		echo					'<div class="controls">';
		echo						'<select name="type">';
		echo							dropdown_types($type);
		echo						'</select>';
		echo					'</div>';
		echo				'</div>';
	}
	
	echo				'<div class="control-group"><label class="control-label" for="result">匹配后结果</label>';
    echo					'<div class="controls">';
	if(!current_user_can('ALL',false)){
		echo						'<input type="text" name="result" id="result" placeholder="" value="'.$result.'" readonly="readonly" class="bootstro" data-bootstro-title="匹配后结果应该是你的名字,请在你的个人资料里把昵称填写为你的中文名字全称,此处使用的是你的用户昵称." data-bootstro-placement="bottom"  data-bootstro-step="5">';
		echo						'<span class="help-inline"><i class="icon-question-sign" data-toggle="tooltip" data-title="这儿显示的是你的昵称,可以在个人资料里修改." data-original-title=""></i></span>';
	}else {
		echo						'<input type="text" name="result" id="result" placeholder="" value="'.$result.'" class="bootstro" data-bootstro-title="匹配后的结果可以是你的名字,或者其它匹配后你想显示的文字." data-bootstro-placement="bottom"  data-bootstro-step="5">';
	}
	echo					'</div>';
  	echo				'</div>';
	if($action!='add') {
		echo				'<div class="control-group"><label class="control-label" for="url">是否可用</label>';
		echo					'<div class="controls">';
		echo						'<input type="checkbox" name="activity" '.($state=='enabled'?'checked="checked"':'').' value="'.$state.'">';
		echo					'</div>';
		echo				'</div>';
	}
	echo				'<div class="control-group">';
    echo					'<div class="controls">';
	//echo 					  '<h5>正则表达式说明：</h5>';
	echo					  '<p><button type="button" id="collapsible" class="btn btn-danger bootstro" data-toggle="collapse" data-target="#details" data-on-hidden="展开正则表达式详细说明 <i class=\'icon-double-angle-down\'></i>" data-on-active="收起正则表达式详细说明 <i class=\'icon-double-angle-up\'></i>" data-bootstro-title="关于正则表达式的一些说明,不会者,请忽略." data-bootstro-placement="bottom"  data-bootstro-step="6">展开正则表达式详细说明 <i class="icon-double-angle-down"></i></button></p>';
	echo					  '<div id="details" class="collapse">';
	echo 					  '<p>使用的是preg_match匹配的。简单的示例: /google.com/i 指网址中包含google.com，一定要加上/.../i, i代表不区分大小写。具体见：http://www.php.net/manual/en/reference.pcre.pattern.modifiers.php</p>';
	echo					'<p><code>/(?&lt;!so)so.com/i</code> 将只匹配so.com而不会匹配soso.com, 另外此正则匹配目前不支持对二级目录的区分,仅支持对域名所属人的判定.</p>';
	echo					'<p>所有顶级域名请使用<code>'.esc_html('/(?<!\w)crusher.com/i').'</code>, 此处以crusher.com为例,它可能会匹配orecrusher.com, goldorecrusher.com,所以必须加上<code>'.esc_html('(?<!\w)').'</code>代表crusher.com前面不存在任何字母和数字. <code>'.esc_html('/(?<!\w)crusher.com/i').'</code> 匹配, www.crusher.com, crusher.com,  blog.crusher.com 但不匹配stonecrusher.com</p>';
	echo	'<p>如果你输入了多个规则是指必须网址匹配所有的这些规则. 例如: 如果你设置了规则1匹配shibang-china.com 规则2匹配stone-crusher.com,那此规则只能会匹配域名中同时带有shibang-china.com和stone-crusher.com的域名.</p>';
	echo					  '<ul class="unstyled">
				<li><code>(?:)</code>  - 非捕获模式. </li>
				<li><code>(?=)</code>  - 右预测等于模式 </li>
				<li><code>(?!)</code>  - 右预测不等于模式 </li>
				<li><code>(?<=)</code>  - 左预测等于模式 </li>
				<li><code>(?&lt;!)</code>  - 左预测不等于模式 </li>
				
				
				<li><code>^</code>  - 匹配字符串的开始 </li>
				<li><code>$</code>  - 匹配字符串的结束 </li>
                <li><code>\s</code> - 匹配空白符 </li>
                <li><code>\S</code> - 匹配所有除了空白符之处 </li>
                <li><code>\f</code> - 匹配换页符</li>
                <li><code>\n</code> - 匹配换行符</li>
                <li><code>\r</code> - 匹配回车</li>                    
                <li><code>\t</code> - 匹配制表符，Tab</li>
                <li><code>\v</code> - 匹配竖向制表符</li>
                <li><code>\d</code> - 匹配任何数字</li>
                <li><code>\D</code> - 匹配所有除了数字</li>
                <li><code>\w</code> - 匹配任何字母和数字字符，包括下划线. 等同于 [A-Za-z0-9_] </li>                
                <li><code>\W</code> - 匹配任何非单词字符. 等同于 [^A-Za-z0-9_] </li>
                
                </ul>';
	echo					'</div>';
	echo					'</div>';
  	echo				'</div>';
	
	
	
	echo			'</div>';
	echo 		'</div>';
	
    echo   '</fieldset>';
    echo   '<p class="submit">';
    if ($action=='add') {
        echo   '<button type="submit" class="btn btn-primary bootstro" data-bootstro-title="点击更新你的内容,点击确定后会回到规则列表页." data-bootstro-placement="bottom"  data-bootstro-step="7">添加规则</button>';
    } else {
        echo   '<button type="submit" class="btn btn-primary bootstro" data-bootstro-title="点击提交你的内容.点击确定后会回到规则列表页,你可以点击空白处,仍然会在当前页面,如果弹出的是成功提示,此时内容已经提交成功." data-bootstro-placement="bottom"  data-bootstro-step="7">更新规则</button><input type="hidden" name="ruleid" value="'.$ruleid.'" />';
		echo '<input type="hidden" name="referer" value="'.referer().'" />';
    }
    echo       '  <button type="button" class="btn" onclick="InfoSYS.redirect(\''.$referer.'\')">返回</button>';
    echo   '</p>';
    echo  '</form>';
    echo '</div>';
}
function dropdown_types($selected=null){
	$hl = '';
	//几种类型已确定，不可随意更改，如改，请在report.php文件里修改相应使用的名称
	$trees = array('搜索引擎来源','网站类型','网站所属人');
	foreach ($trees as $tree) {
		$sel  = $selected==$tree?' selected="selected"':null;
        $hl.= '<option value="'.$tree.'"'.$sel.'>'.$tree.'</option>';	
	}
	return $hl;
}
function display_ul_groups($grpid,$groups=array(),$trees=null) {
    static $func = null;
	$hl = ' ';
    //$hl = sprintf('<ul %s>',is_null($func) ? 'id="sortid" class="categories"' : 'class="children"');
    if (!$func) $func = __FUNCTION__;
    if ($trees === null) $trees = group_get_trees();
    foreach ($trees as $i=>$tree) {
        $checked = instr($tree['id'],$groups) && $grpid!=$tree['id'] ? ' checked="checked"' : '';
        $main_checked = $tree['id']==$grpid?' checked="checked"':'';
        //$hl.= sprintf('<input type="radio" name="sortid" value="%d"%s />',$tree['taxonomyid'],$main_checked);
        $hl.= sprintf('<label class="checkbox " for="group-%d">',$tree['id']);
        $hl.= sprintf('<input type="checkbox" id="group-%1$d" name="additional_groups[]" value="%1$d"%3$s />%2$s</label>',$tree['id'],$tree['name'],$checked);
    	if (isset($tree['subs'])) {
    		$hl.= $func($grpid,$groups,$tree['subs']);
    	}
        $hl.= '';
    }
    $hl.= '';
    return $hl;
}

/**
 * 检查网址是否存在其它匹配中
 * @param   type    $url   要检测的网址
 * @param   type    $ruleid    排除当前规则
 * @return  bool    
 */
function check_url_matched($url,$ruleid)
{
    $db = get_conn();
	$where = "WHERE `state`='enabled' AND `pattern`<>'' AND `type`='网站所属人'";
	if($ruleid!=null)
		$where.=" AND `ruleid`<>$ruleid";
	$rs = $db->query("SELECT `pattern`,`result` FROM `#@_rule` {$where} ;");
	while($results = $db->fetch($rs)){
		$is_match = true;
		$patterns = @unserialize($results['pattern']);
		if(!is_array($patterns)) return false;
		foreach($patterns as $regex) {
			if (@preg_match($regex, $url)==false) {
				$is_match = false;
			}
		}
		if($is_match)
			return $results['result'];
	}
	return null;
}
//得到项级域名
function get_top_level_domain($url)
{
  $domain = parse_url($url , PHP_URL_HOST);
  if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $list)) {
	  print_r($list);
    return substr($list['domain'], 0,strpos($list['domain'], "."));
  }
  return false;
}
function get_url_host($url){
	if($url==null) return false;
	$info = parse_url($url);
	if(!isset($info['host'])) return $url;
	return $info['host'];
}