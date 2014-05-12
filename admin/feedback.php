<?php
// 加载公共文件
include dirname(__FILE__).'/admin.php';
// 查询管理员信息
$_USER = user_current();
// 权限验证
$referer = referer(PHP_FILE,false);
// 标题
system_head('title',  '反馈和建议');
//system_head('styles', array('css/user'));
system_head('scripts',array('js/feedback'));
// 动作
$method = isset($_REQUEST['method'])?$_REQUEST['method']:null;

switch ($method) {
	case 'new':
		system_head('title', '提交新反馈');
		system_head('scripts',array('js/issue'));
		system_head('loadevents','issue_manage_init');
		include ADMIN_PATH.'/admin-header.php';
	    issue_manage_page('add');	    
	    include ADMIN_PATH.'/admin-footer.php';
		break;
	case 'edit':
		system_head('title', '编辑问题');
		system_head('scripts',array('js/issue'));
		system_head('loadevents','issue_manage_init');
		include ADMIN_PATH.'/admin-header.php';
	    issue_manage_page('edit');	    
	    include ADMIN_PATH.'/admin-footer.php';
		break;
	case 'view':
		system_head('title', '问题详情');
		system_head('styles', array('css/issue'));
		system_head('scripts',array('js/issue'));
		system_head('loadevents','issue_manage_init');
		include ADMIN_PATH.'/admin-header.php';
	    issue_manage_page('view');	    
	    include ADMIN_PATH.'/admin-footer.php';
		break;
	case 'save':
		$issueid = isset($_REQUEST['issueid'])?$_REQUEST['issueid']:0;
		if (validate_is_post()) {
			$referer	= referer(PHP_FILE,false);
			$title		= isset($_POST['title'])?$_POST['title']:null;
			$content	= isset($_POST['content'])?$_POST['content']:null;
			$parent  	= isset($_POST['parent'])  ? $_POST['parent']:0;
			
			$reply	 	= isset($_POST['reply'])?$_POST['reply']:null;


			validate_check(array(
				array('title',VALIDATE_EMPTY,'标题不能为空。'),
				array('content',VALIDATE_EMPTY,'内容不能为空。')

            )); 
			
			
			if (validate_is_ok()) {
				// 更新
                if ($issueid) {
                    issue_edit($issueid,$title,$content,null,$_USER);
					$result = '反馈信息已更新.';
                   
                }
                // 强力插入
                else {
					issue_add($title,$content,$parent,$_USER);
					if($parent) 
						$result = '你的回复已提交.';
					else
                    	$result = '反馈信息已添加.';
                }
				//
				ajax_success($result, "InfoSYS.redirect('".$referer."');");
			}
		}
		break;
	default:
		system_head('styles', array('css/issue'));
		system_head('scripts',array('js/issue'));
		system_head('loadevents','issue_list_init');
		include ADMIN_PATH.'/admin-header.php';
	    default_page();
	    include ADMIN_PATH.'/admin-footer.php';
		break;
}


function default_page(){
?>

<div class="module-header"><h3><i class="icon-exclamation-sign"></i> 问题反馈</h3></div>
<div class="row-fluid">

  
  <div class="span12">
    <p>如果在使用系统过程中发现问题,比如bug, 请在这儿提交你的反馈, 你也可以提交对系统的一些改进建议, 等等.</p>
    <p><i class="icon-lightbulb"></i> <small>- 每个反馈,请写一个明确的标题,内容尽量的详细, 说明你的每一个操作步骤.</small></p>
    <?php
	$query    = array('page' => '$');
		
	$search   = isset($_REQUEST['query'])?$_REQUEST['query']:'';
	$status   = isset($_REQUEST['status'])?$_REQUEST['status']:'open';
	$order  = isset($_REQUEST['order'])?$_REQUEST['order']:'DESC';
	$orderby   = isset($_REQUEST['orderby'])?$_REQUEST['orderby']:'id';
	$userid		= isset($_REQUEST['userid'])?$_REQUEST['userid']:null;
	$search	  = esc_html(trim($search));
	
	?>
    <ul class="nav nav-tabs">
    	<li class="active"><a href="#issues">所有反馈 (<?php echo issue_count(null);?>)</a></li>
        <div class="pull-right"><a href="<?php PHP_FILE?>?method=new" class="btn btn-success">问题提交</a></div>
    </ul>
    <div class="tab-content">
    	<div class="tab-pane active" id="issues">
        	<div class="table-nav">
              <div class="btn-group">
                  <a class="btn" href="<?php echo PHP_FILE.'?status=open';?>"> (<?php echo issue_count(null,'open');?>) 开放的</a>
                  <a class="btn" href="<?php echo PHP_FILE.'?status=closed';?>"> (<?php echo issue_count(null,'closed');?>) 关闭的</a>
              </div>
              <div class="btn-group">
                <button class="btn">排序</button>
                <button class="btn dropdown-toggle" data-toggle="dropdown">
                  <span class="caret"></span>
                </button>
                <ul class="dropdown-menu">
                  <li><a href="<?php echo PHP_FILE.'?status='.$status.'&orderby=date&order=desc';?>">最新的</a></li>
                  <li><a href="<?php echo PHP_FILE.'?status='.$status.'&orderby=date&order=asc';?>">最前的</a></li>
                </ul>
              </div>
            </div>
            
            <table class="table table-striped table-hover table-issue">

              <tbody>
                <?php
                
                $conditions = array();
				$where = "WHERE `parent`=0";
				
				if($orderby)  $query['orderby'] = $orderby;
				if($order) $query['order'] = $order;
				
				if($status) {
					$query['status'] = $status;
					$where.= sprintf(" AND `status` = '%s'",esc_sql($status));
				}
		
                if ($search) {
                    $query['query'] = $search;
                    $fields = array('action','objectname','description','datetime','objectsubtype');
                    foreach($fields as $field) {
                        $conditions[] = sprintf("BINARY UCASE(`%s`) LIKE UCASE('%%%%%s%%%%')",$field,esc_sql($search));
                    }
                    $where.= ' AND ('.implode(' OR ', $conditions).')';
                }
                if($userid) {
                    $query['userid'] = $userid;
                    $where.= sprintf(" AND (`userid`=%d)",esc_sql($userid));
                }

                $sql = "SELECT DISTINCT(`id`) FROM `#@_issue` {$where} ORDER BY `{$orderby}` {$order}";
        
                //设置每页显示数
                pages_init(30);
                $result = pages_query($sql);
                // 分页地址
                $page_url   = PHP_FILE.'?'.http_build_query($query);
                if ($result) {
                    while ($data = pages_fetch($result)) {
                        $issues	= issue_get($data['id']);
                        $title	= $issues['title'];
						$status = $issues['status']=='open'?true:false;
						
                        $user = user_get_byid($issues['userid']);
						$edit_url = PHP_FILE.'?method=edit&issueid='.$issues['id'];
						$view_url = PHP_FILE.'?method=view&issueid='.$issues['id'];
						
						$actions = '<div class="row-actions">'.$issues['author'].' 于 '.date_gmt('Y-m-d H:i:s',$issues['date']). ($issues['status']=='open'?' 创建 ':' 关闭 '). (issue_comment_count($issues['id'])?'<i class="icon-comments"></i> <a href="'.$view_url.'">'.issue_comment_count($issues['id']).'条回复</a>':'') . '</div>';
						
                        echo '<tr>';
                        echo   '<td style="width:20px;padding-top:10px;">'.($issues['status']=='open'?'<i class="icon-exclamation-sign" title="处理中的问题" style="font-size:18px;"></i>':'<i class="icon-remove-circle" title="问题已关闭" style="font-size:18px;"></i>').'</td>';
						echo   '<td> <strong><a href="'.$view_url.'">'.$issues['title'].'</a></strong> '.$actions.'</td>';
                        echo '</tr>';
                    }
                } else {
					echo '<div class="none"><p>没有问题可显示. <a href="'.PHP_FILE.'?method=new">新建一个问题.</a></p></div>';
				}
                ?>
              </tbody>
            </table>
        </div>
    </div>


    <?php echo pages_list($page_url);?>
  </div>
</div>

<?php		
}//end default page

/**
 * 管理页面
 *
 * @param string $action
 */
function issue_manage_page($action){
	global $php_file, $_USER;
    $referer = referer(PHP_FILE);
	
    $issueid  = isset($_GET['issueid'])?$_GET['issueid']:0;
	if ($action!='add') {
    	$_DATA 	  = issue_get($issueid);
    }
    
	
    $title = isset($_DATA['title'])?$_DATA['title']:null;
    $content = isset($_DATA['content'])?$_DATA['content']:null;
	$author  = isset($_DATA['author'])?$_DATA['author']:null;
	$date	 = isset($_DATA['date'])?$_DATA['date']:null;
	$status	 = isset($_DATA['status'])?$_DATA['status']:null;
	$mail 	 = isset($_DATA['mail'])?$_DATA['mail']:null;
	$userid  = isset($_DATA['userid'])?$_DATA['userid']:null;
	echo	'<div class="module-header">';
	echo    	'<h3>'.($action=='add'?'<i class="icon-plus"></i> ':'<i class="icon-question-sign"></i> ').system_head('title').'</h3>';
	echo	'</div>';
	
    echo '<div class="wrap">';
    echo   '<form action="'.PHP_FILE.'?method=save" method="post" name="issuemanage" id="issuemanage">';
    echo     '<fieldset>';
	
	if($action=='view') {
		echo '<div class="discussion-bubble">';
		echo '<img class="discussion-bubble-avatar" height="48" src="'.get_user_avatar($userid,48).'" width="48">';
		echo   '<div class="discussion-bubble-content bubble">';
		echo     '<div class="discussion-bubble-inner">';
		echo       '<div class="discussion-topic-header">';
		echo         '<h2 class="discussion-topic-title">'.$title.'</h2>';
		echo 		 '<input type="hidden" name="title" value='.$title.'>';
		echo       '</div>';
		echo       '<div class="discusion-topic-infobar">';
		echo         '<span class="text"><a href="/intel352"><strong>'.$author.'</strong></a> 于 '.date_gmt('Y-m-d H:i:s',$date).($status=='open'?' 创建 ':' 关闭 ').'</span>';
		echo       '</div>';
		echo       '<div class="comment-content">';
		echo	     '<div class="comment-body">';
		echo		   '<p>'.nl2br($content).'</p>';
		echo         '</div>';
		if($status=='closed') {
			echo	     '<div class="issue-status status-closed">';
			echo		   '<p><i class="icon-bookmark-empty"></i> <strong>问题已经关闭</strong> —  如果有新的问题, 请新创建一个(<a href="'.PHP_FILE.'?method=new">新建问题</a>)</p>';
		echo         '</div>';
		} else {
			echo	     '<div class="issue-status status-open">';
			echo		   '<p><i class="icon-info-sign"></i> <strong>提示: </strong>如果当前问题已经解决, 请关闭该问题!</p>';
		echo         '</div>';
		}
		echo       '</div>';
		echo     '</div>';
		echo   '</div>';
		echo '</div>';
		//
		$where  = 'WHERE `parent`='.$issueid;
		$query  = array('page' => '$');
		
		//$reply = issue_get_trees($issueid);
		
		//echo 'print_r';
		//print_r($reply);
		$db = get_conn();
		$result = $db->query("SELECT * FROM `#@_issue` {$where} ORDER BY `id` ASC");
		if ($result) {
			$floor = 1;
			while ($data = $db->fetch($result)) {
				$data['author'] = $data['author'] ? $data['author'] : 'Anonymous';
				if ($data['parent']) {
                    $parent = issue_get($data['parent']);
                    $parent['author'] = $parent['author'] ? $parent['author'] : 'Anonymous';
                    $reply  = sprintf('回复 <a href="%s">%s</a>.', 'javascript:;', $parent['author']);
                } else {
                    $reply  = '';
                }
				
				echo '<div class="discussion-bubble">';
				echo '<img class="discussion-bubble-avatar" height="48" src="'.get_user_avatar($data['userid'],48).'" width="48">';
				echo   '<div class="discussion-bubble-content bubble">';
				echo     '<div class="discussion-bubble-inner">';
				echo       '<div class="comment-header normal-comment-header">';
				echo         '<i class="icon-comment"></i> ';
				echo         '<a href="#" class="comment-header-author">'.$data['author'].' </a> 回复说: #'.$floor;
				echo         '<span class="comment-header-right"><time>'.date_gmt('Y-m-d H:i:s',$data['date']).'</time></span>';
				echo       '</div>';
				echo       '<div class="comment-content">';
				echo	     '<div class="comment-body">';
				echo		   '<p>'.nl2br($data['content']).'</p>';
				echo         '</div>';
				echo       '</div>';
				echo     '</div>';
				echo   '</div>';
				echo '</div>';
				$floor++;
			}
		}
		//提交评论的地方
		if($status!='closed') {
			echo '<div class="discussion-bubble">';
			echo '<img class="discussion-bubble-avatar" height="48" src="'.get_user_avatar($_USER['userid'],48).'" width="48">';
			echo   '<div class="discussion-bubble-content bubble">';
			echo     '<div class="discussion-bubble-inner">';
			echo       '<div class="comment-content">';
			echo	     '<div class="comment-body">';
			echo		   '<textarea name="content" id="content" tabindex="1" placeholder="添加你的回复信息" style="width: 100%; height:250px;-moz-box-sizing: border-box;box-sizing: border-box;"></textarea>';
			echo         '</div>';
			echo       '</div>';
			echo     '</div>';
			echo   '</div>';
			echo '</div>';
		} else {
			echo '<hr />';
		}
	}else {
		echo				'<div class="control-group">';
		echo					'<div class="controls">';
		echo						'<input type="text" name="title" id="title" class="span6" placeholder="标题" value="'.$title.'">';
		echo					'</div>';
		echo				'</div>';
		echo				'<div class="control-group">';
		echo					'<div class="controls">';
		echo editor('content',$content);
		//echo						'<textarea type="text" name="content" id="content" class="span6" rows="10" placeholder="详细信息">'.$content.'</textarea>';
		echo					'</div>';
		echo				'</div>';
	}
	
    echo   '</fieldset>';
	$class ='';
	if($status=='closed' && $action=='view') {
		$class=' style="text-align:center;"';
	}
    echo   '<p class="submit" '.$class.'>';
    if ($action=='add') {
        echo   '<button type="submit" class="btn btn-primary">提交反馈</button>';
    } elseif ($action=='view') {
		if($status!='closed') {
			echo   '<button type="submit" class="btn btn-primary" style="margin-left:63px;">提交回复</button><input type="hidden" name="parent" value="'.$issueid.'" />';
		} else {
			echo   '<a href="'.PHP_FILE.'?method=new" class="btn btn-primary">新建问题</a>';
		}
	}
	else {
        echo   '<button type="submit" class="btn btn-primary">更新反馈</button><input type="hidden" name="issueid" value="'.$issueid.'" />';
    }
    echo       '  <button type="button" class="btn" onclick="InfoSYS.redirect(\''.$referer.'\')">返回</button>';
	if($status!='closed' && $action!='add') {
		echo   	   '<a href="#" id="close-issue" class="btn btn-warning pull-right">关闭该问题</a>';
	}
    echo   '</p>';
    echo  '</form>';
    echo '</div>';
}
?>