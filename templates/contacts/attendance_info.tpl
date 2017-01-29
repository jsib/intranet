<h3 class='a_i_b'>Учёт рабочего времени</h3>
<span class='e_a'>Учет ведется в пределах текущего года</span><br/><br/>

<div class='b_i_d'>
	{if $show_hr_information==true}
		<input type='hidden' id='show_hr_information' value='1'>
	{else}
		<input type='hidden' id='show_hr_information' value='0'>
	{/if}
	
	{if $show_hr_information==true}
		<h4 class='a_i_b'>Общая информация</h4>
		Месяц и год устройства на работу: 
		{if $user_hire != '0000-00-00'}
			{$hire_info.user_hire_date}
		{else}
			не определено
		{/if}
	
		<br/>
	{/if}

	{if $user_hire != '0000-00-00'}
		{if $show_hr_information==true}
			Период начисления отпуска и больничного: 
			{if $hire_info.count_from_begin_of_this_year === true}
				с начала этого года
			{else}
				с момента устройства на работу 
			{/if}
			
			<br/><br/>
		{/if}
	
		<h4 class='a_i_b'>Отпуск</h4>
		
		Начислено: 
		<span id='vacation_credit_days_number'>{$vacation_granted_benefits['days']}</span>д <span id='vacation_credit_hours_number' {if $vacation_granted_benefits['hours']==0}style='display:none'{/if}>{$vacation_granted_benefits['hours']}</span></span>{if $vacation_granted_benefits['hours']>0}ч{/if}<br/>

		{if $show_hr_information==true}
			<div class='i_w_t'>
				Перенесено с прошлого года:
				<input id='vacation_transfer_days_number' type='text' value='{$vacation_survive_benefits['days']}' class='e_s_t' maxlength='2' onblur='set_transfer_days_number(this.value)' onfocus='say_result("", "")' />&nbsp;д
				&nbsp;&nbsp;<span id='transfer_days_number_message'></span>
			</div>
		{/if}
		
		Начислено с учетом перенесенных: <b><span id='vacation_credit_days_number_plus_transferred'></span></b>
		
		<br/><br/>
		
		Использовано:  
		
		<b><span id='vacation_used_days_number'>{$attendance_info[2]['used_days']}</span>д <span id='vacation_used_hours_number' {if $attendance_info[2]['used_hours']==0}style='display:none'{/if}>{$attendance_info[2]['used_hours']}</span></span>{if $attendance_info[2]['used_hours']>0}ч{/if}</b><br/>
		{if $attendance_info[2]['hours']>0}<i>Когда: {$attendance_info[2]['when']}<br/></i>{/if}
		
		{if $show_hr_information==false}
			<input type='hidden' id='vacation_credit_days_number' value='{$vacation_granted_benefits['days']}'>
			<input type='hidden' id='vacation_credit_hours_number' value='{$vacation_granted_benefits['hours']}'>
			<input type='hidden' id='vacation_transfer_days_number' value='{$vacation_survive_benefits['days']}'>
		{/if}
		
		<br/>
		
		Остаток:
		
		<b>
			<span id='vacation_rest'></span>
		</b>

		<br/><br/>
		
		<h4 class='a_i_b'>
			Больничный
		</h4>
		
		Начислено: 
		
		<span id='sickleave_credit_days_number'>{$sickleave_granted_benefits['days']}</span>д 
		<span id='sickleave_credit_hours_number' {if $sickleave_granted_benefits['hours']==0}style='display:none'{/if}>
			{$sickleave_granted_benefits['hours']}
		</span>
		{if $sickleave_granted_benefits['hours']>0}ч{/if}

		<br/><br/>
		
		Использовано: 
		<span id='sickleave_used_days_number'>{$attendance_info[3]['used_days']}</span>д <span id='sickleave_used_hours_number' {if $attendance_info[3]['used_hours']==0}style='display:none'{/if}>{$attendance_info[3]['used_hours']}</span>{if $attendance_info[3]['used_hours']>0}ч{/if}
		
		<br/>
		
		{if $attendance_info[3]['hours']>0}
			<i>Когда: {$attendance_info[3]['when']}</i>
			
			<br/>
		{/if}
		
		<br/>
		
		Остаток:
		<span id='sickleave_rest'></span><br/><br/>
		
		<h4 class='a_i_b'>За свой счёт</h4>
		Использовано: {$attendance_info[4]['used_days']}д {if $attendance_info[4]['used_hours']>0}{$attendance_info[4]['used_hours']}ч{/if}<br/>
		{if $attendance_info[4]['hours']>0}Когда: {$attendance_info[4]['when']}<br/>{/if}<br/>
		
		
		<h4 class='a_i_b'>Командировка</h4>
		Использовано: {$attendance_info[5]['used_days']}д {if $attendance_info[5]['used_hours']>0}{$attendance_info[5]['used_hours']}ч{/if}<br/>
		{if $attendance_info[5]['hours']>0}Когда: {$attendance_info[5]['when']}<br/>{/if}<br/>
		
		
		<h4 class='a_i_b'>Переработка</h4>
		Использовано: {$attendance_info[10]['used_days']}д {if $attendance_info[10]['used_hours']>0}{$attendance_info[10]['used_hours']}ч{/if}<br/>
		{if $attendance_info[10]['hours']>0}Когда: {$attendance_info[10]['when']}<br/>{/if}<br/>
		
		<h4 class='a_i_b'>Оплачиваемая командировка</h4>
		Использовано: {$attendance_info[11]['used_days']}д {if $attendance_info[11]['used_hours']>0}{$attendance_info[11]['used_hours']}ч{/if}<br/>
		{if $attendance_info[11]['hours']>0}Когда: {$attendance_info[11]['when']}<br/>{/if}<br/>	
	
	{else}	
		<span class='sm_e'>!!! Определите месяц и год устройства сотрудника на работу, чтобы получить расчет начислений</span><br/>
	{/if}
</div>
<br/>
{if $show_hr_information==true}
<span class='remark'>*Если не определена дата устройства сотрудника на работу, то расчет количества дней начисленного отпуска и больничного не ведется.<br/>
**Период начисления дней отпуска и больничного:<br/>
&nbsp;&nbsp;&nbsp;1) С начала этого года - для сотрудников, которые были устроены на работу в предыдущие годы<br/>
&nbsp;&nbsp;&nbsp;2) С момента устройства на работу - для сотрудников, которые были устроены на работу в этом году</span><br/>
{/if}

<script>
	load_manager();
</script>