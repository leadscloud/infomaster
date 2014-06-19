<?php
// 定义管理后台路径
defined('ADMIN_PATH') or define('ADMIN_PATH',dirname(__FILE__));
// 禁止重复跳转
define('NO_REDIRECT',true);
// 加载公共文件
include ADMIN_PATH.'/admin.php';
// 检查系统是否已经安装
if (installed()) redirect(ADMIN);
// 系统需要安装
$config_exist = is_file(ABS_PATH.'/config.php');

$setup = isset($_POST['setup']) ? $_POST['setup'] : 'default';

switch($setup) {
	case 'install':
		 if (validate_is_post()) {
			 $dbtype = isset($_POST['dbtype']) ? $_POST['dbtype'] : null;
			 // 配置文件不存在，需要填写表前缀
            if (!$config_exist) {
                validate_check('prefix', '/^[\w]+$/i', '数据表前缀只能包含数字、字母和下划线。');
            }
			// 用户名
            validate_check('adminname',VALIDATE_EMPTY,'用户名不能为空');
			validate_check('nickname',VALIDATE_EMPTY,'用户昵称不能为空');
			validate_check('usercode',VALIDATE_EMPTY,'用户代码不能为空');
			//用户组
			validate_check('admingroup',VALIDATE_EMPTY,'用户组不能为空');
			validate_check('groupcode',VALIDATE_EMPTY,'用户组代码不能为空');
            // 密码
            validate_check('password1',VALIDATE_EQUAL,'两次输入的密码不匹配','password2');
			if (validate_is_ok()) {
				$writable = true;
				fcache_flush();
				// 需要设置数据库配置
                if (!$config_exist) {
                    $dbname = isset($_POST['dbname'])?$_POST['dbname']:null;
                    $uname  = isset($_POST['dbuname'])?$_POST['dbuname']:null;
                    $pwd    = isset($_POST['dbpwd'])?$_POST['dbpwd']:null;
                    $dbhost = isset($_POST['dbhost'])?$_POST['dbhost']:null;
                    $prefix = isset($_POST['prefix'])?$_POST['prefix']:null;
					$key	= md5($_SERVER['HTTP_HOST'].dirname(__FILE__));
                    // mysql DSN
                    if (instr($dbtype,'mysql,mysqli')) {
                        $db_dsn = sprintf('%1$s:host=%2$s;name=%3$s;prefix=%4$s;', $dbtype, $dbhost, $dbname, $prefix);
                    }
                    // sqlite DSN
                    elseif (instr($dbtype,'sqlite2,sqlite3,pdo_sqlite2,pdo_sqlite')) {
                        $db_dsn = sprintf('%1$s:name=%2$s;prefix=%3$s;', $dbtype, $dbname, $prefix);
                    }
                    // 清理之前的错误
                    $err = last_error(null);
                    // 测试数据库链接信息
                    $db  = @DBQuery::factory($db_dsn, $uname, $pwd);
                    // 取得错误信息
                    $err = last_error();
                    // 有错误，提示
                    if ($err) ajax_alert($err['error']);

                    // 检查 config.sample.php 文件是否存在
                    if (!is_file(COM_PATH.'/config.sample.php')) {
                        ajax_error('系统文件缺失：common/config.sample.php ， 请重新上传程序文件。');
                    }
                    // 替换变量
                    $configs = file(COM_PATH.'/config.sample.php');
                    foreach ($configs as $num => $line) {
                        switch(substr($line,0,16)) {
                            case "define('DB_DSN',":
                                $configs[$num] = str_replace("database_dsn_here", $db_dsn, $line);
                                break;
                            case "define('DB_USER'":
                                $configs[$num] = str_replace("username_here", $uname, $line);
                                break;
                            case "define('DB_PWD',":
                                $configs[$num] = str_replace("password_here", $pwd, $line);
                                break;
							case "define('HASHING_":
                                $configs[$num] = str_replace("hashing_key", $key, $line);
                                break;
                        }
                    }
                    // 检查是否具有写入权限
                    if ($writable = is_writable(COM_PATH.'/')) {
                        $config = implode('', $configs);
                        file_put_contents(ABS_PATH.'/config.php', $config);
                    }
                    // 定义数据库链接
                    define('DB_DSN',$db_dsn);
                    define('DB_USER',$uname);
                    define('DB_PWD',$pwd);
                }
				$db = get_conn();
                $query = install_schema();
                // 创建数据表
                $db->batch($query);
                // 是否安装初始数据
                $initial = isset($_POST['initial'])?$_POST['initial']:null;
                // 安装默认设置
                install_defaults($initial);
                // 保存用户填写的信息
				$admingroup	= isset($_POST['admingroup'])?$_POST['admingroup']:'';
				$groupcode	= isset($_POST['groupcode'])?$_POST['groupcode']:'';
                $adminname	= isset($_POST['adminname'])?$_POST['adminname']:'';
                $password	= isset($_POST['password1'])?$_POST['password1']:'';
				$nickname	= isset($_POST['nickname'])?$_POST['nickname']:'';
				$usercode	= isset($_POST['usercode'])?$_POST['usercode']:'';
                $email		= isset($_POST['email'])?$_POST['email']:'';
				
				
				//添加用户组
				$group_info = array(
					'name'    => $admingroup,
					'code'  => $groupcode,
					'permissions' => 'categories,post-new,post-list,post-edit,post-delete,user-list,user-new,user-edit,user-delete,option-general,option-posts'
                 );
				if($group = group_get($admingroup)) {
					group_edit($group['id'],$group_info);
				}else {
					$group = group_add($group_info);
				}
				$groupid = $group['id'];

                // 管理员存在，修改管理员信息
                if ($admin = user_get_byname($adminname)) {
                    user_edit($admin['userid'],array(
                        'pass' => md5($password.$admin['authcode']),
						'usergroup'	=> $groupid,
                        'mail' => esc_html($email),
                        'roles' => 'ALL',
                        'Administrator' => 'Yes'
                    ));
                }
                // 添加管理员
                else {
                    user_add($adminname,$password,$email,array(
                        'url'  => esc_html(HTTP_HOST),
                        'nickname' => esc_html($adminname),
                        'roles' => 'ALL',
                        'Administrator' => 'Yes'
                    ));
                }
                if ($writable) {
                    ajax_success('一切已经就绪！你现在就可以进入后台管理页面。', "InfoSYS.redirect('".ADMIN."');");
                } else {
                    ajax_alert('创建配置文件config.php失败，如果尝试再次失败请检查文件权限或手动配置config.php，详情在根目录下的readme.txt里。');
                }
			}
		 }
		break;
	case 'config':
		break;
	default:
		$error_level = error_reporting(0);
		$html = '<div class="wizard" id="wizard-setup">';
		$html.= '<h1>系统安装</h1>';
		
		$html.= '<div class="wizard-card" data-cardname="database">';
		$html.=   '<h3>配置数据库</h3>';
		
		$html.=   '<div class="wizard-input-section">';
		$html.=     '<div class="control-group">';
		$html.=       '<label class="control-label" for="dbname">选择数据库</label>';
		$html.=       '<div class="controls">';
		$html.=         '<select name="dbtype" id="dbtype" class="wizard-db-select">';
            // sqlite3
            $phpinfo = parse_phpinfo();
            $sqlite  = isset($phpinfo['pdo_sqlite']) ? array_shift($phpinfo['pdo_sqlite']) == 'enabled' : false;
            if ($r = class_exists('SQLite3')) {
                $version = SQLite3::version();
                $html.=             sprintf('<option value="sqlite3">SQLite %s</option>', $version['versionString']);
            } elseif (extension_loaded('pdo_sqlite') && $sqlite) {
                $version = $phpinfo['pdo_sqlite']['SQLite Library'];
                $value   = version_compare($version, '3.0.0', '<') ? 'pdo_sqlite2' : 'pdo_sqlite';
                $html.=             sprintf('<option value="%s">PDO_SQLite %s</option>', $value, $version);
            }
            // sqlite2
            if ($r = function_exists('sqlite_libversion')) {
                $html.=             sprintf('<option value="sqlite2">SQLite %s</option>', sqlite_libversion());
            }
            // mysql
            if ($r = function_exists('mysqli_get_client_info')) {
                $html.=             sprintf('<option value="mysqli">MySQLi %s</option>', mysqli_get_client_info());
            }
            if ($r = function_exists('mysql_get_client_info')) {
                $html.=             sprintf('<option value="mysql">MySQL %s</option>', mysql_get_client_info());
            }
        $html.=         '</select>';
		$html.=         '<span class="help-inline">推荐使用SQLite 3.x或MySQLi。</span>';    
		$html.=       '</div>';
		$html.=     '</div>';
		$html.=   '</div>';
		
		$html.=   '<div class="wizard-input-section wizard-db-detail">';
		$html.=     '<div class="control-group">';
		$html.=       '<label class="control-label" for="dbname">数据库名字</label>';
		$html.=       '<div class="controls">';
		$html.=         '<input type="text" id="dbname" name="dbname" placeholder="数据库名字" value="test" rel="'.str_rand(10).'" data-validate="is_empty">';
		$html.=       '</div>';
		$html.=     '</div>';
		$html.=     '<div class="control-group">';
		$html.=       '<label class="control-label" for="dbuname">数据库用户名</label>';
		$html.=       '<div class="controls">';
		$html.=         '<input type="text" id="dbuname" name="dbuname" placeholder="数据库用户名">';
		$html.=       '</div>';
		$html.=     '</div>';
		$html.=     '<div class="control-group">';
		$html.=       '<label class="control-label" for="dbpwd">数据库密码</label>';
		$html.=       '<div class="controls">';
		$html.=         '<input type="password" id="dbpwd" name="dbpwd" placeholder="数据库用户密码">';
		$html.=       '</div>';
		$html.=     '</div>';
		$html.=     '<div class="control-group">';
		$html.=       '<label class="control-label" for="dbhost">数据库主机</label>';
		$html.=       '<div class="controls">';
		$html.=         '<input type="text" id="dbhost" name="dbhost" placeholder="数据库主机" value="localhost" data-validate="is_empty">';
		$html.=       '</div>';
		$html.=     '</div>';
		$html.=     '<div class="control-group">';
		$html.=       '<label class="control-label" for="prefix">数据表前缀</label>';
		$html.=       '<div class="controls">';
		$html.=         '<input type="text" id="prefix" name="prefix" placeholder="数据表前缀" value="im_" data-validate="is_empty">';
		$html.=       '</div>';
		$html.=     '</div>';
		$html.=   '</div>';
		$html.= '</div>';
				
		$html.= '<div class="wizard-card" data-cardname="group">';
		$html.=   '<h3>创建用户组</h3>';
		$html.=   '<div class="wizard-input-section">';
		$html.=     '<p>创建用户前必须先创建一个用户组（信息组），请填写完整信息。</p>';
		$html.=     '<div class="control-group">';
		$html.=       '<label class="control-label" for="admingroup">用户组名</label>';
		$html.=       '<div class="controls">';
		$html.=         '<input type="text" id="admingroup" name="admingroup" placeholder="例子：世邦信息组" data-validate="is_empty">';
		$html.=       '</div>';
		$html.=     '</div>';
		$html.=     '<div class="control-group">';
		$html.=       '<label class="control-label" for="admincode">用户组代码</label>';
		$html.=       '<div class="controls">';
		$html.=         '<input type="text" id="groupcode" name="groupcode" placeholder="例子： SBXX" data-validate="is_empty">';
		$html.=       '</div>';
		$html.=     '</div>';
		$html.=   '</div>';
		$html.= '</div>';
		
		$html.= '<div class="wizard-card" data-cardname="user">';
		$html.=   '<h3>创建用户</h3>';
		$html.=   '<div class="wizard-input-section">';
		$html.=     '<div class="control-group">';
		$html.=       '<label class="control-label" for="adminname">用户名</label>';
		$html.=       '<div class="controls">';
		$html.=         '<input type="text" id="adminname" name="adminname" placeholder="用户名" data-validate="is_empty">';
		$html.=       '</div>';
		$html.=     '</div>';
		$html.=     '<div class="control-group">';
		$html.=       '<label class="control-label" for="password1">密码(两次)</label>';
		$html.=       '<div class="controls">';
		$html.=         '<input type="password" id="password1" name="password1" placeholder="请输入密码" data-validate="is_empty">';
		$html.=       '</div>';
		$html.=     '</div>';
		$html.=     '<div class="control-group">';
		$html.=       '<div class="controls">';
		$html.=         '<input type="password" id="password2" name="password2" placeholder="请再次输入密码" data-validate="is_empty">';
		$html.=       '</div>';
		$html.=     '</div>';
		$html.=     '<div class="control-group">';
		$html.=       '<label class="control-label" for="nickname">昵称</label>';
		$html.=       '<div class="controls">';
		$html.=         '<input type="text" id="nickname" name="nickname" placeholder="作为信息员的名字" data-validate="is_empty">';
		$html.=       '</div>';
		$html.=     '</div>';
		$html.=     '<div class="control-group">';
		$html.=       '<label class="control-label" for="usercode">代码</label>';
		$html.=       '<div class="controls">';
		$html.=         '<input type="text" id="usercode" name="usercode" placeholder="一般为首写字母缩写" data-validate="is_empty">';
		$html.=       '</div>';
		$html.=     '</div>';
		$html.=   '</div>';
		$html.= '</div>';
		
		$html.= '<div class="wizard-error">';
		$html.=   '<div class="alert alert-error">';
		$html.=     '<strong>安装失败。</strong> 请修正错误，重新提交。';
		$html.=   '</div>';
		$html.= '</div>';
		
		$html.= '<div class="wizard-failure">';
		$html.=   '<div class="alert alert-error">';
		$html.=     '<strong>安装中发生了一个错误</strong> 请等一会儿再提交。';
		$html.=   '</div>';
		$html.= '</div>';
		
		$html.= '<div class="wizard-success">';
		$html.=   '<div class="alert alert-success">';
		//$html.=     '系统安装<strong>成功。</strong>';
		$html.=   '</div>';
		$html.=   '<a class="btn btn-success create-another-config">重新配置</a>';
		$html.=   '<span style="padding:0 10px">或者</span>';
		$html.=   '<a class="btn im-done" href="index.php">进入后台管理界面</a>';
		$html.= '</div>';

		$html.= '</div>';
		
		echo '<!DOCTYPE html><html>';
    	echo '<head>';
   	 	echo '<title>安装向导</title>'; loader_css('css/install'); loader_js('js/common'); loader_js('js/install');
    	echo '</head><body style="padding-top: 45px;">';
		
		echo '<div class="navbar navbar-inverse navbar-fixed-top">';
		echo   '<div class="navbar-inner">';
		echo     '<div class="container">';
		echo        '<a class="brand" href="#">安装向导</a>';
		echo     '</div>';
		echo   '</div>';
		echo '</div>';
		
		echo '<div class="container">';
		//echo    '<h2>安装</h2>';
		echo '<div id="setup">';
        echo     '<table class="data-table table">';
        echo         '<thead><tr><th colspan="3">系统信息</th></tr></thead>';
        echo         '<tbody>';
        echo             '<tr><td>服务器操作系统</td><td>'.PHP_OS .' '. php_uname('r') .' On '. php_uname('m').'</td></tr>';
        echo             '<tr><td>服务器软件</td><td>'.$_SERVER['SERVER_SOFTWARE'].'</td></tr>';
        echo             '<tr><td>服务器API</td><td>'.PHP_SAPI.'</td></tr>';
        echo         '</tbody>';
        echo     '</table>';
        // HTTPLIB
        include COM_PATH.'/system/httplib.php';
        $http_test = httplib_test();
        echo     '<table class="data-table table">';
        echo         '<thead><tr><th colspan="3">需要的功能</th></tr></thead>';
        echo         '<tbody>';
        echo             '<tr class="thead"><th>测试</th><th class="w100">需求</th><th class="w150">当前</th></tr>';
        echo             '<tr><td>PHP版本</td><td>4.3.3+</td><td>'.test_result(version_compare(PHP_VERSION,'4.3.3','>')).'&nbsp; '.PHP_VERSION.'</td></tr>';
        echo             '<tr><td>数据驱动</td><td>SQLite 2.8.0+<br />MySQL 4.1.0+</td><td>';
        // sqlite
        $phpinfo = parse_phpinfo();
        $sqlite  = isset($phpinfo['pdo_sqlite']) ? array_shift($phpinfo['pdo_sqlite']) == 'enabled' : false;
        if ($r = class_exists('SQLite3')) {
            $version = SQLite3::version();
            echo             test_result($r).'&nbsp; SQLite '.$version['versionString'];
        } elseif (extension_loaded('pdo_sqlite') && $sqlite) {
            $version = $phpinfo['pdo_sqlite']['SQLite Library'];
            echo             test_result($sqlite).'&nbsp; SQLite '.$version;
        } elseif ($r = function_exists('sqlite_libversion')) {
            echo             test_result($r).'&nbsp; SQLite '.sqlite_libversion();
        } else {
            echo             test_result(false).'&nbsp; SQLite 不支持';
        }
        echo                 '<br />';
        // mysql
        if ($r = function_exists('mysql_get_client_info')) {
            echo             test_result($r).'&nbsp; MySQL '.mysql_get_client_info();
        } elseif ($r = function_exists('mysqli_get_client_info')) {
            echo             test_result($r).'&nbsp; MySQL '.mysqli_get_client_info();
        } else {
            echo             test_result(false).'&nbsp; MySQL 不支持';
        }
        echo             '</td></tr>';
        echo             '<tr><td>图形绘制库</td><td>2.0.0+</td><td>'.test_result(function_exists('gd_info')).'&nbsp; '.(function_exists('gd_info') ? GD_VERSION : '不支持').'</td></tr>';
        echo             '<tr><td>字符集转换</td><td>2.0.0+</td><td>'.test_result(function_exists('iconv')).'&nbsp; '.(function_exists('iconv') ? ICONV_VERSION : '不支持').'</td></tr>';
        echo             '<tr><td>多字节字符串处理支持</td><td>支持</td><td>'.test_result(extension_loaded('mbstring')).'&nbsp; '.(extension_loaded('mbstring') ? 'mbstring' : '不支持').'</td></tr>';
        echo             '<tr><td>打开远程URL</td><td>支持</td><td>'.test_result($http_test).'&nbsp; '.($http_test ? array_shift($http_test) : '不支持').'</td></tr>';
        echo         '</tbody>';
        echo     '</table>';
        echo     '<table class="data-table table">';
        echo         '<thead><tr><th colspan="3">目录权限</th></tr></thead>';
        echo         '<tbody>';
        echo             '<tr class="thead"><th>路径</th><th class="w100">读</th><th class="w100">写</th></tr>';
        $paths = array(
            '/',
            '/error.log',
            '/config.php',
            '/common/.cache/',
        );
        foreach($paths as $path) {
            $is_read  = is_readable(ABS_PATH.$path);
            $is_write = is_writable(ABS_PATH.$path);
            // 检测文件
            if (!substr_compare($path,'/',strlen($path)-1,1)===false) {
                if (!is_file(ABS_PATH.$path)) {
                    mkdirs(dirname(ABS_PATH.$path));
                    file_put_contents(ABS_PATH.$path, "<?php\necho 'Testing...';");
                    $is_read  = is_readable(ABS_PATH.$path);
                    $is_write = is_writable(ABS_PATH.$path);
                    unlink(ABS_PATH.$path);
                }
            }
            echo         '<tr><td>'.ABS_PATH.$path.'</td><td>'.test_result($is_read).'</td><td>'.test_result($is_write).'</td></tr>';
        }
        echo         '</tbody>';
        echo     '</table>';
        echo     system_phpinfo(INFO_CONFIGURATION | INFO_MODULES);
        echo     '<p class="buttons">';
        echo         '<input type="hidden" name="setup" value="config" />';
        echo         '<button id="open-wizard" class="btn btn-primary"><i class="icon-ok"></i> 开始安装</button> ';
        echo         '<button type="button" rel="refresh" class="btn"><i class="icon-refresh"></i> 重试一次</button> ';
        echo         '<button type="button" rel="phpinfo" class="btn"><i class="icon-eye-open"></i> 显示/隐藏phpinfo信息</button>';
        echo     '</p>';
        echo '</div>';
        error_reporting($error_level);
		echo '</div>';
		
    	echo $html.'</body></html>';
		break;
}
?>