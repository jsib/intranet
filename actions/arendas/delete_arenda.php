<?php
function delete_arenda(){
	if(check_rights('delete_arenda')){
		$user_id=$_GET['arenda'];
		user_delete('retain', $user_id);
		header("location: /manager.php?action=list_arendas");
		return $html;
	}else{
		return "Нет соответствующих прав.<br/>";
	}
}
?>