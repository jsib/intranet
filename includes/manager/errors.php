<?php
function print_error($error){
	//Добавляем отладочную информацию к концу сообщения
	$error .= PHP_EOL . @print_r( debug_backtrace(), true );
	
	//Формируем HTML сообщения
	echo '<div style="margin: 20px 0; border:1px solid #000; padding:10px; background:#AAA; font-family:Tahoma; z-index:1000;">';
	echo '<h1 style="font-family:Tahoma; font-size:12pt; font-weight:normal; color:red;">Ошибка</h1>';
	echo '<pre>' . $error . '</pre>';
	echo '</div>';
}

//Обработчик ошибок для замены встроенного в PHP обработчика
function error_handler($errno, $errstr, $errfile, $errline){
	$error = 'Источник ошибки: Error Handler' . PHP_EOL;
	
	if ( !(error_reporting() & $errno) ) {
		if( ERR_SHOW_NO_REPORTED == false ){
			return false;
		}else{
			$error .= 'Данный код ошибки на задан в error_reporting, поэтому также обрабатывается стандартным обработчиком ошибок PHP.' . PHP_EOL;
		}
	}
		
	//Формируем информацию об ошибке
	$error .= 'Код ошибки:' . $errno . PHP_EOL;
	$error .= 'Текст ошибки:' . PHP_EOL . $errstr . PHP_EOL;
	$error .= 'Отладочная информация:' . PHP_EOL;
	
	//Выводим информацию об ошибке
	print_error($error);
	
    switch ($errno) {
		case E_USER_ERROR:
			exit;
		break;
	}
	
	
    //Не запускаем внутренний обработчик ошибок PHP
    return true;
}
?>