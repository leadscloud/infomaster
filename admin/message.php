<?php
// 加载公共文件
include dirname(__FILE__).'/admin.php';
// 查询管理员信息
$_USER = user_current();
// 权限验证
$referer = referer(PHP_FILE,false);
// 标题
system_head('title',  '收件箱');
system_head('styles', array('css/message'));
system_head('scripts',array('js/message'));
system_head('body_class','pages-messages');
// 动作
$method = isset($_REQUEST['method'])?$_REQUEST['method']:null;
//print_r($_USER);
switch ($method) {
	case 'new':
		system_head('title', '发送消息');
		system_head('loadevents','pm_manage_init');
		include ADMIN_PATH.'/admin-header.php';
	    pm_manage_page('add');	    
	    include ADMIN_PATH.'/admin-footer.php';
		break;
	case 'view':
		system_head('title', '查看消息');
		include ADMIN_PATH.'/admin-header.php';
	    pm_manage_page('view');	    
	    include ADMIN_PATH.'/admin-footer.php';
		break;
	case 'delete':
	    $listids = isset($_POST['listids'])?$_POST['listids']:null;
	    if (empty($listids)) {
	    	ajax_error('你没有选择任何项目。');
	    }
		foreach ($listids as $pmid) {
			message_delete($pmid);
		}
		ajax_success('信息已删除',"InfoSYS.redirect('".referer()."');");
		break;
	case 'mark':
		$status  = isset($_POST['status'])?$_POST['status']:null;
	    $listids = isset($_POST['listids'])?$_POST['listids']:null;
	    if (empty($listids)) {
	    	ajax_error('你没有选择任何项目。');
	    }
		foreach ($listids as $pmid) {
			message_edit($pmid,array('status'=>$status));
		}
		ajax_success('标记成功',"InfoSYS.redirect('".referer()."');");
		break;
	case 'save':
		$pmid = isset($_REQUEST['pmid'])?$_REQUEST['pmid']:0;
		if (validate_is_post()) {
			$referer	= referer(PHP_FILE,false);
			$fromuser	= $_USER['userid'];
			$touser		= isset($_POST['touser'])?$_POST['touser']:null;
			$subject	= isset($_POST['subject'])?$_POST['subject']:null;
			$message  	= isset($_POST['message'])?$_POST['message']:null;
			
			$tags	 	= isset($_POST['tags'])?$_POST['tags']:array();
			
			validate_check(array(
                    array('subject',VALIDATE_EMPTY,'主题不能为空.'),
                    array('message',VALIDATE_EMPTY,'请输入你要发送的信息.'),
                ));
			
			if (validate_is_ok()) {
				$user = user_get_byname(trim($touser));
				$touser = $user['userid'];
				$data = array(
						'to_user' => $touser,
						'from_user' => $fromuser,
						'subject' => $subject,
						'message' => $message,
						'date' => time()
				);
				if(!$pmid) {
					message_add($data);
					$result = '信息已经发送给'.$user['name'];
					ajax_success($result,"InfoSYS.redirect('".PHP_FILE."');");
				}
			}
		}
		break;
	default:
		//system_head('styles', array('css/message'));
		//system_head('scripts',array('js/message'));
		system_head('loadevents','pm_list_init');
		include ADMIN_PATH.'/admin-header.php';
	    default_page();
	    include ADMIN_PATH.'/admin-footer.php';
		break;
}


function default_page(){
	global $_USER;
	$query    = array('page' => '$');
		
	$search   = isset($_REQUEST['query'])?$_REQUEST['query']:'';
	$status   = isset($_REQUEST['status'])?$_REQUEST['status']:'open';
	$order  = isset($_REQUEST['order'])?$_REQUEST['order']:'DESC';
	$orderby   = isset($_REQUEST['orderby'])?$_REQUEST['orderby']:'id';
	$userid		= isset($_REQUEST['userid'])?$_REQUEST['userid']:null;
	$search	  = esc_html(trim($search));
	
	$type   = isset($_REQUEST['type'])?$_REQUEST['type']:'unread';
?>

<div class="module-header"><h3><i class="icon-envelope-alt"></i> 收件箱</h3></div>
<div class="row-fluid">
  <div class="span4">
    <ul class="well nav nav-tabs nav-stacked">
      <li<?php echo $type=='unread'?' class="active"':'';?>>
          <a href="message.php?type=unread"><i class="icon-envelope-alt"></i> 未读 <span class="badge pull-right <?php echo $type=='unread'?' badge-info':'';?>"><?php echo message_count('unread');?></span></a>
      </li>
      <li<?php echo $type=='read'?' class="active"':'';?>>
          <a href="message.php?type=read"><i class="icon-envelope"></i> 已读 <span class="badge pull-right <?php echo $type=='read'?' badge-info':'';?>"><?php echo message_count('read');?></span></a>
      </li>
      <li<?php echo $type=='inbox'?' class="active"':'';?>>
          <a href="message.php?type=inbox"><i class="icon-download-alt"></i> 收件箱 <span class="badge pull-right <?php echo $type=='inbox'?' badge-info':'';?>"><?php echo message_count('inbox');?></span></a>
      </li>
      <li<?php echo $type=='sent'?' class="active"':'';?>>
          <a href="message.php?type=sent"><i class="icon-upload-alt"></i> 发信箱 <span class="badge pull-right <?php echo $type=='sent'?' badge-info':'';?>"><?php echo message_count('sent');?></span></a>
      </li>
      
      
      

    </ul>
    <hr />
    <form class="form-search row-fluid" _lpchecked="1">
    <span class="input-icon">
      <input type="text" name="query" placeholder="搜索信息..." class="search-query input-block-level">
      <i class="icon-search"></i>
    </span>
    </form>
  </div><!--span-->
  
  
  <div class="span8">
    <?php
	
	
	?>
    <ul class="nav nav-tabs">
    	<li class="active"><a href="#pm"><i class="icon-download-alt"></i> 所有信息 (<?php echo message_count('all');?>)</a></li>
        <div class="pull-right"><a href="<?php PHP_FILE?>?method=new" class="btn btn-success">新建消息</a></div>
    </ul>
    <div class="tab-content">
    	<div class="tab-pane active" id="pms">
        	<div class="table-nav">
              
              <div class="btn-group">
                <button class="btn" onclick="selectAll(true);">全选</button>
                <button class="btn dropdown-toggle" data-toggle="dropdown">
                  <span class="caret"></span>
                </button>
                <ul class="dropdown-menu">
                  <li><a href="#" onclick="inverseSelect();">反选</a></li>
                  <li><a href="#" onclick="selectAll(false);">取消所有</a></li>
                </ul>
              </div>
              <div class="btn-group">
                  <button class="btn">动作</button>
                  <button class="btn dropdown-toggle" data-toggle="dropdown">
                    <span class="caret"></span>
                  </button>
                  <ul class="dropdown-menu">
                    <li><a href="#" onclick="markMessage('read');">标记已读</a></li>
                    <li><a href="#" onclick="markMessage('unread');">标记未读</a></li>
                    <li class="divider"></li>
                    <li><a href="#" onclick="deleteMessage();">删除</a></li>
                  </ul>
              </div>
              
            </div>
            
            <table class="table table-striped table-hover table-meesage">

              <tbody>
                <?php
				
                
                $conditions = array();
				$where = "WHERE 1";
				
				if($orderby)  $query['orderby'] = $orderby;
				if($order) $query['order'] = $order;
				
		
                if ($search) {
                    $query['query'] = $search;
                    $fields = array('subject','message');
                    foreach($fields as $field) {
                        $conditions[] = sprintf("BINARY UCASE(`p`.`%s`) LIKE UCASE('%%%%%s%%%%')",$field,esc_sql($search));
                    }
                    $where.= ' AND ('.implode(' OR ', $conditions).')';
                }
				
				switch($type) {
					case 'inbox':
						$where.= sprintf(" AND `to_user`=%d",$_USER['userid']);
						break;
					case 'sent':
						$where.= sprintf(" AND `from_user`=%d",$_USER['userid']);
						break;
					case 'read':
						$where.= " AND `p`.`status`='read'";
						$where.= sprintf(" AND `to_user`=%d",$_USER['userid']);
						break;
					case 'unread':
						$where.= " AND `p`.`status`='unread'";
						$where.= sprintf(" AND `to_user`=%d",$_USER['userid']);
						break;
				}

				
                $sql = "SELECT p.id, p.subject, p.message, p.date, p.status, u.name as sender, u.userid as userid FROM #@_messages as p INNER JOIN #@_user as u ON userid = p.from_user {$where} ORDER BY p.id DESC";
        
                //设置每页显示数
                pages_init(30);
                $result = pages_query($sql);
                // 分页地址
                $page_url   = PHP_FILE.'?'.http_build_query($query);
                if ($result) {
                    while ($data = pages_fetch($result)) {
						
						$date = date_gmt('Y-m-d-H:i:s', $data['date']);
						$time = processtime($data['date']);
						
						$subject = $data['subject'];
						
						if (intval(mb_strlen($subject,'UTF-8')) > 10) {
							$subject = mb_substr($subject, 0, 10, 'UTF-8') . '...';
						}
						
						$message = $data['message'];
						
						if (intval(mb_strlen($message,'UTF-8')) > 30) {
							$message = mb_substr($message, 0, 30, 'UTF-8') . '...';
						}
						
						$icon = ($data['status'] == 'unread' ? '<i class="icon-envelope-alt" title="未读"></i>' : '<i class="icon-envelope" title="已读"></i>');
						
						$view_url = PHP_FILE.'?method=view&pmid='.$data['id'];
                        echo '<tr>';
						echo   '<td class="check-column"><input type="checkbox" name="listids[]" value="'.$data['id'].'" /></td>';
						echo   '<td>'.$data['sender'].'</td>';
                        echo   '<td> '.$icon.' <a href="'.$view_url.'"><strong>'.$subject.'</strong> - <span> '.$message.'</span></a></td>';
						echo   '<td title="'.$date.'">'.$time.'</td>';
                        echo '</tr>';
                    }
                } else {
					echo '<div class="none"><p>没有任何信息. <a href="'.PHP_FILE.'?method=new">发送私信.</a></p></div>';
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
function pm_manage_page($action){
	global $php_file,$_USER;
    $referer = referer(PHP_FILE);
	
    $pmid  = isset($_GET['pmid'])?$_GET['pmid']:0;
	$sender 	= isset($_GET['touser'])?$_GET['touser']:null;
	$disable 	= '';
	if ($action!='add') {
    	$_DATA 	  = message_get($pmid);
		$disable  = ' disabled="disabled"';
    }
	
	
    
    $fromuser 	= isset($_DATA['from_user'])?$_DATA['from_user']:null;
    $subject 	= isset($_DATA['subject'])?$_DATA['subject']:null;
	$message 	= isset($_DATA['message'])?$_DATA['message']:null;
	$date	 	= isset($_DATA['date'])?$_DATA['date']:null;
	$status	  	= isset($_DATA['status'])?$_DATA['status']:null;

	if( $fromuser ) {
		$sender = user_get_byid($fromuser);
		$sender = $sender['name'];
	}
	

	//设置为已读
	if(!empty($_DATA['status']) && $_DATA['status'] == 'unread' && $_DATA['to_user'] == $_USER['userid']){
		message_edit($pmid,array('status'=>'read'));
	}
	
	
	echo	'<div class="module-header">';
	echo    	'<h3>'.($action=='add'?'<i class="icon-plus"></i> ':'<i class="icon-question-sign"></i> ').system_head('title').'</h3>';
	echo	'</div>';
	
    echo '<div class="wrap">';
    echo   '<form action="'.PHP_FILE.'?method=save" method="post" name="pmmanage" id="pmmanage">';
    echo     '<fieldset>';
	

	echo				'<div class="control-group">';
	if ($action=='add') {
		echo				'<label class="control-label">目标用户</label>';
	}else {
		echo				'<label class="control-label">发送者</label>';
	}
	echo					'<div class="controls">';
	echo						'<input type="text" name="touser" id="touser" class="span6" placeholder="填写正确的用户名" value="'.$sender.'"'.$disable.'>';
	echo					'</div>';
	echo				'</div>';
	echo				'<div class="control-group">';
	echo					'<label class="control-label">主题</label>';
	echo					'<div class="controls">';
	echo						'<input type="text" name="subject" id="subject" class="span6" placeholder="主题" value="'.$subject.'"'.$disable.'>';
	echo					'</div>';
	echo				'</div>';
	echo				'<div class="control-group">';
	echo					'<label class="control-label">内容</label>';
	echo					'<div class="controls">';
	echo						'<textarea type="text" name="message" id="message" class="span6" rows="10" placeholder="内容"'.$disable.'>'.$message.'</textarea>';
	echo					'</div>';
	echo				'</div>';
	
    echo   '</fieldset>';

    echo   '<p class="submit">';
    if ($action=='add') {
        echo   '<button type="submit" class="btn btn-primary">发送</button>';
    }
	else {
		echo   '<a href="'.PHP_FILE.'?method=new&touser='.$sender.'" class="btn btn-primary">回复信息</a>';
        echo   '<input type="hidden" name="pmid" value="'.$pmid.'" />';
    }
    echo       '  <button type="button" class="btn" onclick="InfoSYS.redirect(\''.$referer.'\')">返回</button>';

    echo   '</p>';
    echo  '</form>';
    echo '</div>';
}

function processtime($unix, $showlabel = false, $countdown = false){
	$min = 60;
	$hour = 3600;
	$day = 86400;
	
	$diff = time() - $unix;
	$diff2 = $diff;
	
	if($countdown){
		$diff = $unix - time();
		$diff2 = time();
	}

	$days = floor($diff / $day);
	$days = floor($diff / $day);
	$diff = $diff-($day * $days);
	$hours = floor($diff / $hour);
	$diff = $diff-($hour * $hours);
	$minutes = floor($diff / $min);
	$diff = $diff-($min * $minutes);
	$seconds = $diff;
	
	$m = ' 分钟';
	$h = ' 小时';
	$d = ' 天';
	

	if($diff2 < 60) {
		$timest = $diff.' 秒之前.';
	}else{
		if($minutes >= 1){
			$timest = $minutes.$m.' '.(!$countdown ? '前' : '');
		}
		if($hours >= 1){
			$timest = $hours.$h.' '.(!$countdown ? '前' : '');
		}
		if($days >= 1){
			$timest = $days.$d.' '.(!$countdown ? '前' : '');
		}
		if(!isset($timest)){
			$timest = '';
		}
	}

	if($timest == ''){
		$timest = (!$countdown ? '刚刚.' : 'Any second');
	}
	
	if($showlabel){
		if($countdown){
			$now = $unix-time();
		}else{
			$now = time()-$unix;
		}
		switch(true){
			case($now <= 10800): //3 hours
				$labeltype = 'label-success';
				break;
			case($now > 10800 && $now <= 86400): //24 hours
				$labeltype = 'label-info';
				break;
			case($now > 86400 && $now <= 259200): //3 days
				$labeltype = 'label-warning';
				break;
			case($now > 259200 && $now <= 604800): //7 days
				$labeltype = 'label-important';
				break;
			case($now > 604800): //7 days and more
			default:
				$labeltype = '';
				break;
		}
		
		$timest = '<span class="label '.$labeltype.'">'.$timest.'</span>';
	}
	
	return $timest;
}
?>