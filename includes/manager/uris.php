<?
//����� � $uri ��������� � ������ $argument_name
function uri_find_argument($argument_name, $uri){
    if(preg_match("/\?$argument_name\=/", $uri) || preg_match("/\&$argument_name\=/", $uri)){
        return true;
    }else{
        return false;
    }
}

//�������� � uri �������� �������� $argument_name �� $argument_value
//���� � uri �� ��� ��������� �������� $argument_name, �� �� ����� �������� � ���������� ������
function uri_change($argument_name, $argument_value, $uri){
    //uri �������� "?"
    if(preg_match("/\?/", $uri)){
        //uri �������� ������� ��������
        if(uri_find_argument($argument_name, $uri)){
			//������ �������� ��������� (��� ������� ��������)
			if($argument_value!=""){
				$uri=preg_replace("/\?$argument_name\=[^\&\?]+/", "?$argument_name=$argument_value", $uri);
				$uri=preg_replace("/\&$argument_name\=[^\&\?]+/", "&$argument_name=$argument_value", $uri);
			//�������� ��������� (��� ������ ��������, ��� ���� �������� ������ ����)
			}else{
				$uri=preg_replace("/\?$argument_name\=[^\&\?]+/", "?", $uri);
				preg_replace("/\?\&/", "?", $uri); //�� ������, ���� �������� ����� ����� ? � �� ��� ���� ��� ��������� (������� ������ ������������ &)
				$uri=preg_replace("/\&$argument_name\=[^\&\?]+/", "", $uri);
			}
        //uri �� �������� �������� ���������
        }elseif($argument_value!=""){
            //show("uri 3: ".$argument_name."::". $argument_value. "::".$uri);
            $uri="{$uri}&{$argument_name}={$argument_value}";
        }
    //uri �� �������� "?", ������ ������ ��������� �������� �� ���������
    }else{
        $uri="{$uri}?{$argument_name}={$argument_value}";
    }
    return $uri;
}

//������� uri_make ����� ��������� ��� ���������
function uri_prepare($argument, $value){
	$GLOBALS['prepared_uri'][$argument]=$value;
}

//������ uri
function uri_clean(){
	$arguments=func_get_args();
	
	//����������� ��������� ��� ��������� ����
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

//����� ������ ������� uri. ���������� $_SERVER['REQUEST_URI'] ��� �����.
function uri_make($argument_name=false, $argument_value="", $anchor=""){
	if(!$argument_name){
		return $_SERVER['REQUEST_URI'];
	}else{
		//���� ������� ��������� �� � ����  �������
		if(!is_array($argument_name)){
			$arguments[$argument_name]=$argument_value;
		}else{
			$arguments=$argument_name;
			$anchor=$argument_value;
		}
		
		//��������, ��������� � �������� uri
		$uri=$_SERVER['REQUEST_URI'];
		
		//�������� ��� �������, ���� ����������
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
		
		//�������� �������� ��� ��������� ���������� �� �����
		foreach($arguments as $name=>$value){
			$uri=uri_change($name, $value, $uri);
		}

		//��� ��������� ���������, ���� �� �� ����
		foreach($GLOBALS['prepared_uri'] as $name=>$value){
			if(!isset($arguments[$name])){
				$uri=uri_change($name, $value, $uri);
			}
		}

		//������� ���������, ���� ����������
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
		
		//��������� �����
		if($anchor!="") $uri.="#$anchor";
		
		//���������� ���������
		return $uri;
	}
}

function uri_make_v1($argument_name=false, $argument_value="", $anchor=""){
	if(!$argument_name){
		return $_SERVER['REQUEST_URI'];
	}else{
		//���� ������� ��������� �� � ����  �������
		if(!is_array($argument_name)){
			$arguments[$argument_name]=$argument_value;
		}else{
			$arguments=$argument_name;
			$anchor=$argument_value;
		}
		
		//��������, ��������� � �������� uri
		$uri=$_SERVER['REQUEST_URI'];
		
		//�������� ��� �������, ���� ����������
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
		
		//������� ��� ���������, ���� �� ���������� ��������������� ���������
		if(@$arguments['SaveArguments']!="yes"){
			if(preg_match("/\?/", $uri)){
				$uri=explode("?", $uri)[0];
			}
		}
		
		//�������� �������� ��� ��������� ���������� �� �����
		foreach($arguments as $name=>$value){
			$uri=uri_change($name, $value, $uri);
		}
		
		//������� ���������, ���� ����������
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
		
		//��������� �����
		if($anchor!="") $uri.="#$anchor";
		
		//���������� ���������
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