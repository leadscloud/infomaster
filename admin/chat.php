<?php
// 加载公共文件
include dirname(__FILE__).'/admin.php';
// 查询管理员信息
$_USER = user_current();
// 动作
$referer = referer(PHP_FILE,false);

$name = isset($_USER['nickname'])?$_USER['nickname']:$_USER['name'];

// include ADMIN_PATH.'/admin-header.php';
?>
<!DOCTYPE html>
<html dir="ltr" lang="zh" xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>WebSocket 在线聊天室</title>
<link rel="stylesheet" href="/infomaster/common/system/websocket/static/style.css" type="text/css" media="all" />
<script type="text/javascript" src="/infomaster/common/system/websocket/static/jquery.js"  media="all"></script>
<script type="text/javascript" src="/infomaster/common/system/websocket/static/jquery.websocket.js"  media="all"></script>
<script type="text/javascript">
WS_STATIC_URL = 'http://127.0.0.1/infomaster/common/system/websocket/static';
WS_HOST = '127.0.0.1';
WS_PORT = 843;

$(function(){
    var t = $('.message');
    $.wsmessage( 'msg', function( data ){
        t.append( data );
        $('.message').animate( { scrollTop: $('.message')[0].scrollHeight } ,0 );
    });
    
    $.wsmessage( 'chat', function( data ){
        t.append( data );
        $('.message').animate( { scrollTop: $('.message')[0].scrollHeight } ,0 );
    });
    
    $.wsmessage( 'name', function( data ) {
        if ( data ) {
            $('.msg.info.name').remove();
        }
        
    });
    
    $.wsmessage( 'list', function( data ) {
        if ( !data ) {
            return false;
        }
        $.each( data, function( k, v ){
            if ( v[1] ) {
                var w = $( '<li>' + v[0] + '</li>' ).click(function(){
                    $('.send .chat').val( '@' + v[0] + ' ' );
                });
                $('.list ul').append( w );
            } else {
                $(".list ul li").each(function(){
                    if ( $(this).html() == v[0] ) {
                        $(this).remove();
                        return false;
                    }
                });
            }
        });
        $('.online').html( $('.list ul li').size() );
    });
    $.wsclose(function( data ){
        $(".list ul li").html('');
        $('.online').html( 0 );
        t.append( '<div class="msg info">连接已断开, 6秒后自动重试</div>' );
    });
    
    
    $.wsopen( function( data ) {
        t.append( '<div class="msg info">连接服务器成功</div>' );
        $.wssend('name=<?php echo $name ?>');
        
        // w.find('.submit').click(function(){
        //     $.wssend('name=' + w.find('input.name').val() );
        //     return false;
        // });
    });
    
    
    
    
    $('.send .submit').click(function(){
        if ( $('.send .chat').val() ) {
            
            $.wssend($.param( { chat : $('.send .chat').val() } ) );
            $('.send .chat').val('');
        }
        return false;
    });
    $('.send  .chat').keydown(function (e) {
        if ( ( e.ctrlKey && e.keyCode == 13 ) || ( e.altKey && e.keyCode == 83 ) ) {
            $('.send .submit').click();
            return false;
        }
    })
    
    $('.tool .empty').click(function(){
        t.html('');
    })
});
</script>
</head>
<body>
<div class="content">
    <div class="message"></div>
    <div class="tool">
        <span class="empty">清空记录</span>
    </div>
    <div class="send">
        <textarea class="chat" name="chat"></textarea>
        <p><input type="submit" class="submit" name="submit" value="发送" /></p>
    </div>
    <div class="list">
        <h3>在线用户<strong class="online">0</strong></h3>
        <ul>
        </ul>
    </div>
</div>
</body>
</html>
<?php
// include ADMIN_PATH.'/admin-footer.php';


