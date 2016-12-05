<?
//Perform replacements in the template
function template_get($template_file, $replacements=array()){
    if(template_exists($template_file)){
        //Get HTML from the template
        $html=file_easy_read($_SERVER['DOCUMENT_ROOT']."/templates/$template_file.html");
		
		//Perform replacements in HTML
		$html=html_replace($html, $replacements);
    }else{
		//Handle error
        system_error('template_file_doesnt_exist', array('file'=>$template_file));
    }
	
	//Return HTML flow
	return $html;
}

//Check template file existance
function template_exists($template_file){
    return file_exists($_SERVER['DOCUMENT_ROOT']."/templates/$template_file.html");
}

//Perform replacements in HTML
function html_replace($html, $replacements=array()){
	//Check for number of replacements
	if(count($replacements)>0){
		//Iterate replacements...
		foreach($replacements as $match=>$replacement){
			//Replace all mathes '{$match}' to '$replacement'
			$html=str_replace("{".$match."}", $replacement, $html);
		}
	}
	
	//Return HTML flow
	return $html;
}

//Return HTML of final message
function show_messages($messages){
	//Define HTML flow
	$html="";

	//Build final message
	if(count($messages)>0){
		foreach($messages as $index=>$message){
			//Build final message HTML
			$messages_html.=$message;
			
			//Add empty string
			$index<count($messages) ? $messages_html.="<br/>" : '';
		}
		
		//Build final HTML
		$html=template_get("message", array('message'=>$messages_html));
	//There is no messages
	}else{
		//Build final HTML
		$html=template_get("no_message");
	}
	
	//Return HTML flow
	return $html;
}
?>