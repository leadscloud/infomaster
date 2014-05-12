  </div>
    </div><!-- /container --> 
    <div id="settings-container">
        <div class="btn btn-app btn-mini btn-warning" id="settings-btn">
            <i class="icon-cog"></i>
        </div>

        <div id="settings-box" class="">

            <div>
                <input type="checkbox" class="checkbox-2" checked="checked" id="settings-header">
                <label class="lbl" for="settings-header"> 固定头部</label>
            </div>

            <div>
                <input type="checkbox" class="checkbox-2" checked="checked" id="settings-sidebar">
                <label class="lbl" for="settings-sidebar"> 固定侧边栏</label>
            </div>

            <div>
                <input type="checkbox" class="checkbox-2" id="settings-breadcrumbs">
                <label class="lbl" for="settings-breadcrumbs"> 固定面包屑导航</label>
            </div>
        </div>
    </div><!--/#settings-container-->
    </div><!-- /#main-container -->
<?php
echo '<script>var baseurl = "'.ROOT.'admin/", username = "'.$_USER['name'].'", AccCheckIntval = 30000,logged = true;</script>';
// 加载JS核心库
loader_js('js/common');
// 加载模块JS库
loader_js(system_head('scripts'));
?>
<script type="text/javascript">
//<![CDATA[
window.addLoadEvent=function(a){if(typeof jQuery!='undefined')jQuery(document).ready(a);else if(typeof InfoOnload!='function')InfoOnload=a;else{var b=InfoOnload;InfoOnload=function(){b();a()}}};
<?php
// 执行事件
$loadevents = system_head('loadevents');
if ($loadevents) {
    if (is_array($loadevents)) {
    	foreach ($loadevents as $event) {
    		echo "addLoadEvent({$event});";
    	}
    } else {
        echo "addLoadEvent({$loadevents});";
    }
}
?>
//]]>
</script>
<script type="text/javascript">if(typeof InfoOnload=='function') InfoOnload();</script>
</body>
</html>