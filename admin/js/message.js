function pm_list_init() {
	
}
function selectAll(active){
	$('input[name^=list]:checkbox,input[name=select]:checkbox').attr('checked',active);
	return false;
}
function pm_manage_init() {
	$('form#pmmanage').ajaxSubmit();
}
function inverseSelect(){
	$('input[name^=list]:checkbox,input[name=select]:checkbox').each(function(){
		this.checked = !this.checked; 
    });
	return false;
}
function markMessage(status) {
	var listids = [] , url = self.location.href,
	 form   = $('.table-meesage');
	 $('input:checkbox[name^=listids]:checked',form).each(function(){
		 listids.push(this.value);
	 });
	 $.ajax({
		dataType: 'json', url: url, 
		data: { 'method':'mark','listids':listids, 'status':status },
		type: 'POST',
		
		success: function(data, status, xhr){
			InfoSYS.ajaxSuccess(data, status, xhr);
		},
		complete: function(){
			InfoSYS.Loading.remove();
		}
	});
	return false;
}
function deleteMessage(){
	InfoSYS.confirm('确定删除?',function(r){
	   if (r) {
		   var listids = [] , url = self.location.href,
		   form   = $('.table-meesage');
		   $('input:checkbox[name^=listids]:checked',form).each(function(){
			   listids.push(this.value);
           });
		   $.ajax({
			  dataType: 'json', url: url, 
			  data: { 'method':'delete','listids':listids },
			  type: 'POST',
			  success: function(data, status, xhr){
				  InfoSYS.ajaxSuccess(data, status, xhr);
			  },
			  complete: function(){
				  InfoSYS.Loading.remove();
			  }
		  });
	   }
   });
}