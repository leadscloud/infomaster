WS_STATIC_URL = InfoSYS.URI.Host+baseurl+'websocket/static';
WS_HOST = '96.126.121.54';
WS_PORT = 843;

$(function(){
    var t = $('.dialogs');
    $.wsmessage( 'msg', function( data ){
        t.append( data );
        $('.dialogs').animate( { scrollTop: $('.dialogs')[0].scrollHeight } ,0 );

    });
    
    $.wsmessage( 'chat', function( data ){
        t.append( data );
        $('.dialogs').animate( { scrollTop: $('.dialogs')[0].scrollHeight } ,0 );

        if(!document.hasFocus()){
            notify(data);
        }
        
    });
    
    $.wsmessage( 'name', function( data ) {
        if ( data ) {
            $('.msg.info.name').remove();
        }
        
    });
    //显示在线用户
    $.wsmessage( 'list', function( data ) {
        if ( !data ) {
            return false;
        }
        $.each( data, function( k, v ){
            if ( v[1] ) {
                var w = $( '<li class="online"><a href="javascript:;">' + v[0] + '</a></li>' ).click(function(){
                    $('.send .chat').val( '@' + v[0] + ' ' );
                });
                $('.list ul').append( w );
            } else {
                $(".list ul li").each(function(){
                    if ( $(this).text() == v[0] ) {
                        $(this).remove();
                        return false;
                    }
                });
            }
        });
        $('.online-count').html( $('.list ul li.online a').size() );
    });
    $.wsclose(function( data ){
        $(".list ul li").html('');
        $('.online-count').html( 0 );
        t.append( '<div class="msg info">连接已断开, 6秒后自动重试</div>' );

    });
    
    
    $.wsopen( function( data ) {
        t.append( '<div class="msg info">连接服务器成功</div>' );
        $.wssend('name='+username);
        
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
    $('.send .chat').keydown(function (e) {
        if ( ( e.ctrlKey && e.keyCode == 13 ) || ( e.altKey && e.keyCode == 83 ) ) {
            $('.send .submit').click();
            return false;
        }
    })
    //清空聊天记录
    $('.empty-messages').click(function(){
        t.html('');
        return false;
    });

    //$(window).bind( 'blur', this.windowBlur).bind( 'focus', this.windowFocus); 

});

function notify(content) {
    if (window.webkitNotifications) {
        if (window.webkitNotifications.checkPermission() == 0) {
            var notification_test = window.webkitNotifications.createNotification("http://images.cnblogs.com/cnblogs_com/flyingzl/268702/r_1.jpg", '新消息',content);
            notification_test.display = function() {}
            notification_test.onerror = function() {}
            notification_test.onclose = function() {}
            
            notification_test.replaceId = 'Meteoric';

            notification_test.show();

            notification_test.onclick=function(){  
                window.focus();  
                this.cancel();      
            }
                        
            //var tempPopup = window.webkitNotifications.createHTMLNotification(["http://www.baidu.com/", "http://www.soso.com"][Math.random() >= 0.5 ? 0 : 1]);
            //tempPopup.replaceId = "Meteoric_cry";
            //tempPopup.show();
        } else {
            window.webkitNotifications.requestPermission(notify);
        }
    } 
}