<?php
function delete_point(){
	if(!check_rights('delete_point')){
		//���������� �������� �������
		return "� ��� ��� ��������������� ����";
	}

	/*�������� ������ �� ������������*/
	$point_id=$_GET['point'];
	
	//������ � ����
	$point=db_easy("SELECT * FROM `phpbb_points` WHERE `id`=$point_id");
	
	//������ � ����
	db_query("DELETE FROM `phpbb_points` WHERE `id`=$point_id");
	
	//���������� HTTP ���������
	header("location: /manager.php?action=list_points&message=pointdeleted&name={$point['name']}");
	
	//���������� �������� �������
	return $html;
}
?>