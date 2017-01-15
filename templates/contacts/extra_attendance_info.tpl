<br/>
<h3 class='a_i_b'>Расчет начислений дней отпуска и больничного</h3>
<span class='e_a'>Ведется в пределах текущего года</span><br/><br/>
<div class='b_i_d'>
Месяц и год приема на работу: <b>{$hire_info.user_hire_date}</b><br/>
{if $user_hire != '0000-00-00'}
	Принцип начисления: <b>{if $hire_info.count_from_begin_of_this_year === true}с начала этого года{else}с момента устройства на работу{/if}</b><br/>
	Кол-во полных месяцев/дней прошло : <b>{$hire_info.months_work}мес {$hire_info.days_work}д</b><br/>
	Кол-во начисленных дней/часов отпуска: <b>{$credits_info.vacation.credit_days}д {$credits_info.vacation.credit_hours}ч</b><br/>
	Кол-во начисленных дней/часов больничного: <b>{$credits_info.sickleave.credit_days}д {$credits_info.sickleave.credit_hours}ч</b><br/>
{/if}
</div>
<br/>
<span class='remark'>*Если не определена дата устройства сотрудника на работу, то расчет количества дней начисленного отпуска и больничного не ведется.<br/>
<span class='remark'>**Принцип начисления дней отпуска и больничного:<br/>
&nbsp;&nbsp;&nbsp;1) С начала этого года - для сотрудников, которые были устроены на работу в предыдущие годы<br/>
&nbsp;&nbsp;&nbsp;2) С момента устройства на работу - для сотрудников, которые были устроены на работу в этом году</span><br/>

