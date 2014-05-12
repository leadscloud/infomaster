$(".view-detail").toggle(
  function () {
	$(this).children('i').removeClass("icon-double-angle-down").addClass("icon-double-angle-up");
    $(this).next('.detail').removeClass("hide");
  },
  function () {
	$(this).children('i').removeClass("icon-double-angle-up").addClass("icon-double-angle-down");
    $(this).next('.detail').addClass("hide");
  }
);

function deleteHistory(day){
	var button = $(this);
	InfoSYS.confirm('确定删除? 点击确定将清除60天前的所有数历史数据!',function(r){
		 if (r) {
			 button.attr('disabled',true);
			 $.ajax({
				dataType: 'json', url: self.location.href, data: {'method': 'delete','days': 60},
				type: 'POST',
				success: function(data){
					//console.log(data);
				},
				complete: function(){
					button.attr('disabled',false); 
					InfoSYS.Loading.remove();
				}
			});
		 }
	 });
}