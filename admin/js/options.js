//options javascript
$('#updateViewInfo').ajaxSubmit();
$('#updateMapInfo').ajaxSubmit();




/*$(".et_markers").click(function() {
	var _this = $(this), _marker = $(this).closest('label').find('input');
	console.log(_this,_marker );
	var pos = $(this).position();
	var actualHeight = $(this)[0].offsetHeight;
	var left =  pos.left + actualHeight;
	$('#markers-container').css({
    	"top": pos.top,
    	"left": left
    });
	//console.log(pos,pos.left,actualHeight,left,$(this));
        $('#markers-container').toggle('fast', function() {
            $(document).click(function(event) {
				//console.log(event.target);
				//console.log($(event.target).is('.et_markers'));
                if (!($(event.target).is('.et_markers') || $(event.target).is('#markers-container'))) {
					if($(event.target).is('.icon_select')) console.log(event.target.src);
					console.log('test',_marker);
					_marker[0].value = event.target.src;
                    $('#markers-container').hide(200);
                }
            });
        });
    });*/