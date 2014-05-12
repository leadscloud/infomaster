<?php
defined('COM_PATH') or die('Restricted access!');
/**
 * 分页类
 */
class Pages {
    var $_db = null;
    var $total, $pages, $page, $size, $length;
    
    function Pages() {
        $args = func_get_args();
		call_user_func_array( array(&$this, '__construct'), $args );
	}
    /**
     * 初始化
     *
     * 接收 page,size 变量
     *
     * @return void
     */
    function __construct($size=null, $page=null){
        $this->size = $size===null ? 20 : $size;

        if ($page === null) {
            $this->page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
        } else {
            $this->page = $page;
        }
        
        $this->page = $this->page<1 ? $page : $this->page;
        $this->size = $this->size<1 ? $size : $this->size;
        $this->_db  = get_conn();
    }
    /**
     * 执行查询
     *
     * @param string $sql
     * @return mixed
     */
    function query($sql) {
        $csql = preg_replace_callback(
                    '/SELECT (.+) FROM/iU',
                    create_function('$matches','
                        if (preg_match(\'/DISTINCT\s*\([^\)]+\)/i\',$matches[1], $match)) {
                            $field = $match[0];
                        } else {
                            $field = "*";
                        }
                        return sprintf("SELECT COUNT(%s) FROM", $field);
                    '),
                    rtrim($sql, ';'), 1
                );
        $csql = preg_replace('/\sORDER\s+BY.+$/i', '', $csql, 1);

        $sql .= sprintf(' LIMIT %d OFFSET %d;', $this->size, ($this->page - 1) * $this->size);
        // 执行结果
        $result = $this->_db->query($sql);
        // 总记录数
        $this->total  = $this->_db->result($csql);
        $this->pages  = ceil($this->total/$this->size);
        $this->pages  = ((int)$this->pages == 0) ? 1 : $this->pages;
        if ((int)$this->page < (int)$this->pages) {
            $this->length = $this->size;
        } elseif ((int)$this->page == (int)$this->pages) {
            $this->length = $this->total - (($this->pages-1) * $this->size);
        } else {
            $this->length = 0;
        }
        if ($this->total==0 || $this->length==0) $result = false;
        return $result;
    }
    /**
     * 取得数据集
     *
     * @param  $resource
     * @param int $type
     * @return array|null
     */
    function fetch($resource,$type=1) {
        if (is_resource($resource) || is_object($resource)) {
            return $this->_db->fetch($resource,$type);
        }
    }
    /**
     * 分页信息
     *
     * @return array
     */
    function info() {
        return array(
            'page'   => $this->page,
            'size'   => $this->size,
            'total'  => $this->total,
            'pages'  => $this->pages,
            'length' => $this->length,
        );
    }
    /**
     * 清理分页信息
     *
     * @return void
     */
    function close() {
        $this->total = $this->pages = $this->length = 0;
    }
    
    /**
     * 分页函数
     *
     * @param string $url   url中必须包含$特殊字符，用来代替页数
     * @param string $mode  首页丢弃模式
     * @return string
     */
    function page_list($url,$mode='$'){
        $html = '';
        $this->page   = abs(intval($this->page));
        $this->page   = $this->page<1 ? 1  : $this->page;
        $this->pages  = abs(intval($this->pages));
        $this->length = abs(intval($this->length));
        if (strpos($url,'%24') !==false )
            $url = str_replace('%24','$',$url);
        if (strpos($url,'$')===false || $this->length==0)
            return ;
        $start = instr($mode,'!$,!_$') ? '' : 1;
        if ($this->page > 2) {
            $html.= '<li><a href="'.str_replace('$',$this->page-1,$url).'">&laquo;</a></li>';
        } elseif ($this->page==2) {
            if ($mode == '!_$') {
                $html.= '<li><a href="'.str_replace('_$',$start,$url).'">&laquo;</a></li>';
            } else {
                $html.= '<li><a href="'.str_replace('$',$start,$url).'">&laquo;</a></li>';
            }
        }
        if ($this->page > 3) {
            if ($mode == '!_$') {
                $html.= '<li><a href="'.str_replace('_$',$start,$url).'">1</a><span>&#8230;</span></li>';
            } else {
                $html.= '<li><a href="'.str_replace('$',$start,$url).'">1</a><span>&#8230;</span></li>';
            }
        }
        $before = $this->page-2;
        $after  = $this->page+7;
        for ($i=$before; $i<=$after; $i++) {
            if ($i>=1 && $i<=$this->pages) {
                if ((int)$i==(int)$this->page) {
                    $html.= '<li class="active"><span>'.$i.'</span></li>';
                } else {
                    if ($i==1) {
                        if ($mode == '!_$') {
                            $html.= '<li><a href="'.str_replace('_$',$start,$url).'">'.$i.'</a></li>';
                        } else {
                            $html.= '<li><a href="'.str_replace('$',$start,$url).'">'.$i.'</a></li>';
                        }
                    } else {
                        $html.= '<li><a href="'.str_replace('$',$i,$url).'">'.$i.'</a></li>';
                    }
                }
            }
        }
        if ($this->page < ($this->pages-7)) {
            $html.= '<li><span>&#8230;</span><a href="'.str_replace('$',$this->pages,$url).'">'.$this->pages.'</a></li>';
        }
        if ($this->page < $this->pages) {
            $html.= '<li><a href="'.str_replace('$',$this->page+1,$url).'">&raquo;</a></li>';
        }
        return '<div class="pages pagination pagination-right"><ul>'.$html.'</ul></div>';
    }
}

/**
 * 分页实例
 *
 * @return Pages
 */
function &_pages_get_object($size=null, $page=null) {
    static $pages;
	if ( is_null($pages) )
		$pages = new Pages($size, $page);
	return $pages;
}
/**
 * 初始化分页类
 *
 * @param int $size
 * @param int $page
 * @return Pages
 */
function pages_init($size=10, $page=null) {
    return _pages_get_object($size, $page);
}
/**
 * 执行分页查询
 *
 * @param string $sql
 * @return mixed
 */
function pages_query($sql) {
    $pages = _pages_get_object();
    return $pages->query($sql);
}
/**
 * 取得数据集
 *
 * @param resource $resource
 * @param int $type
 * @return array|null
 */
function pages_fetch($resource, $type=1) {
    $pages = _pages_get_object();
    return $pages->fetch($resource, $type);
}

if (!function_exists('pages_list')) :
/**
 * 分页列表
 *
 * @param string $url   $ 代表当前页数
 * @param string $mode  首页丢弃模式
 * @param int $page     当前页数
 * @param int $total    总页数
 * @param int $length   当前页记录数
 * @return string
 */
function pages_list($url,$mode='$',$page=null,$total=null,$length=null) {
    $pages = _pages_get_object();
    if ($page !== null)   $pages->page   = $page;
    if ($total !== null)  $pages->pages  = $total;
    if ($length !== null) $pages->length = $length;
    return $pages->page_list($url, $mode);
}
endif;
/**
 * 分页信息
 *
 * @return array
 */
function pages_info() {
    $pages = _pages_get_object();
    return $pages->info();
}
/**
 * 清理分页信息
 *
 * @return void
 */
function pages_close() {
    $pages = _pages_get_object();
    return $pages->close();
}
