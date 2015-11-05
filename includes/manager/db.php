<?
//Подключаемся к базе
//Implement connection to database
function db_connect($database="", $characterset='utf8')
{
	//Получаем глобальные переменные
	global $dbhost;
	global $dbname;
	global $dbuser;
	global $dbpasswd2;
	
    if(!mysql_connect($dbhost, $dbuser, $dbpasswd2)){
        trigger_error("Connection to database failed, error: ".mysql_error(), E_USER_ERROR);
        exit();
    }
    if(!mysql_select_db($dbname)){
        trigger_error("Changing database failed, error: ".mysql_error(), E_USER_ERROR);
        exit();
    }

	//Устанавливаем кодировку для работы с базой
	mysql_query("SET NAMES '$characterset'");
	mysql_query("SET CHARACTER SET '$characterset'");
	mysql_query("SET SESSION collation_connection = '$characterset_general_ci'");
	
	/*mysqli*/
	/*global $MySQLi;
	$MySQLi = new mysqli($dbhost, $dbuser, $dbpasswd2, $dbname);
	if ($MySQLi->connect_errno) {
		echo "Не удалось подключиться к MySQLi: (" . $MySQLi->connect_errno . ") " . $MySQLi->connect_error;
		exit;
	}*/
	
	//PDO
	global $Dbh;
	try {
		$Dbh = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpasswd2);
		$Dbh->query("SET NAMES '$characterset'");
		$Dbh->query("SET CHARACTER SET '$characterset'");
		$Dbh->query("SET SESSION collation_connection = '$characterset_general_ci'");

	} catch (PDOException $e) {
		print "Error!: " . $e->getMessage() . "<br/>";
		die();
	}	
}

function db_query($question)	//Wrapper for mysql_query
{
    $debug=debug_backtrace();
	//show($debug);
    if($q=mysql_query($question))
    {
        return $q;
    }else{
        show_error("Ошибка в запросе к базе данных db_query(".$question."). Запрос вызван из файла ".$debug[0]['file']." line {$debug[0]['line']}.");
    }
}

function db_fetch($query)	//Wrapper for mysql_fetch_array
{
    return mysql_fetch_array($query);
}

function db_count($query)	//Wrapper for mysql_num_rows
{
    return mysql_num_rows($query);
}

function db_result($query='notdefined')
{
    if($query=='notdefined')
    {
        return mysql_affected_rows();
    }else{
        return mysql_affected_rows($query);
    }
}

//Easy implement a query to database and return result immediately (e.g. query + fetch = both in one)
function db_easy($question, $file='', $line='')
{
    if($a=db_query($question, $file, $line))
    {
        return db_fetch($a);
    }else{
        return false;
    }
}

function db_short_easy($question, $file='', $line='')
{
    if($a=db_query($question, $file, $line))
    {
        $result=db_fetch($a);
        return $result[0];
    }else{
        return false;
    }
}

//Простой подсчет количества возвращаемых результатов поиска по базе
function db_easy_count($question){
    return mysql_num_rows(db_query($question));
}

function db_easy_result($question)
{
    if($a=db_query($question))
    {
        return db_result($a);
    }else{
        return false;
    }
}

function db_insert_id($q="not-defined"){
	if($q=="not-defined"){
		return mysql_insert_id();
	}else{
		return mysql_insert_id($q);
	}
}
function db_disconnect($conn="none"){
	if($conn=="none"){
		mysql_close();
	}else{
		mysql_close($conn);
	}	
}
/*Проверяет строку на наличие ключевых слов MySQL*/
function sql_dirty($str){
	if(preg_match("/(SELECT )|(UPDATE )|(INSERT )|(DROP )|(ALTER )|(ORDER )|(FROM )|(CHANGE )|(CALL )|(CREATE )|(INTO )/i", $str)){
		return true;
	}else{
		return false;
	}
}
?>