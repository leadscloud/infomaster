<?php
$email['title'] = '重置密码确认(SBM)';
$email['body'] = '<h4>重置密码确认</h4>
<p>我们收到IP地址为: {{visitor_ip}} 的密码重置请求,重置账户为: {{username}}<br />
如果确认重置密码,请点击以下链接,或者把此链接复制到浏览器地址里按回车键"Enter".</p>
<p><a href="{{site_url}}/admin/login.php?method=reset&reset={{resetcode}}">{{site_url}}/admin/login.php?method=reset&reset={{resetcode}}</a></p>
<hr />
<p>如果你没有请求过重置密码服务,请可以放心的删除此邮件, 没有确认的重置请求,将在2小时后失效.</p>
<p>感谢你使用本系统,<br /><a href="{{site_url}}">{{website_name}}</a></p>';
$email['variables'] = 'website_name,site_url,username,email,resetcode,visitor_ip';
