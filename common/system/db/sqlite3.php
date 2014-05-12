<?php
defined('COM_PATH') or die('Restricted access!');
/**
 * sqlite3 访问类
 *
 */
class DB_Sqlite3 extends DBQuery {
    /**
     * 初始化链接
     *
     * @param array $config
     * @return bool
     */
    function __construct($config) {
        if (!class_exists('SQLite3')) {
            return throw_error(sprintf('您的 PHP 似乎缺少系统所需的 %s 扩展。', 'SQLite3'),E_SYS_ERROR);
        }
        if (!empty($config)) {
            $this->name = isset($config['name']) ? $config['name'] : $this->name;
            if (strncmp($this->name, '/', 1)!==0 || strpos($this->name, ':')===false) {
                $this->name = ABS_PATH.'/'.$this->name;
            }
            $this->open($this->name);
            if ($this->conn && $this->conn->lastErrorCode()==0) {
                $this->apply_plugins();
                $this->ready = true;
            }
        }
    }

    /**
     * 打开数据库
     *
     * @param string $dbname
     * @param int $flags
     * @return bool
     */
    function open($dbname, $flags=null) {
        if ($flags === null) $flags = SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE;
        $this->conn = new SQLite3($dbname, $flags, null);
        // 验证连接是否正确
        if ($this->conn->lastErrorCode() > 0) {
            return throw_error(sprintf('数据库链接错误：%s', $this->conn->lastErrorMsg()), E_SYS_ERROR);
        }
        // 设置字段模式
        $this->conn->exec("PRAGMA short_column_names=ON;");
        // 设置10秒等待
        $this->conn->busyTimeout(10000);
        return $this->conn;
    }
    /**
     * 执行自定义函数
     *
     * @return void
     */
    function apply_plugins() {
        $this->conn->createFunction('UCASE', 'strtoupper', 1);
    }
    /**
     * 执行查询
     *
     * @param string $sql
     * @return bool
     */
    function query($sql){
        // 验证连接是否正确
        if (!is_object($this->conn)) {
            return throw_error('提供的参数不是一个有效的SQLite对象。',E_SYS_ERROR);
        }
        $args = func_get_args(); $afters = array();

        $sql = call_user_func_array(array(&$this,'prepare'), $args);
        $sql = $this->process($sql, $befores, $afters);

        if ( preg_match("/^\\s*(insert|delete|update|replace|alter table|create) /i",$sql) ) {
        	$func = 'exec';
        } else {
        	$func = 'query';
        }
        // 执行前置sql
        if ($befores) {
            foreach ($befores as $v) $this->query($v);
        }
        $this->sql = $sql;
        if (!($result = $this->conn->$func($sql))) {
            return throw_error(sprintf('SQLite 查询错误：%s',$sql."\r\n\t".$this->conn->lastErrorMsg()),E_SYS_ERROR);
        }
        // 查询正常
        else {
            // 执行后置 SQL
            if ($afters) {
                foreach ($afters as $v) $this->query($v);
            }
            // 返回结果
            if ($func == 'exec') {
                if ( preg_match("/^\\s*(insert|replace) /i", $sql) ) {
                    $result = ($insert_id = $this->conn->lastInsertRowID()) >= 0 ? $insert_id : $this->result("SELECT LAST_INSERT_ROWID();");
                } else {
                    $result = $this->conn->changes();
                }
            }
        }
        return $result;
    }
    /**
     * 取得数据集的单条记录
     *
     * @param SQLite3Result $result
     * @param int $mode
     * @return array
     */
    function fetch($result,$mode=1){
        switch (intval($mode)) {
            case 0: $mode = SQLITE3_NUM;break;
            case 1: $mode = SQLITE3_ASSOC;break;
            case 2: $mode = SQLITE3_BOTH;break;
        }
        return $result->fetchArray($mode);
    }
    /**
     * 检查是否存在数据库
     *
     * @param string $dbname
     * @return bool
     */
    function is_database($dbname) {
        return is_file($dbname);
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
        $res = $this->query("SELECT `name` FROM `sqlite_master` WHERE `type`='table';");
        if (!strncasecmp($table,'#@_',3))
            $table = str_replace('#@_', $this->prefix, $table);

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
        $res    = $this->query(sprintf("PRAGMA table_info(%s);", $table));
        while ($row = $this->fetch($res)) {
            $result[] = $row['name'];
        }
        return $result;
    }
    /**
     * SQLite 版本
     *
     * @return string
     */
    function version(){
        $version = $this->conn->version();
        return $version['versionString'];
    }
    /**
     * 关闭链接
     *
     * @return bool
     */
    function close(){
        if (is_object($this->conn)) {
            return $this->conn->close();
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
			$value = $this->conn->escapeString($value);
		else
			$value = str_replace("'", "''", $value );

        return $value;
    }
    /**
     * 类构造
     *
     * @return void
     */
    function DB_Sqlite3() {
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
