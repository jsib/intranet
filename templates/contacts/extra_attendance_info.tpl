<br/>
<h3 class='a_i_b'>Расчет начислений дней отпуска и больничного</h3>
<span class='e_a'>Ведется в пределах текущего года</span><br/><br/>
<div class='b_i_d'>
<h4 class='a_i_b'>Общая информация</h4>
Месяц и год устройства на работу: <b>{if $user_hire != '0000-00-00'}{$hire_info.user_hire_date}{else}не определено{/if}</b><br/>
{if $user_hire != '0000-00-00'}
	Период начисления: <b>{if $hire_info.count_from_begin_of_this_year === true}с начала этого года{else}с момента устройства на работу{/if}</b><br/>
	Кол-во полных месяцев/дней прошло : <b>{$hire_info.months_work}мес {$hire_info.days_work}д</b><br/><br/>
	
	<h4 class='a_i_b'>Отпуск</h4>
	Кол-во автоматически начисленных дней/часов отпуска: <b>{$credits_info.vacation.credit_days}д {$credits_info.vacation.credit_hours}ч</b>
	<div class='o_s'>
		Перенести дней отпуска с прошлого года:&nbsp;
		<input type='text' value='{$hire_info.transfer_days_number}' class='e_s_t' maxlength='2' onblur='set_transfer_days_number(this.value)' onfocus='say_result("", "")' />
		&nbsp;&nbsp;<span id='transfer_days_number_message'></span>
	</div>
	Итого с учетом перенесенных дней: <b><span id='vacation_credit_with_transferred'>{$credits_info.vacation.credit_days+$hire_info.transfer_days_number}</span>д
	&nbsp;{$credits_info.vacation.credit_hours}ч</b><br/><br/>
	
	<h4 class='a_i_b'>Больничный</h4>
	Кол-во автоматически начисленных дней/часов больничного: <b>{$credits_info.sickleave.credit_days}д {$credits_info.sickleave.credit_hours}ч</b><br/>
{else}	
<span class='e_a'>!!! Определите месяц и год устройства сотрудника на работу, чтобы получить расчет начислений</span><br/>
{/if}
</div>
<br/>
<span class='remark'>*Если не определена дата устройства сотрудника на работу, то расчет количества дней начисленного отпуска и больничного не ведется.<br/>
**Период начисления дней отпуска и больничного:<br/>
&nbsp;&nbsp;&nbsp;1) С начала этого года - для сотрудников, которые были устроены на работу в предыдущие годы<br/>
&nbsp;&nbsp;&nbsp;2) С момента устройства на работу - для сотрудников, которые были устроены на работу в этом году</span><br/>