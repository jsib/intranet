<?php
function show_admin_panel(){
	//���������� ����������
	$html="";

	/*���������� ���� �������*/
	$html.=template_get("admin/show_admin_panel",
								array(
										
								));

	
	//���������� �������� �������
	return $html;
}
?>