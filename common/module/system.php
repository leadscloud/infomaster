<?php
defined('COM_PATH') or die('Restricted access!');

// 添加 CSS
func_add_callback('loader_add_css', array(
    'bootstrap'         => array('/common/css/bootstrap.min.css'),
    'bootstrap.responsive'             => array('/common/css/bootstrap-responsive.min.css'),
	'bootstrap.google'	=> array('/common/css/bootstrap-google.css'),
    'common'            => array('/admin/css/style.css', array('bootstrap','bootstrap.responsive')),
    'style'             => array('/admin/css/style.css'),
	'responsive'		=> array('/common/css/admin-responsive.min.css'),
	
	'daterangepicker'   => array('/common/css/daterangepicker.css'),
	'farbtastic'        => array('/common/css/farbtastic.css'),
	'bootstrap.datepicker'  => array('/common/css/datepicker.css'),
	'daterangepicker'  	=> array('/common/css/daterangepicker.css'),
	'bootstrap.google'  => array('/common/bootstrap/css/bootstrap-google.css'),
	'chosen'  			=> array('/common/css/chosen.css'),
	'bootstrap-wizard'  => array('/common/css/bootstrap-wizard.css'),
	'bootstro'			=> array('/common/css/bootstro.min.css'),
	
	
    'admin'             => array('/admin/css/admin.css', array('style')),
    'login'             => array('/admin/css/login.css', array('style')),
    'install'           => array('/admin/css/install.css', array('style','bootstrap-wizard')),
    'cpanel'            => array('/admin/css/cpanel.css'),
    'user'              => array('/admin/css/user.css'),
    'model'             => array('/admin/css/model.css'),
    'post'              => array('/admin/css/post.css', array('xheditor.plugins')),
    'tools'           	=> array('/admin/css/tools.css'),
    'options'           => array('/admin/css/options.css'),
    'issue'           	=> array('/admin/css/issue.css'),
	'message'           => array('/admin/css/message.css'),
	'domain'            => array('/admin/css/domain.css'),
    'xheditor.plugins'  => array('/common/css/xheditor.plugins.css'),
    'statistics'        => array('/admin/css/statistics.css'),
));
// 添加js
func_add_callback('loader_add_js', array(
	'bootstrap'			=> array('/common/js/bootstrap.min.js'),
    'jquery'            => array('/common/js/jquery.js'),
    'jquery.extend'     => array('/common/js/jquery.extend.js'),
	'jquery.validate'	=> array('/common/js/jquery.validate.min.js'),
    'common'            => array('/admin/js/common.js', array('jquery','jquery.extend','bootstrap','scrollUp')),
	'date' 				=> array('/common/js/date.js'),
	'daterangepicker'  	=> array('/common/js/daterangepicker.js'),
	'datetimepicker'  	=> array('/common/js/bootstrap-datetimepicker.min.js'),
	'farbtastic'        => array('/common/js/farbtastic.js'),
	'jquery.flot'		=> array('/common/js/jquery.flot.js'),
	'jquery.flot.pie'	=> array('/common/js/jquery.flot.pie.js'),
	'jquery.flot.time'	=> array('/common/js/jquery.flot.time.js'),
	'jquery.flot.resize'=> array('/common/js/jquery.flot.resize.min.js'),
	'jquery.flot.categories'=> array('/common/js/jquery.flot.categories.js'),
	'jquery.peity'		=> array('/common/js/jquery.peity.min.js'),
	'jquery.chosen'		=> array('/common/js/chosen.jquery.min.js'),
	'bootstrap-wizard'  => array('/common/js/bootstrap-wizard.min.js'),
	'ZeroClipboard'		=> array('/common/js/ZeroClipboard.min.js'),
	'shortcuts'			=> array('/common/js/jquery.shortcuts.min.js'),
	'scrollUp'			=> array('/common/js/jquery.scrollUp.min.js'),
	'jquery.dataTables'	=> array('/common/js/jquery.dataTables.min.js'),
	'bootstro'			=> array('/common/js/bootstro.min.js'),
	'jvectormap'		=> array('/common/js/jquery-jvectormap-1.2.2.min.js'),
	'jvectormap.world'	=> array('/common/js/jquery-jvectormap-world-mill-cn.js'),
	'imgareaselect'		=> array('/common/js/jquery.imgareaselect.min.js'),
	'ajaxupload'		=> array('/common/js/ajaxupload.3.5.js'),
    'webcam'            => array('/common/js/webcam.js'),
    'jquery.form'       => array('/common/js/jquery.form.js'),

    'websocket'         => array('/admin/websocket/static/jquery.websocket.js'),
	
	
    'login'             => array('/admin/js/login.js'),
    'install'           => array('/admin/js/install.js', array('bootstrap-wizard')),
    'cpanel'            => array('/admin/js/cpanel.js'),
	'recent'            => array('/admin/js/recent.js'),
    'user'              => array('/admin/js/user.js'),
	'rule'              => array('/admin/js/rule.js'),
	'contact'           => array('/admin/js/contact.js'),
    'model'             => array('/admin/js/model.js'),
    'categories'        => array('/admin/js/categories.js'),
    'post'              => array('/admin/js/post.js'),
    'options'           => array('/admin/js/options.js'),
    'tools'             => array('/admin/js/tools.js'),
	'history'           => array('/admin/js/history.js'),
	'profile'           => array('/admin/js/profile.js'),
    'issue'             => array('/admin/js/issue.js'),
    'statistics'        => array('/admin/js/statistics.js'),
	'tsorts'             => array('/admin/js/tsort.js'),
	'message'           => array('/admin/js/message.js'),
	'domain'            => array('/admin/js/domain.js'),
    'comment'           => array('/admin/js/comment.js'),
    'chat'              => array('/admin/js/chat.js'),
    'xheditor'          => array('/common/editor/xheditor.js', array('xheditor.plugins')),
    'xheditor.plugins'  => array('/common/js/xheditor.plugins.js'),
));
// 系统权限
func_add_callback('system_purview_add', array(
    'cpanel' => array(
        '_LABEL_'           => '控制面板',
        'cpanel'           	=> '报表显示',
        'upgrade'           => '升级',
    ),
	'contact' => array(
        '_LABEL_'           => '联系人管理',
        'contact-new'          => '添加联系人',
        'contact-list'         => '查看联系人',
        'contact-edit'         => '编辑联系人',
        'contact-delete'       => '删除联系人',
    ),
    'posts' => array(
        '_LABEL_'           => '询盘信息',
        'categories'        => '分类（部门）',
        'post-new'          => '添加信息',
        'post-list'         => '询盘列表',
        'post-edit'         => '编辑信息',
        'post-delete'       => '删除信息',
		'data-export'		=> '数据导出',
		'data-import'		=> '数据导入',

		'post-view-all'		=> '查看所有询盘',
    ),
	'domains' => array(
        '_LABEL_'           => '域名管理',
        'domain-admin'      => '管理所有域名',
        'domain-new'        => '添加域名',
        'domain-list'       => '域名列表',
        'domain-edit'       => '编辑域名',
        'domain-state'      => '审核域名',
    ),
/*    'pages' => array(
        '_LABEL_'           => 'Pages',
        'page-list'         => 'List',
        'page-new'          => 'Add New',
        'page-edit'         => 'Edit',
        'page-delete'       => 'Delete',
    ),
    'models' => array(
        '_LABEL_'           => 'Models',
        'model-list'        => 'List',
        'model-new'         => 'Add New',
        'model-edit'        => 'Edit',
        'model-delete'      => 'Delete',
        'model-export'      => 'Export',
        'model-fields'      => 'Fields',
    ),
    'comments' => array(
        '_LABEL_'           => 'Comments'),
        'comment-list'      => 'List',
        'comment-state'     => 'Change State',
        'comment-reply'     => 'Reply comment',
        'comment-edit'      => 'Edit',
        'comment-delete'    => 'Delete',
    ),*/
    'users' => array(
        '_LABEL_'           => '用户管理',
        'user-list'         => '用户列表',
        'user-new'          => '添加用户',
        'user-edit'         => '编辑用户',
        'user-delete'       => '删除用户',
    ),
    /*'plugins' => array(
        '_LABEL_'           => 'Plugins',
        'plugin-list'       => 'List',
        'plugin-new'        => 'Add New',
        'plugin-delete'     => 'Delete',
    ),*/
    'settings' => array(
        '_LABEL_'           => '设置',
        'option-general'    => '一般设置',
        'option-posts'      => '询盘信息设置',
		'rule-list'      	=> '查看规则',
		'rule-new'			=> '新建规则',
        'rule-edit'         => '规则编辑',
        'rule-delete'       => '删除规则',
    )
));


if (!function_exists('system_category_guide')) :
/**
 * 生成导航
 *
 * @param int $sortid
 * @return string
 */
function system_category_guide($sortid) {
    if (empty($sortid)) return ; $result = '';
    if ($taxonomy = taxonomy_get($sortid)) {
        $result = '<a href="'.ROOT.$taxonomy['path'].'/" title="'.esc_html($taxonomy['name']).'">'.esc_html($taxonomy['name']).'</a>';
        if ($taxonomy['parent']) {
            $result = system_category_guide($taxonomy['parent'])." &gt;&gt; ".$result;
        }
    }
    return $result;
}
endif;


/**
 * 设置head变量
 *
 * @param string $key
 * @param mixed $value
 * @return mixed
 */
function system_head($key,$value=null) {
    static $head = array();
    // 赋值
    if (!is_null($value)) {
        if (isset($head[$key]) && is_array($value)) {
            $head[$key] = array_merge((array)$head[$key], $value);
        } else {
            $head[$key] = $value;
        }

    }
    return isset($head[$key])?$head[$key]:array();
}
function system_class($key,$value=null) {
    static $head = array();
    // 赋值
    if (!is_null($value)) {
        if (isset($head[$key]) && is_array($value)) {
            $head[$key] = array_merge((array)$head[$key], $value);
        } else {
            $head[$key] = $value;
        }

    }
    return isset($head[$key])?(' class="'.$head[$key].'"'):'';
}
/**
 * 添加权限
 *
 * @param array $purview
 * @return array
 */
function system_purview_add($purview) {
    global $LC_Purview;
    
    if (!$LC_Purview)
        $LC_Purview = array();

    if (is_array($purview)) {
        foreach ($purview as $key=>$val) {
            $LC_Purview[$key] = $val;
        }
    } else {
        $args = func_get_args();
        $key  = $args[0];
        $val  = $args[1];
        $LC_Purview[$key] = $val;

    }
    return $LC_Purview;
}
/**
 * 权限列表
 *
 * @return array
 */
function system_purview($data=null) {
    global $LC_Purview;
    $hl = '<div class="role-list">';
    foreach ((array) $LC_Purview as $k=>$pv) {
        $title = $pv['_LABEL_']; unset($pv['_LABEL_']);
        $roles = null; $parent_checked = ' checked="checked"';
        foreach ($pv as $sk=>$spv) {
            if ($data == 'ALL') {
                $checked = ' checked="checked"';
            } else {
                $checked = instr($sk, $data)?' checked="checked"':null;
            }
            $parent_checked = empty($checked)?'':$parent_checked;
        	$roles.= '<label class="checkbox inline"><input type="checkbox" name="roles[]" rel="'.$k.'" value="'.$sk.'"'.$checked.' /> '.$spv.'</label>';
        }
        $hl.= '<div class="control-group"><div class="controls"><label class="checkbox"><input type="checkbox" name="parent[]" class="parent-'.$k.'" value="'.$k.'"'.$parent_checked.' /> <strong>'.$title.'</strong></label> '.$roles.'</div></div>';
    }
    $hl.= '</div>';
    return $hl;
}
/**
 * 添加菜单
 *
 * @param string|array $menus
 * @return array
 */
function system_menu_add($menus) {
    global $LC_system_menus;

    if (!$LC_system_menus)
        $LC_system_menus = array();

    if (is_array($menus)) {
        foreach ($menus as $key=>$val) {
            $LC_system_menus[$key] = $val;
        }
    } else {
        $args = func_get_args();
        $key  = $args[0];
        $val  = $args[1];
        $LC_system_menus[$key] = $val;

    }
    return $LC_system_menus;
}
/**
 * 输出后台菜单
 *
 * @param  $menus
 * @return bool
 */
function system_menu($menus) {
    global $parent_file,$_USER,$LC_system_menus;
    // 获取管理员信息
    if (!isset($_USER)) $_USER = user_current(false);
    // 自动植入配置
    $is_first = true; $is_last = false;
    // 设置默认参数
    if (!empty($_SERVER['QUERY_STRING'])) {
        parse_str($_SERVER['QUERY_STRING'],$query);
        if (!isset($query['method'])) {
            $query = array_merge(array('method' => 'default'),$query);
        }
        $query = '?'.http_build_query($query);
    } else {
        $query = '?method=default';
    }
    if (!isset($parent_file)) {
    	$parent_file = PHP_FILE.$query;
    } else {
        //$parent_file = (strpos($parent_file,'?')!==false ? $parent_file : $parent_file.'?method=default');
		$parent_file = ADMIN.(strpos($parent_file,'?')!==false ? $parent_file : $parent_file.'?method=default');
    }
    // 插入菜单
    if ($LC_system_menus) {
        $i = $j = 0;
        foreach($menus as $k=>$v) {
            if ($j >= 2) break; $i++;
            if (is_string($v)) $j++;
        }
        reset($menus);
        array_ksplice($menus, $i-1, 0, '-');
        array_ksplice($menus, $i, 0, $LC_system_menus);
        unset($i, $j);
    }

    $menus_tree = array();
    // 预处理菜单
    while (list($k,$menu) = each($menus)) {
        if (is_array($menu)) {
            $submenus = array(); $is_expand = $has_submenu = false; $has_view = true;
            if (!empty($menu[3]) && is_array($menu[3])) {
                $has_submenu = true;
                foreach ($menu[3] as $href) {
					//print_r($href);
					if(is_array($href)){
                    	// 文件不存在，菜单也不能出现
                    	if (!is_file(ADMIN_PATH.'/'.parse_url($href[1],PHP_URL_PATH))&&$href[1]!='') continue;
						$href[1]   = ADMIN.$href[1];
						$url_query = strpos($href[1],'?')!==false?$href[1]:$href[1].'?method=default';
						$href[3]   = !strncasecmp($parent_file,$url_query,strlen($url_query))?true:false;
					}
					$is_expand = !strncasecmp($parent_file,$url_query,strlen($url_query))?true:$is_expand;
	
					// 子菜单需要权限才能访问，且用户要有权限
					if (isset($href[2]) && (instr($href[2],$_USER['roles']) || $_USER['roles']=='ALL' ||  instr($href[2], get_group_permissions(explode(',', $_USER['permissions']),$_USER['other_grps'])))) {
						$submenus[] = $href;
					}
					// 子菜单存在，不需要权限
					elseif (empty($href[2])) {
						$submenus[] = $href;
					}
					
                }
            }
            // 没有子菜单
            else {
                // 文件存在
                if (!is_file(ADMIN_PATH.'/'.parse_url($menu[1],PHP_URL_PATH))
                    || (isset($menu[3]) && ($_USER['roles']!='ALL' && !instr($menu[3],$_USER['roles'])))) {
                    $has_view = false;
                }
            }


            // 存在子菜单，并且子菜单不为空，或者没有子菜单
            if ($has_submenu && !empty($submenus) || $has_view && !$has_submenu) {
                $menu[1] = ADMIN.$menu[1];
                $current = !strncasecmp($parent_file,$menu[1],strlen($menu[1])) || $is_expand ? ' active open' : '';
                $expand  = empty($submenus) || empty($current) ? '' : ' expand';
                $menu = array(
                    'text' => $menu[0],
                    'link' => $menu[1],
                    'current'  => $current,
                    'expand'   => $expand,
                    'submenus' => $submenus,
                );
                $menus_tree[$k] = $menu;
            }
        } else {
            $menus_tree[] = $menu;
        }
    }
	

    // 循环所有的菜单
    while (list($k,$menu) = each($menus_tree)) {
        // 数组是菜单
        if (is_array($menu)) {
            // 展示子菜单
            if (!empty($menu['submenus'])) {
				echo '<li class="head'.$menu['current'].'">';
				echo '<a href="#" class="dropdown-toggle">'.$menu['text'].'<b class="arrow icon-angle-down"></b></a>';
                echo '<ul class="submenu">';             
                foreach ($menu['submenus'] as $submenu) {
					if(is_array($submenu)){
                    	$current = $submenu[3]?' active':null;
						if($submenu[0]==null)
							echo '<li class="divider"></li>';
						else
                    		echo '<li class="'.$current.'"><a href="'.$submenu[1].'">'.$submenu[0].'</a></li>';
					}else{
						echo '<li class="divider"><div class="separator"></div></li>';
					}
                }
                echo '</ul></li>';
            }
			else
			{
				echo '<li class="'.$menu['current'].'">';
				echo '<a href="'.$menu['link'].'">'.$menu['text'].'</a>';
				echo '<li>';
			}
			$separator = true;
        }
		// 否则是分隔符
        elseif($separator) {
            echo '<li class="divider"></li>';
            $separator = false;
        }
    }

    return true;
}
/**
 * 取得PHPINFO
 *
 * @param int $info
 * @return mixed|string
 */
function system_phpinfo($info = INFO_ALL) {
    /**
     * callback function to eventually add an extra space in passed <td class="v">...</td>
     * after a ";" or "@" char to let the browser split long lines nicely
     */
    function _system_phpinfo_v_callback($matches) {
        $matches[2] = preg_replace('/(?<!\s)([;@])(?!\s)/', "$1 ", $matches[2]);
        return $matches[1] . $matches[2] . $matches[3];
    }
    ob_start(); phpinfo($info);
    $output = preg_replace(array('/^.*<body[^>]*>/is', '/<\/body[^>]*>.*$/is'), '', ob_get_clean(), 1);

    $output = preg_replace('/width="[0-9]+"/i', 'width="100%"', $output);
    $output = str_replace('<table border="0" cellpadding="3" width="100%">', '<table class="phpinfo table">', $output);
    $output = str_replace('<hr />', '', $output);
    $output = str_replace('<tr class="h">', '<tr>', $output);
    $output = str_replace('<a name=', '<a id=', $output);
    $output = str_replace('<font', '<span', $output);
    $output = str_replace('</font', '</span', $output);
    $output = str_replace(',', ', ', $output);
    // match class "v" td cells an pass them to callback function
    return preg_replace_callback('%(<td class="v">)(.*?)(</td>)%i', '_system_phpinfo_v_callback', $output);
}