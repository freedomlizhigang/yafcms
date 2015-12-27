$(function(){
	$(".confirm").click(function(){
		if (!confirm("确实要删除吗?")){
			return false;
		}else{
			return true;
		}
	});
	$(".checkall").click(function(){
		$(".subcheck").prop("checked", this.checked);
	});
});