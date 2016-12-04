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
/*Возвращает количество дней/часов в формате строки*/
function get_time_str($hours){
	//Приводим тип
	$hours=(int)$hours;
	
	//Определяем переменную
	$str="";
	
	//Определяем переменную
	$str.=round(($hours-($hours%8))/8, 0).'';
	
	//IF
	if($hours%8!=0){
		$str.="д ";
		$str.=($hours%8).'ч';
	}else{
		$str.=" ";
	}
	
	//Возвращаем значение функции
	return $str;
}

/*Вычисляет количество рабочих дней (без суббот и воскресений) в указанном месяце*/
function count_work_days($Year, $Month, $To=false){      /*Если указано To, то считает не до конца месяца, а по номер дня в месяце To включая его самого.*/
	if($To){
		$total_days=$To;
	}else{
		$total_days=cal_days_in_month(CAL_GREGORIAN, $Month, $Year);
	}
	
	$work_days=0;
	
	for($dayFOR=1;$i<=$total_days;$dayFOR++){
		$day_of_week=date("N", strtotime("$Year-$Month-$dayFOR"));
		if($day_of_week==6 || $day_of_week==7){
			//nothing
		}else{
			$work_days++;
		}
	}
}

//Output error somehow
function dis_error($error_text, $output_type='return'){
	//Form error full text
	$error_full_text="DIS error: '".$error_text."'. Please, contact your system administrator.";
	
	//Perform depends on output type
	switch($output_type):
		case 'return';
			return $error_full_text;
		break;
		case 'echo';
			echo $error_full_text;
		break;
	endswitch;
}

function dis_error_handler($errno, $errstr, $errfile, $errline)
{
    if (!(error_reporting() & $errno)) {
        // Этот код ошибки не включен в error_reporting
        return;
    }

    switch ($errno) {
    case E_USER_ERROR:
        echo "<b>My ERROR</b> [$errno] $errstr<br />\n";
        echo "  Фатальная ошибка в строке $errline файла $errfile";
        echo ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
        echo "Завершение работы...<br />\n";
        exit(1);
        break;

    case E_USER_WARNING:
        echo "<b>My WARNING</b> [$errno] $errstr<br />\n";
        break;

    case E_USER_NOTICE:
        echo "<b>My NOTICE</b> [$errno] $errstr<br />\n";
        break;

    default:
        echo "Неизвестная ошибка: [$errno] $errstr<br />\n";
        break;
    }

    /* Не запускаем внутренний обработчик ошибок PHP */
    return true;
}
?>