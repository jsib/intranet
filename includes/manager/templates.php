<?
//Perform replacements in the template
function template_get($template_file, $replacements=array()){
    if(template_exists($template_file)){
        //Get text from the template
        $text=file_easy_read($_SERVER['DOCUMENT_ROOT']."/templates/$template_file.html");
		
		//Perform replacements in text
		$text=text_replace($text, $replacements);
    }else{
		//Perform errors
        echo "DIS error. Template file '$template_file' doesn't exist.";
    }
}

//Check template file existance
function template_exists($template_file){
    return file_exists($_SERVER['DOCUMENT_ROOT']."/templates/$template_file.html");
}

//Perform replacements in text
function text_replace($text, $replacements=array()){
	//Check for number of replacements
	if(count($replacements)>0){
		//Iterate replacements...
		foreach($replacements as $match=>$replacement){
			//Replace all mathes '{$match}' to '$replacement'
			$text=str_replace("{".$match."}", $replacement, $text);
		}
	}
	
	//Return finished text
	return $text;
}
?>