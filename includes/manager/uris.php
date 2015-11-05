<?
//поиск в $uri аргумента с именем $argument_name
function uri_find_argument($argument_name, $uri){
    if(preg_match("/\?$argument_name\=/", $uri) || preg_match("/\&$argument_name\=/", $uri)){
        return true;
    }else{
        return false;
    }
}

//заменяет в uri значение аргумент $argument_name на $argument_value
//если в uri не был определен аргумент $argument_name, то он будет добавлен в получаемую строку
function uri_change($argument_name, $argument_value, $uri){
    //uri содержит "?"
    if(preg_match("/\?/", $uri)){
        //uri содержит искомый аргумент
        if(uri_find_argument($argument_name, $uri)){
			//замена значения аргумента (при наличии значения)
			if($argument_value!=""){
				$uri=preg_replace("/\?$argument_name\=[^\&\?]+/", "?$argument_name=$argument_value", $uri);
				$uri=preg_replace("/\&$argument_name\=[^\&\?]+/", "&$argument_name=$argument_value", $uri);
			//удаление аргумента (при пустом значении, при этом аргумент вообще есть)
			}else{
				$uri=preg_replace("/\?$argument_name\=[^\&\?]+/", "?", $uri);
				preg_replace("/\?\&/", "?", $uri); //на случай, если аргумент стоял после ? и за ним были еще аргументы (которые всегда предваряются &)
				$uri=preg_replace("/\&$argument_name\=[^\&\?]+/", "", $uri);
			}
        //uri не содержит искомого аргумента
        }elseif($argument_value!=""){
            //show("uri 3: ".$argument_name."::". $argument_value. "::".$uri);
            $uri="{$uri}&{$argument_name}={$argument_value}";
        }
    //uri не содержит "?", значит просто добавляем аргумент со значением
    }else{
        $uri="{$uri}?{$argument_name}={$argument_value}";
    }
    return $uri;
}

//Функция uri_make будет учитывать эти изменения
function uri_prepare($argument, $value){
	$GLOBALS['prepared_uri'][$argument]=$value;
}

//Чистит uri
function uri_clean(){
	$arguments=func_get_args();
	
	//Специальное поведение для отдельных окон
	if(strripos($_SERVER['PHP_SELF'], 'statistics.php')){
		foreach($arguments as $id=>$name){
			if($name=='show_positions') unset($arguments[$id]);
		}
	}
	
	foreach($arguments as $id=>$argument){
		//if(!(isset($arguments['show_positions']) && $argument=="show_histories")){
			uri_prepare($argument, '');
		//}
	}
}

//Очень хорошо готовит uri. Использует $_SERVER['REQUEST_URI'] для этого.
function uri_make($argument_name=false, $argument_value="", $anchor=""){
	if(!$argument_name){
		return $_SERVER['REQUEST_URI'];
	}else{
		//Если входные параметры не в виде  массива
		if(!is_array($argument_name)){
			$arguments[$argument_name]=$argument_value;
		}else{
			$arguments=$argument_name;
			$anchor=$argument_value;
		}
		
		//Получаем, введенный в браузере uri
		$uri=$_SERVER['REQUEST_URI'];
		
		//Заменяем имя скрипта, если необходимо
		if(isset($arguments['UriScript'])){
			$UriScript=$arguments['UriScript'];
			unset($arguments['UriScript']);
			if(preg_match("/\?/", $uri)){
				$UriScript_browser=substr(explode("?", $uri)[0], 1);
			}else{
				$UriScript_browser=substr($uri, 1);
			}
			$uri=str_replace("/".$UriScript_browser, "/".$UriScript, $uri);
		}
		
		//Заменяем значения уже имеющихся аргументов на новые
		foreach($arguments as $name=>$value){
			$uri=uri_change($name, $value, $uri);
		}

		//Или добавляем аргументы, если их не было
		foreach($GLOBALS['prepared_uri'] as $name=>$value){
			if(!isset($arguments[$name])){
				$uri=uri_change($name, $value, $uri);
			}
		}

		//Убираем аргументы, если необходимо
		if(isset($arguments['UriClean'])){
			$UriClean=$arguments['UriClean'];
			unset($arguments['UriClean']);
			switch($UriClean){
				case "DeleteAllArguments":
					if(preg_match("/\?/", $uri)){
						$uri=explode("?", $uri)[0];
					}
					//$uri=str_replace("/".$UriScript_browser, "/".$UriScript, $uri);
				break;
			}
		}
		
		//Добавляем якорь
		if($anchor!="") $uri.="#$anchor";
		
		//Возвращаем результат
		return $uri;
	}
}

function uri_make_v1($argument_name=false, $argument_value="", $anchor=""){
	if(!$argument_name){
		return $_SERVER['REQUEST_URI'];
	}else{
		//Если входные параметры не в виде  массива
		if(!is_array($argument_name)){
			$arguments[$argument_name]=$argument_value;
		}else{
			$arguments=$argument_name;
			$anchor=$argument_value;
		}
		
		//Получаем, введенный в браузере uri
		$uri=$_SERVER['REQUEST_URI'];
		
		//Заменяем имя скрипта, если необходимо
		if(isset($arguments['UriScript'])){
			$UriScript=$arguments['UriScript'];
			unset($arguments['UriScript']);
			if(preg_match("/\?/", $uri)){
				$UriScript_browser=substr(explode("?", $uri)[0], 1);
			}else{
				$UriScript_browser=substr($uri, 1);
			}
			$uri=str_replace("/".$UriScript_browser, "/".$UriScript, $uri);
		}
		
		//Убираем все аргументы, если не определено противоположное поведение
		if(@$arguments['SaveArguments']!="yes"){
			if(preg_match("/\?/", $uri)){
				$uri=explode("?", $uri)[0];
			}
		}
		
		//Заменяем значения уже имеющихся аргументов на новые
		foreach($arguments as $name=>$value){
			$uri=uri_change($name, $value, $uri);
		}
		
		//Убираем аргументы, если необходимо
		if(isset($arguments['UriClean'])){
			$UriClean=$arguments['UriClean'];
			unset($arguments['UriClean']);
			switch($UriClean){
				case "DeleteAllArguments":
					if(preg_match("/\?/", $uri)){
						$uri=explode("?", $uri)[0];
					}
					//$uri=str_replace("/".$UriScript_browser, "/".$UriScript, $uri);
				break;
			}
		}
		
		//Добавляем якорь
		if($anchor!="") $uri.="#$anchor";
		
		//Возвращаем результат
		return $uri;
	}
}

function make_hiddens_from_uri($uri){
	$html="";
	parse_str(parse_url($uri)['query'], $arguments);
	foreach($arguments as $name=>$value){
		$html.="<input type='hidden' name='$name' value='$value'/>";
	}
	return $html;
}

function get_class_depend_on_uri($rule, $argument, $value=''){
    switch($rule){
        case "=":
            if(trim(@$_GET[$argument])==$value){
                return "not-lighted";
            }else{
                return "";
            }
        break;
        case "!=":
            if(trim(@$_GET[$argument])!=$value){
                return "not-lighted";
            }else{
                return "";
            }
        break;
		case 'notexist':
            if(!isset($_GET[$argument])){
                return "not-lighted";
            }else{
                return "";
            }
		break;
    }
}
?>