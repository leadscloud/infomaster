<?php
defined('COM_PATH') or die('Restricted access!');
/**
 * 数据库访问类
 *
 */
class DBQuery {
    // public
    var $ready  = false;
    var $conn   = null;
    var $sql    = '';
    var $name   = 'test';
    var $prefix = '';
    var $scheme = null;
    /**
     * 取得数据库实例
     *
     * @param string $DSN   PDO格式 // mysql:host=localhost;name=test;prefix=info_;
     * @param string $user  用户名 [可选]
     * @param string $pwd   密码  [可选]
     * @return object
     */
    function &factory($DSN, $user=null, $pwd=null) {
        $config = array();
        if (($pos=strpos($DSN, ':')) !== false) {
            $scheme = strtolower(substr($DSN, 0, $pos));
            $string = substr($DSN, $pos+1);
            if (strpos($string,';') !== false) {
                $arrays = explode(';', trim($string,';'));
                foreach ($arrays as $v) {
                    $pos = strpos($v, '=');
                    $key = trim(substr($v, 0, $pos));
                    $val = trim(substr($v, $pos + 1));
                    $config[$key] = $val;
                }
            } else {
                $config[] = $string;
            }
            if ($scheme && !isset($config['scheme']))
                $config['scheme'] = $scheme;
            if ($user!==null && !isset($config['user']))
                $config['user'] = $user;
            if ($pwd!==null && !isset($config['pwd']))
                $config['pwd'] = $pwd;
        }
        if ($scheme && $config) {
            // 加载数据库文件
            if (strncasecmp($scheme, 'pdo_sqlite', 10) === 0) {
                $classname = 'DB_PDO_Sqlite';
                include_file(COM_PATH.'/system/db/pdo_sqlite.php');
            } else {
                $classname = 'DB_'.ucfirst($scheme);
                include_file(COM_PATH.sprintf('/system/db/%s.php', $scheme));
            }
            if (class_exists($classname)) {
                $db = new $classname($config);
            } else {
                $db = new DBQuery_NOOP();
            }
            $db->scheme = $scheme;
            if (isset($config['prefix']))
                $db->prefix = $config['prefix'];
        }
        return $db;
    }
    /**
     * 等同于 mysql_result
     *
     * @param string $sql 可以是MYSQL资源句柄，也可以使用MYSQL语句
     * @param int $row 偏移量
     * @return string
     */
    function result($sql,$row=0) {
    	$result = $this->query($sql);
        if ($rs = $this->fetch($result,0)) {
            return $rs[$row];
        }
        return null;
    }
    /**
     * 插入数据
     *
     * @param string $table    table
     * @param array  $data     插入数据的数组，key对应列名，value对应值
     * @return int
     */
    function insert($table,$data){
        $cols = array();
        $vals = array();
        foreach ($data as $col => $val) {
            $cols[] = $this->identifier($col);
            $vals[] = $this->escape($val);
        }

        $sql = "INSERT INTO "
             . $this->identifier($table)
             . ' (' . implode(', ', $cols) . ') '
             . "VALUES ('" . implode("', '", $vals) . "')";

             return $this->query($sql);
    }
    /**
     * 更新数据
     *
     * @param string $table    table
     * @param array  $sets     set 数组
     * @param mixed  $where    where语句，支持数组，数组默认使用 AND 连接
     * @return int
     */
    function update($table,$sets,$where=null){
        // extract and quote col names from the array keys
        $set = array();
        foreach ($sets as $col => $val) {
            $val   = $this->escape($val);
            $set[] = $this->identifier($col)." = '".$val."'";
        }
        $where = $this->where($where);
        // build the statement
        $sql = "UPDATE "
             . $this->identifier($table)
             . ' SET ' . implode(', ', $set)
             . (($where) ? " WHERE {$where}" : '');

        return $this->query($sql);
    }
    /**
     * 删除数据
     *
     * @param string $table
     * @param string $where
     * @return int
     */
    function delete($table,$where=null){
        $where = $this->where($where);
        // build the statement
        $sql = "DELETE FROM "
             . $this->identifier($table)
             . (($where) ? " WHERE {$where}" : '');

        return $this->query($sql);
    }
    /**
     * 清空表
     *
     * @param string $table
     * @return resource
     */
    function truncate($table) {
        return $this->query(sprintf("DELETE FROM `%s`;", $table));
    }

    /**
     * 判断列名是否存在
     *
     * @param string $p1    table
     * @param string $p2    field
     * @return bool
     */
    function is_field($table,$field){
        return in_array($field,$this->list_fields($table));
    }
    /**
	 * Prepares a SQL query for safe execution. Uses sprintf()-like syntax.
	 *
	 * The following directives can be used in the query format string:
	 *   %d (decimal number)
	 *   %s (string)
	 *   %% (literal percentage sign - no argument needed)
	 *
	 * Both %d and %s are to be left unquoted in the query string and they need an argument passed for them.
	 * Literals (%) as parts of the query must be properly written as %%.
	 *
	 * This function only supports a small subset of the sprintf syntax; it only supports %d (decimal number), %s (string).
	 * Does not support sign, padding, alignment, width or precision specifiers.
	 * Does not support argument numbering/swapping.
	 *
	 * May be called like {@link http://php.net/sprintf sprintf()} or like {@link http://php.net/vsprintf vsprintf()}.
	 *
	 * Both %d and %s should be left unquoted in the query string.
	 *
	 *
	 * @param string $query Query statement with sprintf()-like placeholders
	 * @param array|mixed $args The array of variables to substitute into the query's placeholders if being called like
	 * 	{@link http://php.net/vsprintf vsprintf()}, or the first variable to substitute into the query's placeholders if
	 * 	being called like {@link http://php.net/sprintf sprintf()}.
	 * @param mixed $args,... further variables to substitute into the query's placeholders if being called like
	 * 	{@link http://php.net/sprintf sprintf()}.
	 * @return null|false|string Sanitized query string, null if there is no query, false if there is an error and string
	 * 	if there was something to prepare
	 */
    function prepare($query = null) { // ( $query, *$args )
        if ( is_null( $query ) ) return ;
        $args = func_get_args(); array_shift( $args );
        // If args were passed as an array (as in vsprintf), move them up
		if ( isset( $args[0] ) && is_array($args[0]) ) $args = $args[0];

        $query = str_replace( "'%s'", '%s', $query ); // in case someone mistakenly already singlequoted it
		$query = str_replace( '"%s"', '%s', $query ); // doublequote unquoting
        $query = preg_replace( '/(?<!%)%s/', "'%s'", $query ); // quote the strings, avoiding escaped strings like %%s
        // 处理表前缀
        if (preg_match_all("/'[^']+'/",$query,$r)) {
            foreach($r[0] as $i=>$v) {
                $query = preg_replace('/'.preg_quote($v,'/').'/',"'@{$i}@'",$query,1);
            }
        }
        $query = preg_replace('/#@_([^ ]+)/iU',$this->prefix.'$1',$query);
        if (isset($r[0]) && !empty($r[0])) {
            foreach($r[0] as $i=>$v) {
                $query = str_replace("'@{$i}@'", $v, $query);
            }
        }
        if ($args) {
            foreach($args as $k=>$v) {
                $args[$k] = $this->escape($v);
            }
            $query = vsprintf($query, $args);
        }
        return $query;
    }
    /**
     * 处理 SQLite
     *
     * @param string $sql
     * @param array &$before
     * @param array &$after
     * @return string
     */
    function process($sql, &$before=null, &$after=null) {
        $sql = str_replace('`', '"', $sql);
        $result = $after = array(); $charlist = '`"[]\'';
        if (preg_match('/^(\s*CREATE\s+TABLE\s+)(IF\s+NOT\s+EXISTS\s+([^\s]+)|[^\s]+)/is', $sql, $matches)) {
            $table = isset($matches[3])?$matches[3]:$matches[2];
            // 版本低于3.0 AND 表已存在
            if (version_compare($this->version(),'3.0.0','<')
                    && preg_match('/^IF\s+NOT\s+EXISTS/i', $matches[2])
                    && $this->is_table(trim($table,$charlist))) {
                $sql = 'SELECT 1;';
            } else {
                preg_match('/\((.*)\)/ms', $sql, $match);
                $inner = trim($match[1]);
                $inner = str_ireplace(' unsigned', '', $inner);
                $lines = explode("\n", $inner);
                foreach ($lines as $line) {
                    $line = rtrim(trim($line),',');
                    // 处理主键
                    if (preg_match('/^PRIMARY\s*KEY.+$/i', $line)) continue;
                    // 处理唯一索引
                    if (preg_match('/^UNIQUE\s*KEY\s*([^ ]+)\s*(\(.+\))$/i', $line, $match)) {
                        $after[] = sprintf('CREATE UNIQUE INDEX `%2$s_%1$s` ON `%2$s` %3$s;', trim($match[1],$charlist), trim($table,$charlist), $match[2]);
                        continue;
                    }
                    // 处理普通索引
                    if (preg_match('/^KEY\s*([^ ]+)\s*(\(.+\))$/i', $line, $match)) {
                        $after[] = sprintf('CREATE INDEX `%2$s_%1$s` ON `%2$s` %3$s;', trim($match[1],$charlist), trim($table,$charlist), $match[2]);
                        continue;
                    }
                    // 处理自动编号
                    if (strpos($line, 'AUTO_INCREMENT') !== false) {
                        preg_match('/^([^ ]+).+/i', $line, $match);
                        $line = sprintf('%s INTEGER PRIMARY KEY NOT NULL', $match[1]);
                    }
                    // 处理字段类型
                    $line = preg_replace('/ NOT\s*NULL/i', '', $line);
                    $line = preg_replace('/ (bigint|int|smallint|tinyint)\([0-9]+\)/i', ' INTEGER', $line);
                    $line = preg_replace('/ (char\([0-9]+\)|(tinytext|text|longtext))/i', ' TEXT', $line);
                    $line = preg_replace('/ enum\([^\)]+\)/i', ' TEXT', $line);
                    $line = preg_replace('/ decimal(\([^\)]+\))/i', ' NUMERIC\1', $line);
                    $line = preg_replace('/ varchar(\([^\)]+\))/i', ' VARCHAR\1', $line);
                    $line = preg_replace('/ timestamp/i', ' TIMESTAMP', $line);
                    $result[] = $line;
                }
                if (version_compare($this->version(),'3.0.0','<')) {
                    $sql = sprintf("%s%s (\n%s\n);", $matches[1], $table, implode(",\n", $result));
                } else {
                    $sql = sprintf("%s (\n%s\n);", $matches[0], implode(",\n", $result));
                }
            }
        }
        // SELECT COUNT(DISTINCT("postid"))
        elseif (version_compare($this->version(),'3.0.0','<') && preg_match('/^(\s*SELECT\s+)COUNT\s*\(\s*(DISTINCT\s*\(\s*[^\)]+\s*\))\s*\)(\s+FROM )/isU', $sql, $matches)) {
            $create_view = preg_replace('/^(\s*SELECT\s+)COUNT\s*\(\s*(DISTINCT\s*\(\s*[^\)]+\s*\))\s*\)(\s+FROM )/is', '\1\2\3', $sql);
            $view_name   = md5($create_view);
            $create_view = sprintf('CREATE TEMP VIEW "%s" AS %s;', $view_name, $create_view);
            $before[]    = $create_view;
            $sql         = sprintf('SELECT COUNT(*) FROM "%s";', $view_name);
        }
        // SELECT
        elseif(preg_match('/^\s*SELECT .+ FROM /is', $sql, $matches)) {
            $sql = preg_replace('/BINARY\s*/i', '', $sql);
        }
        // TODO alter table
        elseif (false) {

        }


        // DATE_ADD
        if (preg_match('/DATE_ADD\((CURDATE\(\)),\s*INTERVAL(\s*[^\)]+\s*)\)/isU', $sql, $match)) {
        	$sql = preg_replace('/DATE_ADD\((CURDATE\(\)),\s*INTERVAL(\s*[^\)]+\s*)\)/isU', 'DATE("NOW", "\2")', $sql);
            $sql = preg_replace('/FROM_UNIXTIME(\([^\)]+\))/i', '\1', $sql);
        }
        // DATE_FORMAT
        if (preg_match('/(SELECT.*?)(DATE_FORMAT)\((\s*[^,]+\s*),\s*(\s*[^\)]+\s*)\)\s*=\s*(DATE_FORMAT)\((\s*[^,]+\s*),\s*(\s*[^\)]+\s*)\)/isU', $sql, $match)) {
            $sql = sprintf('%s strftime(%s, %s)=strftime(%s, %s)', $match[1],$match[4], $match[3], $match[7], $match[6]);

            $sql = preg_replace('/FROM_UNIXTIME(\([^\)]+\))/i', '\1', $sql);
            $sql = preg_replace('/now\(\)/i', 'date("now")', $sql);

        }

        if(preg_match('/TO_DAYS\((.*?)\)/isU',$sql)){

            $sql = preg_replace('/CONVERT_TZ\(([^\,]+)\,[^\,]+\,[^\)]+\)/isU', '\1', $sql);
            $sql = preg_replace('/FROM_UNIXTIME\(([^\)]+)\)/isU', 'datetime(\1, "unixepoch")', $sql);
            $sql = preg_replace('/TO_DAYS\(([^\)]+)\)/isU', 'date( julianday(\1) )', $sql);

        }
        if(preg_match('/[^\w]+YEAR\((.*?)\)/isU',$sql)){
        	$sql = preg_replace('/YEAR\((.*?)\)/isU', 'strftime("%Y", \1)', $sql);
        	$sql = preg_replace('/FROM_UNIXTIME\(([^\)]+)\)/isU', 'datetime(\1, "unixepoch")', $sql);
        }
        if(preg_match('/[^\w]+MONTH\((.*?)\)/isU',$sql)){
        	$sql = preg_replace('/MONTH\((.*?)\)/isU', 'strftime("%m", \1)', $sql);
        	$sql = preg_replace('/FROM_UNIXTIME\(([^\)]+)\)/isU', 'datetime(\1, "unixepoch")', $sql);
        }
        if(preg_match('/[^\w]+DAY\((.*?)\)/isU',$sql)){
        	$sql = preg_replace('/DAY\((.*?)\)/isU', 'strftime("%d", \1)', $sql);
        	$sql = preg_replace('/FROM_UNIXTIME\(([^\)]+)\)/isU', 'datetime(\1, "unixepoch")', $sql);
        }
        if(preg_match('/DATE_SUB\(([^\,]+),\s*INTERVAL([^\)]+)\s*DAY\s*\)/isU', $sql, $match)){
        	//临时解决方案
        	$sql = preg_replace('/WHERE\s*(.*?)\s*BETWEEN\s*DATE_SUB\(([^\,]+),\s*INTERVAL([^\)]+)\s*DAY\s*\)/isU', 'WHERE julianday(date("now")) - julianday(date(\1)) > \3', $sql);
        	$sql = preg_replace('/FROM_UNIXTIME\(([^\)]+)\)/isU', 'datetime(\1, "unixepoch")', $sql);
        }

        return $sql;
    }
    /**
     * where语句组合
     *
     * @param mixed $data where语句，支持数组，数组默认使用 AND 连接
     * @return string
     */
    function where($data) {
        if (empty($data)) {
            return '';
        }
        if (is_string($data)) {
            return $data;
        }
        $cond = array();
        foreach ($data as $field => $value) {
            $cond[] = "(" . $this->identifier($field) ." = '". $this->escape($value) . "')";
        }
        $sql = implode(' AND ', $cond);
        return $sql;
    }
    /**
     * 转义变量
     *
     * @param mixed $value
     * @return string
     */
    function envalue($value) {
        // 空
        if ($value === null) return '';
        // 不是标量
        if (!is_scalar($value)) {
            // 是数组列表
            if (is_array($value) && !is_assoc($value)) {
                $value = implode(',', $value);
            }
            // 需要序列化
            else {
                $value = serialize($value);
            }
        }
        $value = trim($value);
        return $value;
    }
    /**
     * 转义SQL关键字
     *
     * @param string $filed
     * @return string
     */
    function identifier($filed){
        $result = null;
        // 检测是否是多个字段
        if (strpos($filed,',') !== false) {
            // 多个字段，递归执行
            $fileds = explode(',',$filed);
            foreach ($fileds as $k=>$v) {
                if (empty($result)) {
                    $result = $this->identifier($v);
                } else {
                    $result.= ','.$this->identifier($v);
                }
            }
            return $result;
        } else {
            // 解析各个字段
            if (strpos($filed,'.') !== false) {
                $fileds = explode('.',$filed);
                $_table = trim($fileds[0]);
                $_filed = trim($fileds[1]);
                $_as    = chr(32).'AS'.chr(32);
                if (stripos($_filed,$_as) !== false) {
                    $_filed = sprintf("`%s`%s`%s`",trim(substr($_filed,0,stripos($_filed,$_as))),$_as,trim(substr($_filed,stripos($_filed,$_as)+4)));
                }
                return sprintf("`%s`.%s",$_table,$_filed);
            } else {
                return sprintf("`%s`",$filed);
            }
        }
    }
    /**
     * 批量执行 SQL
     *
     * @param string $batSQL
     * @return bool
     */
    function batch($batSQL) {
        if (!$batSQL) return false;
        $batSQL = str_replace("\r\n", "\n", $batSQL);
        $lines  = explode("\n",$batSQL);
        $sql    = '';
        foreach ($lines as $line) {
            $line = trim($line);
            if (preg_match('/;$/', $line)) {
                $sql.= $line;
                // 执行SQL
                $this->query($sql);
                // 执行完，置空
                $sql = '';
            } elseif (!preg_match('/^\-\-/', $line)     // --
                    && !preg_match('/^\/\//', $line)    // //
                    && !preg_match('/^\/\*/', $line)    // /*
                    && !preg_match('/^#/', $line)) {    // #
                if ($pos=strpos($line,'# ') !== false) {
                    $str = trim(substr($line, 0, $pos));
                    if (substr($str, -1) == ',') $line = $str;
                }
                $sql.= $line."\n";
            }
        }
        return true;
    }
}

class DBQuery_NOOP {
    // public
    var $ready  = false;
    var $conn   = null;
    var $sql    = '';
    var $name   = '';
    var $prefix = '';
    var $scheme = null;

    function query($sql) {
        return false;
    }
    function result($sql,$row=0) {
        return null;
    }
    function batch($sql) {
        return null;
    }
    function insert($table,$data) {
        return false;
    }
    function update($table,$sets,$where=null) {
        return false;
    }
    function delete($table,$where=null) {
        return false;
    }
    function truncate($table) {
        return false;
    }
    function is_database($dbname) {
        return false;
    }
    function is_table($table) {
        return false;
    }
    function is_field($table,$field) {
        return false;
    }
    function list_fields($table) {
        return array();
    }
    function fetch($result,$mode=1) {
        return null;
    }
    function version() {
        return null;
    }
    function close() {
        return false;
    }
    function escape($value) {
        return $value;
    }
}