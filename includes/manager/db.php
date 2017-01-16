<?
//Connect to database
function db_connect($database="", $characterset='utf8')
{
	//Bind global variables
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

	//Set encoding to work with database
	mysql_query("SET NAMES '$characterset'");
	mysql_query("SET CHARACTER SET '$characterset'");
	mysql_query("SET SESSION collation_connection = '".$characterset."_general_ci'");
	
	//Connection using PDO
	global $Dbh;
	try {
		$Dbh = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpasswd2);
		$Dbh->query("SET NAMES '$characterset'");
		$Dbh->query("SET CHARACTER SET '$characterset'");
		$Dbh->query("SET SESSION collation_connection = '".$characterset."_general_ci'");

	} catch (PDOException $e) {
		print "Error!: " . $e->getMessage() . "<br/>";
		die();
	}
}

//Wrapper for mysql_query
function db_query($question)	
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

//Wrapper for mysql_fetch_array
function db_fetch($query)	
{
    return mysql_fetch_array($query);
}

//Wrapper for mysql_num_rows
function db_count($query)	
{
    return mysql_num_rows($query);
}

//Wrapper for mysql_affected_rows
function db_result($query='notdefined')
{
    if($query=='notdefined')
    {
        return mysql_affected_rows();
    }else{
        return mysql_affected_rows($query);
    }
}

//Request to database and return result immediately (e.g. query + fetch = both in one)
function db_easy($question, $file='', $line='')
{
    if($a=db_query($question, $file, $line))
    {
        return db_fetch($a);
    }else{
        return false;
    }
}

//Same as db_easy, but also return 1st column value
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

//Wrapper for mysql_num_rows
function db_easy_count($question){
    return mysql_num_rows(db_query($question));
}

//Fast request to database with INSERT, UPDATE operators
function db_easy_result($question)
{
    if($a=db_query($question))
    {
        return db_result($a);
    }else{
        return false;
    }
}

//Get last inserted row id
function db_insert_id($q="not-defined"){
	if($q=="not-defined"){
		return mysql_insert_id();
	}else{
		return mysql_insert_id($q);
	}
}

//Wrapper for mysql_close
function db_disconnect($conn="none"){
	if($conn=="none"){
		mysql_close();
	}else{
		mysql_close($conn);
	}	
}
//Check string for mysql key words existence
function sql_dirty($str){
	if(preg_match("/(SELECT )|(UPDATE )|(INSERT )|(DROP )|(ALTER )|(ORDER )|(FROM )|(CHANGE )|(CALL )|(CREATE )|(INTO )/i", $str)){
		return true;
	}else{
		return false;
	}
}
//Wrapper for mysql_real_escape_string
function db_escape($val){
	return mysql_real_escape_string($val);
}
?>
