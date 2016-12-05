<?php
function system_error($name, $replacements=array()){
	//Retrive global variables
	global $system_errors;
	global $system_mode;
	
	//Output style for error box
	echo "<style>.error{position:absolute;top:100px;left:100px;padding:20px;border:7px double #DA1B21;background:#FFF;color:#000;font-weight:normal;font-size:11pt;} 
	      .backtrace{font-size:9pt;}</style>";
	
	//System error is known
	if(isset($system_errors[$name])){
		//Retrieve system error array
		$system_error=$system_errors[$name];
		
		//Build HTML of function call stack
		$backtrace_html="<pre class='backtrace'>".print_r(debug_backtrace(), true)."</pre>";
		
		//Admin type errors: in production mode real message write only to log, user sees only general message; in develop mode user sees real message also. 
		if($system_error['type']=='admin'){
			if($system_mode=="production"){
				echo "<div class='error'>".TXT_SYSTEM_ERROR."</div>";
			}else{
				echo "<div class='error'>".html_replace($system_error['text'], $replacements).$backtrace_html."</div>";
			}
		//User type errors - in production and develop mode user sees real message
		}else{
			echo "<div class='error'>".html_replace($system_error['text'], $replacements)."</div>";
		}
	//System error is unknown
	}else{
		echo "<div class='error'>".TXT_UNKNOWN_ERROR."</div>";
	}
}
?>