function load_manager(){
	update_vacation_information();
	update_sickleave_information();
}

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
				say_result('Изменения успешно сохранены', 'sm_s')
				update_vacation_information();
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
	transfer_days_number_message.className=class_name;
}

function update_vacation_information(){
	//Get information about vacation credit
	if(document.getElementById('show_hr_information').value=='1'){
		credit_days_number=parseInt(document.getElementById('vacation_credit_days_number').innerHTML);
		credit_hours_number=parseInt(document.getElementById('vacation_credit_hours_number').innerHTML);
	}else{
		credit_days_number=parseInt(document.getElementById('vacation_credit_days_number').value);
		credit_hours_number=parseInt(document.getElementById('vacation_credit_hours_number').value);
	}	
	
	credit_hours_number_total=credit_days_number*8+credit_hours_number;
	
	//Get information about vacation credit transferred from previous year
	transfer_days_number=parseInt(document.getElementById('vacation_transfer_days_number').value);
	
	//Get information about vacation uses
	used_days_number=parseInt(document.getElementById('vacation_used_days_number').innerHTML);
	used_hours_number=parseInt(document.getElementById('vacation_used_hours_number').innerHTML);
	used_hours_number_total=used_days_number*8+used_hours_number;
	
	//Get rest days number
	rest_hours_number_total=credit_hours_number_total+transfer_days_number*8-used_hours_number_total;
	rest_hours_number_total_abs=Math.abs(rest_hours_number_total);
	rest_hours_number=rest_hours_number_total_abs%8;
	rest_days_number=(rest_hours_number_total_abs-rest_hours_number)/8;
	rest_sign=rest_hours_number_total/rest_hours_number_total_abs;
	if(rest_sign==-1){
		rest_sign_str=rest_sign.toString().substr(0,1);
	}else{
		rest_sign_str='';
	}
	
	//Put credit + transferred days sum
	document.getElementById('vacation_credit_days_number_plus_transferred').innerHTML=(credit_days_number+transfer_days_number).toString()+'д '+credit_hours_number+'ч';

	//Put rest vacation information to page
	document.getElementById('vacation_rest').innerHTML=rest_sign_str+rest_days_number.toString()+'д '+rest_hours_number.toString()+'ч';
	
}

function update_sickleave_information(){
	//Get information about sickleave credit
	credit_days_number=parseInt(document.getElementById('sickleave_credit_days_number').innerHTML);
	credit_hours_number=parseInt(document.getElementById('sickleave_credit_hours_number').innerHTML);
	credit_hours_number_total=credit_days_number*8+credit_hours_number;
	
	//Get information about sickleave uses
	used_days_number=parseInt(document.getElementById('sickleave_used_days_number').innerHTML);
	used_hours_number=parseInt(document.getElementById('sickleave_used_hours_number').innerHTML);
	used_hours_number_total=used_days_number*8+used_hours_number;
	
	//Get rest days number
	rest_hours_number_total=credit_hours_number_total-used_hours_number_total;
	rest_hours_number_total_abs=Math.abs(rest_hours_number_total);
	rest_hours_number=rest_hours_number_total_abs%8;
	rest_days_number=(rest_hours_number_total_abs-rest_hours_number)/8;
	rest_sign=rest_hours_number_total/rest_hours_number_total_abs;
	if(rest_sign==-1){
		rest_sign_str=rest_sign.toString().substr(0,1);
	}else{
		rest_sign_str='';
	}
	
	//Put rest sickleave information to page
	document.getElementById('sickleave_rest').innerHTML=rest_sign_str+rest_days_number.toString()+'д '+rest_hours_number.toString()+'ч';
	
}