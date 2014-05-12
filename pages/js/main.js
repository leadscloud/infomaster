/* Default class modification */
$.extend( $.fn.dataTableExt.oStdClasses, {
	"sSortAsc": "header headerSortDown",
	"sSortDesc": "header headerSortUp",
	"sSortable": "header"
} );

/* API method to get paging information */
$.fn.dataTableExt.oApi.fnPagingInfo = function ( oSettings )
{
	return {
		"iStart":         oSettings._iDisplayStart,
		"iEnd":           oSettings.fnDisplayEnd(),
		"iLength":        oSettings._iDisplayLength,
		"iTotal":         oSettings.fnRecordsTotal(),
		"iFilteredTotal": oSettings.fnRecordsDisplay(),
		"iPage":          Math.ceil( oSettings._iDisplayStart / oSettings._iDisplayLength ),
		"iTotalPages":    Math.ceil( oSettings.fnRecordsDisplay() / oSettings._iDisplayLength )
	};
}

/* Bootstrap style pagination control */
$.extend( $.fn.dataTableExt.oPagination, {
	"bootstrap": {
		"fnInit": function( oSettings, nPaging, fnDraw ) {
			var oLang = oSettings.oLanguage.oPaginate;
			var fnClickHandler = function ( e ) {
				e.preventDefault();
				if ( oSettings.oApi._fnPageChange(oSettings, e.data.action) ) {
					fnDraw( oSettings );
				}
			};

			$(nPaging).addClass('pagination').append(
				'<ul>'+
					'<li class="prev disabled"><a href="#">&larr; '+oLang.sPrevious+'</a></li>'+
					'<li class="next disabled"><a href="#">'+oLang.sNext+' &rarr; </a></li>'+
				'</ul>'
			);
			var els = $('a', nPaging);
			$(els[0]).bind( 'click.DT', { action: "previous" }, fnClickHandler );
			$(els[1]).bind( 'click.DT', { action: "next" }, fnClickHandler );
		},

		"fnUpdate": function ( oSettings, fnDraw ) {
			var iListLength = 5;
			var oPaging = oSettings.oInstance.fnPagingInfo();
			var an = oSettings.aanFeatures.p;
			var i, j, sClass, iStart, iEnd, iHalf=Math.floor(iListLength/2);

			if ( oPaging.iTotalPages < iListLength) {
				iStart = 1;
				iEnd = oPaging.iTotalPages;
			}
			else if ( oPaging.iPage <= iHalf ) {
				iStart = 1;
				iEnd = iListLength;
			} else if ( oPaging.iPage >= (oPaging.iTotalPages-iHalf) ) {
				iStart = oPaging.iTotalPages - iListLength + 1;
				iEnd = oPaging.iTotalPages;
			} else {
				iStart = oPaging.iPage - iHalf + 1;
				iEnd = iStart + iListLength - 1;
			}

			for ( i=0, iLen=an.length ; i<iLen ; i++ ) {
				// Remove the middle elements
				$('li:gt(0)', an[i]).filter(':not(:last)').remove();

				// Add the new list items and their event handlers
				for ( j=iStart ; j<=iEnd ; j++ ) {
					sClass = (j==oPaging.iPage+1) ? 'class="active"' : '';
					$('<li '+sClass+'><a href="#">'+j+'</a></li>')
						.insertBefore( $('li:last', an[i])[0] )
						.bind('click', function (e) {
							e.preventDefault();
							oSettings._iDisplayStart = (parseInt($('a', this).text(),10)-1) * oPaging.iLength;
							fnDraw( oSettings );
						} );
				}

				// Add / remove disabled classes from the static elements
				if ( oPaging.iPage === 0 ) {
					$('li:first', an[i]).addClass('disabled');
				} else {
					$('li:first', an[i]).removeClass('disabled');
				}

				if ( oPaging.iPage === oPaging.iTotalPages-1 || oPaging.iTotalPages === 0 ) {
					$('li:last', an[i]).addClass('disabled');
				} else {
					$('li:last', an[i]).removeClass('disabled');
				}
			}
		}
	}
} );

/* Table initialisation */
$(document).ready(function() {
	$('#data-domain').dataTable( {
		"bProcessing": true,
		"sAjaxSource": "index.php?method=data-table",
		"sDom": "<'row'<'span5'l><'span5 pull-right'f>r>t<'row'<'span5'i><'span5'p>>",
		"sPaginationType": "bootstrap",
		"oLanguage": {
			"sLengthMenu": "_MENU_ 记录/每页",
			"sInfo": "显示 _START_ 到 _END_ 项, 总计 _TOTAL_ 项",
			"sInfoEmpty": "无记录可显示",
			"sLoadingRecords": "请等待 - 正努力为你加载中...",
			"sProcessing": "处理中...",
			"sSearch": "搜索:",
			"oPaginate": {
        		"sPrevious": "上一页",
				"sNext": "下一页"
      		},
			"sZeroRecords": "没有匹配项可显示!",
			"sInfoFiltered": " - 从 _MAX_ 条记录中筛选"
		},
		"fnRowCallback": function( nRow, aData, iDisplayIndex ) {
			$(nRow).attr("id",'item-'+$('td:eq(0)', nRow).html());
			//$('td:eq(0)', nRow).attr("title",aData[0]);
			$('td:eq(1)', nRow).html('<i class="icon-user"></i> '+aData[1]).attr("title",aData[1]);
			var count = parseInt($('td:eq(2)', nRow).text());
			var classname;
			if(!isNaN(count)){
				if(count>30)
					classname ="badge-important";
				else if(count>10)
					classname ="badge-info";
				else
					classname = "badge-success";
					
			}
			$('td:eq(2)', nRow).html('<i class="icon-tags"></i> <span class="badge '+classname+'">'+aData[2]+'</span>');
			//$('td:eq(2)', nRow).wrapInner('<span class="badge '+classname+'" />').prepend('<i class="icon-tags"></i> ');
			$('td:eq(3)', nRow).html('<i class="icon-link"></i> '+aData[3]).attr("title",aData[3]);
			return nRow;
        }
	});
	//user table data
	$('#user-data-domain').dataTable( {
		"bProcessing": true,
		"sAjaxSource": "index.php?method=search&action=user-data-table&name="+$('#name').val(),
		"sDom": "<'row'<'span5'l><'span5 pull-right'f>r>t<'row'<'span5'i><'span5'p>>",
		"sPaginationType": "bootstrap",
		"oLanguage": {
			"sLengthMenu": "_MENU_ 记录/每页",
			"sInfo": "显示 _START_ 到 _END_ 项, 总计 _TOTAL_ 项",
			"sInfoEmpty": "无记录可显示",
			"sLoadingRecords": "请等待 - 正努力为你加载中...",
			"sProcessing": "处理中...",
			"sSearch": "搜索:",
			"oPaginate": {
        		"sPrevious": "上一页",
				"sNext": "下一页"
      		},
			"sZeroRecords": "没有匹配项可显示!",
			"sInfoFiltered": " - 从 _MAX_ 条记录中筛选"
		},
		"fnRowCallback": function( nRow, aData, iDisplayIndex ) {
			$(nRow).attr("id",'item-'+$('td:eq(0)', nRow).html());
			//$('td:eq(0)', nRow).attr("title",aData[0]);
			$('td:eq(1)', nRow).html('<i class="icon-user"></i> '+aData[1]).attr("title",aData[1]);
			$('td:eq(2)', nRow).html('<i class="icon-link"></i> '+aData[2]).attr("title",aData[2]);
			//$('td:eq(3)', nRow).attr("title",aData[3]);
			return nRow;
        }
	});
	$('#all-domain').dataTable( {
		"bProcessing": true,
		"sAjaxSource": "all-domain.php?method=data-table",
		"sDom": "<'row'<'span5'l><'span5 pull-right'f>r>t<'row'<'span5'i><'span5'p>>",
		"sPaginationType": "bootstrap",
		"oLanguage": {
			"sLengthMenu": "_MENU_ 记录/每页",
			"sInfo": "显示 _START_ 到 _END_ 项, 总计 _TOTAL_ 项",
			"sInfoEmpty": "无记录可显示",
			"sLoadingRecords": "请等待 - 正努力为你加载中...",
			"sProcessing": "处理中...",
			"sSearch": "搜索:",
			"oPaginate": {
        		"sPrevious": "上一页",
				"sNext": "下一页"
      		},
			"sZeroRecords": "没有匹配项可显示!",
			"sInfoFiltered": " - 从 _MAX_ 条记录中筛选"
		},
		"fnRowCallback": function( nRow, aData, iDisplayIndex ) {
			if(aData[3]==null || aData[4]==null){
				$.ajax({
					url: "all-domain.php?method=whois",
					type: "POST",
					data: { 'domain': aData[0]},
					dataType: "json",
					beforeSend: function() {
						$('td:eq(3)', nRow).html('<i class="icon-spinner icon-spin"></i> 获取中...');
						$('td:eq(4)', nRow).html('<i class="icon-spinner icon-spin"></i> 获取中...');
					},
					success: function(data) {
						//console.log(data);
						$('td:eq(3)', nRow).html(data[0]);
						$('td:eq(4)', nRow).html(data[1]);
						aData[3] = data[0];
						aData[4] = data[1];
						
					},
					error : function(data){
						$('td:eq(3)', nRow).html('error: '+data);
					},
					complete: function(){
					}
				});
			}
			
			$(nRow).attr("id",'item-'+$('td:eq(0)', nRow).html());
			//$('td:eq(0)', nRow).attr("title",aData[0]);
			$('td:eq(1)', nRow).html('<i class="icon-user"></i> '+aData[1]).attr("title",aData[1]);
			var count = parseInt($('td:eq(2)', nRow).text());

			//$('td:eq(2)', nRow).wrapInner('<span class="badge '+classname+'" />').prepend('<i class="icon-tags"></i> ');
			$('td:eq(0)', nRow).html('<i class="icon-link"></i> '+aData[0]).attr("title",aData[0]);
			return nRow;
        }
	});
	//$('#all-domain').dataTable().fnUpdate( ["-","1","2","-","4"], $('#all-domain tbody tr:eq(0)')[0] );
	// 根据域名检测系统中的所属人
	$("#check-url").click(function(){
		return false;
	});
	$("#check-url").keyup(function(){
		var e = $(this);
		var url = e.val();
		var button = $("#btn-search");
		if (!url.match("^http")) {
			url = 'http://'+url;
		}
		if(url.length>12){
			$.ajax({
				url: "index.php?method=checkurl",
				type: "POST",
				data: { 'url': url, 'type': '网站所属人'},
				dataType: "json",
				beforeSend: function() {
					button.html('<i class="icon-check-empty"></i>检测中');
				},
				success: function(data) {
					//console.log(data);
					if(data==null)
						button.html('没有找到').removeClass('g-btn-success').removeClass('g-btn-danger');
					else if(data==false)
						button.html('系统错误').addClass('g-btn-danger').removeClass('g-btn-success');
					else if(data!=null) 
						button.html(data).addClass('g-btn-success').removeClass('g-btn-danger');
				},
				complete: function(){
				}
			});
		}
	});
	
	$.fn.dataTableExt.ofnSearch['data-domain'] = function ( sData ) {
   		return sData.replace(/\n/g," ").replace( /<.*?>/g, "" );
	}
	
	$('.table-data td').on('hover',function() {
		var e=$(this);
		console.log(e);
		var title = e.text();
		console.log(title);
	});
	//

	$('*[data-poload]').bind('hover',function() {
		console.log('test');
		var e=$(this);
		e.unbind('hover');
		$.get(e.data('poload'),function(d) {
			e.popover({content: d}).popover('show');
		});
	});
});