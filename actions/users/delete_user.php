<?php
function delete_contact(){
	if(check_rights('delete_contact')){
		$user_id=$_GET['contact'];
		user_delete('retain', $user_id);
		header("location: /manager.php?action=list_contacts");
		return $html;
	}else{
		return "Нет соответствующих прав.<br/>";
	}
}
?>