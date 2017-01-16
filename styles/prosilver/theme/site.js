function set_transfer_days_number(days_number){
	//alert(status)
	date=new Date();
	uri=getQueryParams(document.location.search);
	transfer_days_number_message=document.getElementById('transfer_days_number_message')

	//Check correct number of days
	if(!isInt(days_number)){
		say_result('Ошибка. Допускаются только целые числа.', 'sm_e')
		return;		
	}
	
	$.ajax({
		type: "GET",
		data: "user_id="+uri.contact+"&days_number="+days_number+"&year="+date.getFullYear(),
		dataType: "html",
		url: "/ajax.php?action=set_transfer_days_number",
		dataType: "text",
		success: function(result){
			if(result==1){
				document.getElementById('vacation_credit_with_transferred').innerHTML=days_number
				say_result('Изменения успешно сохранены', 'sm_s')
			}else{
				say_result('Ошибка. Не удалось сохранить значение Обратитесь к системному администратору.', 'sm_e')
				return;		
			}
		},
		
		error: function(result, ajax_stat, ajax_error){
			alert('Возникла ошибка при обновлении данных.')
		},
		beforeSend: function(){
			//alert("Начинаем отправку")
			//document.getElementById("preloader").style.display='block';
		},
		complete: function(){
			//alert("Данные получены")
			//document.getElementById("preloader").style.display='none';
		}
		
	});
}

//Query parameters from URI
//Using: var query = getQueryParams(document.location.search);
//alert(query.foo);
function getQueryParams(qs) {
    qs = qs.split('+').join(' ');

    var params = {},
        tokens,
        re = /[?&]?([^=]+)=([^&]*)/g;

    while (tokens = re.exec(qs)) {
        params[decodeURIComponent(tokens[1])] = decodeURIComponent(tokens[2]);
    }

    return params;
}

function isInt(value) {
  return !isNaN(value) && 
         parseInt(Number(value)) == value && 
         !isNaN(parseInt(value, 10));
}

function say_result(message_text, class_name){
	transfer_days_number_message.innerHTML=message_text;
	transfer_days_number_message.className=class_name
}