<?php
function delete_branch(){
	if(!check_rights('delete_branch')){
		//���������� �������� �������
		return "� ��� ��� ��������������� ����";
	}
	
	$branch_id=$_GET['branch'];
	db_query("DELETE FROM `phpbb_branches` WHERE `id`=$branch_id");
	header("location: /manager.php?action=list_branches");
	return $html;
}
?>