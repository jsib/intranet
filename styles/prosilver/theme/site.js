function set_transfer_days_number(days_number){
	//alert(status)
	date=new Date();
	uri=getQueryParams(document.location.search);
	transfer_days_number_message=document.getElementById('transfer_days_number_message')
	alert(1)
	//Check correct number of days
	if(!isInteger(days_number)){
		alert(3)
		transfer_days_number_message.innerHTML='Ошибка. Должно быть введено число.';
		transfer_days_number_message.className='sm_e'
		exit;		
	}else{
		alert(2)
	}
	
	if(days_number < -127 || days_number > 127){
		transfer_days_number_message.innerHTML='Ошибка. Значение должно быть в диапазоне от -127 до 127.';
		transfer_days_number_message.className='sm_e'
		exit;
	}
	
	alert('ok')
	
	$.ajax({
		type: "GET",
		data: "user_id="+uri.contact+"&days_number="+days_number+"&year="+date.getFullYear(),
		dataType: "html",
		url: "/ajax.php?action=set_transfer_days_number",
		dataType: "text",
		success: function(result){
			if(result==1){
				alert('Запись произведена успешно')
			}else{
				//alert('Возникла ошибка при обновлении данных в графике работы (db error).')
				alert(result);
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

