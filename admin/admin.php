<?php
// 定义管理后台路径
defined('ADMIN_PATH') or define('ADMIN_PATH', dirname(__FILE__));
// 加载公共文件
include ADMIN_PATH.'/../global.php';
// 后台的目录
define('ADMIN',ROOT.str_replace('\\','/',substr(ADMIN_PATH,strlen(ABS_PATH)+1)).'/');
// js css 加载类
include_file(COM_PATH.'/system/loader.php');
// 添加 CSS
func_add_callback('loader_add_css', language(), sprintf('/admin/css/%s.css', language()));
// 加载公共模块
include_modules();

// 检查是否已配置，设置安装界面时修改
/*defined('NO_REDIRECT') or define('NO_REDIRECT', false);
if (!NO_REDIRECT && (!is_file(ABS_PATH.'/config.php') || !installed())) {
    redirect(ADMIN.'install.php');
}*/
/**
 * 验证用户权限
 *
 * @param string $action
 * @param bool $is_redirect
 * @return bool
 */
function current_user_can($action,$is_redirect=true) {
    $result = false;
    $user = user_current(false);
	$roles = array();
    if (isset($user['Administrator']) && isset($user['roles'])) {
        // 超级管理员
        if($user['Administrator']=='Yes' && $user['roles']=='ALL') {
            $result = true;
        }
        // 普通管理员
        elseif ($user['Administrator']=='Yes') {
            if (instr($action, $user['roles']) || instr($action, get_group_permissions(explode(',', $user['permissions']),$user['other_grps']))) {
                $result = true;
            }
        }
    }

    // 权限不足
    if (!$result && $is_redirect) {
    	if (is_ajax()) {
            $text = '受限的访问, 请联系管理员.';
    		// 显示未登录的提示警告
            if (is_accept_json()) {
        	    ajax_alert($text);
            } else {
                echo $text; exit();
            }
    	} else {
            global $_USER;
    	    system_head('title', '限制访问');
    	    include ADMIN_PATH.'/admin-header.php';
    	    echo error_page('限制访问', '受限的访问, 请联系管理员.');
    	    include ADMIN_PATH.'/admin-footer.php';
    		exit();
    	}
    }
    return $result;
}
