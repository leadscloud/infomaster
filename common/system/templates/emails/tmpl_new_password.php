<?php
$email['title'] = '新密码';
$email['body'] = '<h4>新密码</h4>
<p>你的新密码是: <b>{{newpassword}}</b></p>
<p>你可以在登陆成功后,于个人资料里修改你的密码.</p>
<p>感谢你使用本系统,<br /><a href="{{site_url}}">{{website_name}}</a></p>';
$email['variables'] = 'website_name,site_url,username,email,newpassword,visitor_ip';
