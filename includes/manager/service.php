<?
//Выводит содержание в зависимости от формата
function show($text)	
{
	$sapi=php_sapi_name();
	if(gettype($text)=='boolean')
	{
		if($text){$text='true';}else{$text='false';}
		if($sapi=='cli')
		{
			echo $text."\n";
		}else{
			echo $text."<br/>";
		}
	}elseif(gettype($text)=='array')
		if($sapi!='cli')
		{
			echo "<pre>";
			print_r($text);
			echo "</pre>";
		}else{
			print_r($text);
	}else{
		if($sapi=='cli')
		{
			echo $text."\n";
		}else{
			echo $text.'<br/>';
		}
	}
}

function show_error($text){
	show("<span style='color:red;font-size:14pt;'>".$text."</span>");
}

function h1($text)
{
	return "<h1 style='font-size:24pt;'>$text</h1>";
}

function h2($text)
{
    return "<h2 style='font-size:18pt;'>$text</h2>";
}

function get_file_name($fileName){ 
    return substr($fileName, 0, strrpos($fileName, '.'));
} 

function get_file_extension($fileName) {
    return substr($fileName, strrpos($fileName, '.') + 1);
} 

//Цепляет блок
function block($file){
	require_once($_SERVER['DOCUMENT_ROOT']."/blocks/".$file.".php");

}

//Цепляет файл
function pickup_all($dir){
	$dp = opendir($dir);
	while($f = readdir($dp)){
		if($f != '.' && $f != '..'){
			if(is_dir($dir."/".$f)){
				pickup_all($dir."/".$f);
			}else{
				require_once($dir."/".$f);	
				$file=substr($f, 0, strlen($f)-4);
				$file();
			}
		}
	}
}
?>