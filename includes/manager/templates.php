<?
//Perform replacements in the template
function template_get($template_file, $replacements=array()){
    if(template_exists($template_file)){
        //Get HTML from the template
        $html=file_easy_read($_SERVER['DOCUMENT_ROOT']."/templates/$template_file.html");
		
		//Perform replacements in HTML
		$html=html_replace($html, $replacements);
    }else{
		//Perform errors
        dis_error("Template file '$template_file' doesn't exist", 'echo');
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
?>