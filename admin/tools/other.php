<?php
// 加载公共文件
include dirname(__FILE__).'/../admin.php';
// 查询管理员信息
$_USER = user_current();
// 权限验证
current_user_can('clean-cache');
$referer = referer(PHP_FILE,false);
// 标题
system_head('title',  '其它工具');
//system_head('styles', array('css/user'));
system_head('scripts',array('js/tools'));

?>
<?php
include ADMIN_PATH.'/admin-header.php';

echo '<div class="module-header">';
echo	'<h3><i class="icon-food"></i> 其它工具</h3>';
echo '</div>';
?>
<div class="tabbable">
		<ul class="nav nav-tabs">
			<li class="active"><a href="#clear-cache" data-toggle="tab"><i class="icon-trash"></i> 删除缓存</a></li>
            <li><a href="#error-log" data-toggle="tab"><i class="icon-remove-sign"></i> 错误日志</a></li>
            <li><a href="#site-belong" data-toggle="tab"><i class="icon-refresh"></i> 重置网站所属人</a></li>
		</ul>
		<div class="tab-content">
			<div class="tab-pane active" id="clear-cache">
					<div class="well" style="width:400px">
						<p>缓存可以提高你的访问速度。界面出现问题后，请联系系统管理员(<span class="text-info">sbmzhcn@gmail.com</span>)，以修复问题。</p>
						<p style="margin-top:15px"><button class="btn btn-danger" id="clearCache"><i class="icon-trash icon-white"></i> 删除缓存</button></p>
            		</div>
			</div>
            
            <div class="tab-pane" id="error-log">
				<?php echo showErrLog(); ?>
				<br />
				<button id="clearErrLog" class="btn btn-warning">清除错误记录</button>
			</div>
            <div class="tab-pane" id="site-belong">
				<div class="well" style="width:350px;padding-bottom:10px">
					<p><span class="redetermineMessage">是否要重新计算网站所属人？</span></p>
					<p style="margin-top: 20px;">
						<button class="btn btn-small" id="redetermine">重新计算网站所属人</button>
					</p>
				</div>
			</div>
		</div>
	</div>
<?php		
include ADMIN_PATH.'/admin-footer.php';
?>