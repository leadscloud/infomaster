<?php
defined('COM_PATH') or die('Restricted access!');
// 定义返回类型
define('CLIENT_MULTI_RESULTS', 0x20000);
// 加载父类
include_file(dirname(__FILE__).'/abs_mysql.php');
/**
 * mysql 访问类
 */
class DB_Mysql extends DBQuery {
    // private
    var $host     = 'localhost';
    var $user     = 'root';
    var $pwd      = '';
    var $pconnect = false;
    var $goneaway = 3; 
    /**
     * 初始化连接
     *
     * @param array $config          数据库设置
     * @return void
     */
    function __construct($config) {
        if (!function_exists('mysql_query')) {
            return throw_error(sprintf('您的 PHP 似乎缺少系统所需的 %s 扩展。', 'MySQL'),E_SYS_ERROR);
        }
        if (!empty($config)) {
            $this->host     = isset($config['host']) ? $config['host'] : $this->host;
            $this->user     = isset($config['user']) ? $config['user'] : $this->user;
            $this->pwd      = isset($config['pwd']) ? $config['pwd'] : $this->pwd;
            $this->name     = isset($config['name']) ? $config['name'] : $this->name;
            $this->pconnect = isset($config['pconnect']) ? $config['pconnect'] : $this->pconnect;
            if ($this->connect()) {
                $this->select_db();
            }
            if ($this->conn && mysql_errno($this->conn)==0) {
                $this->ready = true;
            }
        }
    }
    /**
     * 连接Mysql
     *
     * @return bool|void
     */
    function connect(){
        // 检验数据库链接参数
        if (!$this->host || !$this->user)
            return throw_error('数据库连接错误，请检查数据库设置！',E_SYS_ERROR);
        // 连接数据库
        if (function_exists('mysql_pconnect') && $this->pconnect) {
            $this->conn = mysql_pconnect($this->host,$this->user,$this->pwd,CLIENT_MULTI_RESULTS);
        } elseif (function_exists('mysql_connect')) {
            $this->conn = mysql_connect($this->host,$this->user,$this->pwd,false,CLIENT_MULTI_RESULTS);
        }
        
        // 验证连接是否正确
        if (!$this->conn) {
            return throw_error(sprintf('数据库链接错误：%s', mysql_error()),E_SYS_ERROR);
        }
        return $this->conn;
    }
    /**
     * 选择数据库
     *
     * @param string $db (optional)
     */
    function select_db($db=null){
        // 验证连接是否正确
        if (!$this->conn) $this->connect();
        if (empty($db)) $db = $this->name;
        // 选择数据库
        if (!mysql_select_db($db,$this->conn)) {
            return throw_error(sprintf('%s 数据库不存在！',$db),E_SYS_ERROR);
        }
        // MYSQL数据库的设置
        if (version_compare($this->version(), '4.1', '>=')) {
            if (mysql_client_encoding($this->conn) != 'utf8')
                mysql_query("SET NAMES utf8;", $this->conn);
        	if(version_compare($this->version(), '5.0.1', '>' )) {
	            mysql_query("SET sql_mode='';", $this->conn);
	        }
        } else {
            return throw_error('MySQL数据库版本低于4.1，请升级MySQL！',E_SYS_ERROR);
        }
        
        return true;
    }
    /**
     * 指定函数执行SQL语句
     *
     * @param string $sql	sql语句
     * @return resource
     */
    function query($sql){
        // 验证连接是否正确
        if (!$this->conn) {
            return throw_error('提供的参数不是一个有效的MySQL的链接资源。',E_SYS_ERROR);
        }
        $args = func_get_args();

        $sql = call_user_func_array(array(&$this,'prepare'), $args);

        if ( preg_match("/^\\s*(insert|delete|update|replace|alter table|create) /i",$sql) ) {
        	$func = 'mysql_unbuffered_query';
        } else {
        	$func = 'mysql_query';
        }
        $this->sql = $sql;
		
        if (!($result = $func($sql,$this->conn))) {
            if (in_array(mysql_errno($this->conn),array(2006,2013)) && ($this->goneaway-- > 0)) {
                $this->close(); $this->connect(); $this->select_db();
                $result = call_user_func_array(array(&$this,'query'), $args);
            } else {
                // 重置计数
                $this->goneaway = 3;
                return throw_error(sprintf("SQLite 查询错误：%s",$sql."\r\n\t".mysql_error($this->conn)),E_SYS_ERROR);
            }
        }
        // 查询正常
        if ($result) {
            // 重置计数
            $this->goneaway = 3;
            // 返回结果
            if ($func == 'mysql_unbuffered_query') {
                if ( preg_match("/^\\s*(insert|replace) /i",$sql) ) {
                    $result = ($insert_id = mysql_insert_id($this->conn)) >= 0 ? $insert_id : $this->result("SELECT LAST_INSERT_ID();");
                } else {
                    $result = mysql_affected_rows($this->conn);
                }
            }
        }
        return $result;
    }
    /**
     * 检查是否存在数据库
     *
     * @param  $dbname
     * @return bool
     */
    function is_database($dbname){
        $res = $this->query("SHOW DATABASES;");
        while ($rs = $this->fetch($res,0)) {
        	if ($dbname == $rs[0]) return true;
        }
        return false;
    }
    /**
     * 判断数据表是否存在
     *
     * 注意表名的大小写，是有区别的
     *
     * @param string $table    table
     * @return bool
     */
    function is_table($table){
        $res = $this->query(sprintf("SHOW TABLES FROM `%s`;", $this->name));
        if (!strncasecmp($table,'#@_',3))
            $table = str_replace('#@_',$this->prefix,$table);

        while ($rs = $this->fetch($res,0)) {
        	if ($table == $rs[0]) return true;
        }
        return false;
    }
    /**
     * 列出表里的所有字段
     *
     * @param string $table    表名
     */
    function list_fields($table){
        $result = array();
        $res = $this->query(sprintf("SHOW COLUMNS FROM `%s`;", $table));
        while ($rs = $this->fetch($res)) {
        	$result[] = $rs['Field'];
        }
        return $result;
    }
    /**
     * 取得数据集的单条记录
     *
     * @param resource  $result
     * @param int       $mode
     * @return array
     */
    function fetch($result,$mode=1){
        switch (intval($mode)) {
            case 0: $mode = MYSQL_NUM;break;
            case 1: $mode = MYSQL_ASSOC;break;
            case 2: $mode = MYSQL_BOTH;break;
        }
        return mysql_fetch_array($result,$mode);
    }
    /**
     * 取得 MySQL 服务器信息
     *
     * @return string
     */
    function version(){
        return mysql_get_server_info($this->conn);
    }
    /**
     * 关闭 MySQL 连接
     *
     * @return bool
     */
    function close(){
        if (is_resource($this->conn)) {
            return mysql_close($this->conn);
        }
    }
	/**
     * 转义SQL语句
     *
     * @param mixed $value
     * @return string
     */
    function escape($value){
        // 空
        if ($value === null) return '';
        // 转义变量
        $value = $this->envalue($value);

        if ( $this->conn )
			$value = mysql_real_escape_string( $value, $this->conn );
		else
			$value = addslashes( $value );

        return $value;
    }
    /**
     * 类构造函数
     *
     * @return void
     */
    function DB_Mysql() {
        // 添加PHP4下的析构函数
        register_shutdown_function( array(&$this, '__destruct') );

        // 调用PHP的构造函数
        $args = func_get_args();
		call_user_func_array( array(&$this, '__construct'), $args );
    }
    /**
     * 类析构
     * 
     * @return void
     */
    function __destruct(){
    	$this->close();
    }
}
