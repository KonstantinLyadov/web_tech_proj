<?php	  
	$uploaddir = getcwd();
    $uploaddir .= $_GLOBALS['UPLOAD_DIR'];	
    // ����� ��������� ����������� 
    // ����� � ��� ����������
	$destination = $uploaddir . $_FILES['myfile']['name'];
    // ��� ����� ������� ����������

	print "<pre>";
	if (move_uploaded_file( $_FILES['myfile']['tmp_name'], $destination)) 
	{ 
		/* ���������� ���� �� ��������� ����� � ��������� ���������� ��� �������� */
		print "���� ������� �������� <br>";
	} 
	else 
	{
		echo "��������� ������ ��� �������� �����. ��������� ���������� ����������:<br>";
		print_r($_FILES);
	}
	print "</pre>"; 
?>