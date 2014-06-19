# 询盘管理系统 #


程序如果在运行时，请把程序的错误级别设置为任何错误都不显示。具体在`global.php`文件里设置。

##  安装步骤:  ##

1. 把压缩文件解压到空目录，然后上传所有文件。
2. 在浏览器中打开 [`admin/install.php`][1] 。 它将会引导你安装，并根据你的数据库设置创建一个配置文件 `config.php` 。
  1. 由于某些原因可能无法创建配置文件，请编辑`common/config.sample.php`，然后设置好你的数据库配置。
  2. 保存这个文件为 `config.php` 然后上传到根目录.
  3. 打开 [`admin/install.php`][1] 重新安装 。
3. 如果有任何错误，请删除你的配置文件 config.php, 并重新安装。 如果再次失败，请联系作者得到更多帮助信息。
4. 为了安全起见，请不要使用admin/admin作为你的用户名或密码，也不要使用你的生日或一些常用数字作为密码。

  [1]: admin/install.php        "点击安装"

## 技术支持 ##

使用邮件联系我

如果遇到任何问题，请联系 [sbmzhcn@gmail.com](sbmzhcn@gmail.com "给我发送邮件")

1. 邮件标题格式为： 信息系统错误 -- 你的问题 -- 你的名字
2. 邮箱内容中请把详细错误信息复制下来，并详细描述你遇到的问题。如果有图片截图更好。
3. 请把你的服务器信息也附上。如: MYSQL与PHP版本号

## 系统运行需求 ##

- [PHP][1] 版本 5.3.0 以上版本.
- [MySQL][2] 版本 4.1.0 以上版本.
- 或 [SQLite][3] 版本 2.8.0 以上版本.

  [1]: http://php.net/ 
  [2]: http://www.mysql.com/
  [3]: http://www.sqlite.org/

浏览器推荐

为了更佳的操作体验，请使用支持HTM5的浏览器打开此网站。 注意，IE浏览器下许多功能可能无法实现，或者界面出现错乱。此系统在Chrome浏览器下（版本：Version 26.0.1410.64 m）测试通过。

推荐的浏览器

- [Google浏览器][1]
- [Firefox 火狐浏览器][2]

  [1]: http://www.google.com/intl/zh-CN/chrome/browser/ 
  [2]: http://www.mozilla.org/zh-CN/firefox/beta/

## Share the Love ##

服务器推荐使用 LNMP(Linux + Nginx + MySQL + PHP) 或 LAMP(Linux + Apache + MySQL + PHP)

2013年5月7日 于 郑州