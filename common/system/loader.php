<?php
defined('COM_PATH') or die('Restricted access!');
/**
 * 禁用错误报告
 *
 * Set this to error_reporting( E_ALL ) or error_reporting( E_ALL | E_STRICT ) for debugging
 */
/**
 * 后台静态文件加载类
 */
class Loader {
    var $_files = array();
    /**
     * 设置分组
     *
     * @param array $files
     * @return void
     */
    function set_files($files) {
        $this->_files = $files;
    }
    /**
     * 取得版本号
     *
     * @param string|array $loads
     * @return int
     */
    function get_version($loads){
        $version = 0;
        $files   = $this->get_dependence_files($loads);
        foreach ($files as $srcs) {
        	foreach ($srcs as $src) {
        		$version = max($version,filemtime($src));
        	}
        }
        if ($version) {
        	$version = date('YmdHis',$version);
        }
        return $version;
    }
    /**
     * 取得文件
     *
     * @param string $loads
     * @return array
     */
    function get_dependence_files($loads){
        $result = array(); $files = array();
        if (is_array($loads) || strpos($loads,',')!==false) {
        	$loads = !is_array($loads) ? explode(',', $loads) : $loads;
        	foreach ($loads as $file) {
        	    $dependence_file = $this->_get_dependence_files($file);
        	    if (!empty($dependence_file)) {
        	    	$files[$file] = $dependence_file;
        	    }
        	}
        } else {
            $files[$loads] = $this->_get_dependence_files($loads);
        }

        $is_exist = array();
        foreach ($files as $group => $src) {
            $arr_src = array();
            foreach ($src as $k=>$v) {
                if (empty($v)) continue;
                if (!isset($is_exist[$k])) {
                	$arr_src[]    = $k;
                	$is_exist[$k] = 1;
                }
            }
            $result[$group] = $arr_src;
        }
        return $result;
    }
    function _get_dependence_files($name){
        $files = array();
        if (isset($this->_files[$name])) {
            $rule = $this->_files[$name];
        	// 存在依赖
        	if (isset($rule[1]) && !empty($rule[1])) {
        	    foreach ($rule[1] as $tode_file) {
        	    	$dependence_files = $this->_get_dependence_files($tode_file);
        	    	foreach ($dependence_files as $file=>$v) {
        	    	    if (!isset($files[$file])) {
        	    	    	$files[$file] = 1;
        	    	    }
        	        }
        	    }
        	}
        	$file = $rule[0];
            if (!strncasecmp($file, '/common/', 8)) {
                $file = COM_PATH.substr($file,7);
            } elseif (!strncasecmp($file, '/admin/', 7)) {
                $file = ADMIN_PATH.substr($file,6);
            }
        	if (!isset($files[$file]) && is_file($file)) {
                $files[$file] = 1;
            }
        }
        return $files;
    }
}

/**
 * 取得实例
 *
 * @return $loader
 */
function &_loader_get_object() {
    static $loader;
	if ( is_null($loader) )
		$loader = new Loader();
	return $loader;
}
/**
 * 取得文件列表
 *
 * @param string $type css or js
 * @param string|array $loads
 * @return array
 */
function loader_get_files($type, $loads) {
    $loader = _loader_get_object();
    if ($type == 'css') {
        global $Loader_Styles;
        $loader->set_files($Loader_Styles);
    } elseif ($type == 'js') {
        global $Loader_Scripts;
        $loader->set_files($Loader_Scripts);
    }
    return $loader->get_dependence_files($loads);
}
/**
 * 加载css
 *
 * @return void
 */
function loader_css() {
    global $Loader_Styles;
    $loader = _loader_get_object();
    $files  = array();
    $args   = func_get_args();
    if (isset($args[0]) && is_array($args[0]))
        $args = $args[0];

    if (empty($args)) return ;

    foreach ($args as $file) {
        $files[] = !strncasecmp($file,'css/',4) ? substr( $file, 4 ) : $file;
    }
    $loads = implode(',', $files);
    // 设置css
    $loader->set_files($Loader_Styles);
    // 加载样式表
    $version = $loader->get_version($loads);
    // 输出HTML
    printf('<link href="'.ADMIN.'loader.php?%s" rel="stylesheet" type="text/css" />',str_replace('%2C',',',http_build_query(array(
        'type' => 'css',
        'load' => $loads,
        'ver'  => $version,
    ))));
}
/**
 * 加载js
 *
 * @return void
 */
function loader_js() {
    global $Loader_Scripts;
    $loader = _loader_get_object();
    $files  = array();
    $args   = func_get_args();
    if (isset($args[0]) && is_array($args[0]))
        $args = $args[0];

    if (empty($args)) return ;

    foreach ($args as $file) {
        $files[] = !strncasecmp($file,'js/',3) ? substr($file, 3) : $file;
    }
    $loads = implode(',', $files);
    // 设置js
    $loader->set_files($Loader_Scripts);
    // 加载样式表
    $version = $loader->get_version($loads);
    // 输出HTML
    printf('<script type="text/javascript" src="'.ADMIN.'loader.php?%s"></script>',str_replace('%2C',',',http_build_query(array(
        'type' => 'js',
        'load' => $loads,
        'ver'  => $version,
    ))));
}
/**
 * 添加css
 *
 * @param array $styles
 *      array(
 *          'key' => array('css path', array('common')),
 *      )
 * @return array
 */
function loader_add_css($styles) {
    global $Loader_Styles;
    if (!$Loader_Styles) {
        $Loader_Styles = array();
    }
    if (func_num_args() == 2) {
        $args = func_get_args();
        $key  = $args[0];
        $val  = $args[1];
        $Loader_Styles[$key] = (array) $val;
    } else {
        foreach ((array) $styles as $key=>$val) {
            $Loader_Styles[$key] = (array) $val;
        }
    }
    return $Loader_Styles;
}
/**
 * 添加js
 *
 * @param array $scripts
 *      array(
 *          'key' => array('js path', array('common')),
 *      )
 * @return array
 */
function loader_add_js($scripts) {
    global $Loader_Scripts;
    if (!$Loader_Scripts) {
        $Loader_Scripts = array();
    }
    if (func_num_args() == 2) {
        $args = func_get_args();
        $key  = $args[0];
        $val  = $args[1];
        $Loader_Scripts[$key] = (array) $val;
    } else {
        foreach ((array) $scripts as $key=>$val) {
            $Loader_Scripts[$key] = (array) $val;
        }
    }
    return $Loader_Scripts;
}