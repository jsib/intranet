<?php
/* Smarty version 3.1.30, created on 2017-01-16 02:18:37
  from "/home/portal.domyshev.ru/www/templates/contacts/extra_attendance_info.tpl" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_587c034d3e1b60_32159358',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'ae4e21496b9920b52ed8c1dd68006bd09acd896c' => 
    array (
      0 => '/home/portal.domyshev.ru/www/templates/contacts/extra_attendance_info.tpl',
      1 => 1484522312,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_587c034d3e1b60_32159358 (Smarty_Internal_Template $_smarty_tpl) {
?>
<br/>
<h3 class='a_i_b'>Расчет начислений дней отпуска и больничного</h3>
<span class='e_a'>Ведется в пределах текущего года</span><br/><br/>
<div class='b_i_d'>
Месяц и год приема на работу: <b><?php echo $_smarty_tpl->tpl_vars['hire_info']->value['user_hire_date'];?>
</b><br/>
<?php if ($_smarty_tpl->tpl_vars['user_hire']->value != '0000-00-00') {?>
	Принцип начисления: <b><?php if ($_smarty_tpl->tpl_vars['hire_info']->value['count_from_begin_of_this_year'] === true) {?>с начала этого года<?php } else { ?>с момента устройства на работу<?php }?></b><br/>
	Кол-во полных месяцев/дней прошло : <b><?php echo $_smarty_tpl->tpl_vars['hire_info']->value['months_work'];?>
мес <?php echo $_smarty_tpl->tpl_vars['hire_info']->value['days_work'];?>
д</b><br/>
	Кол-во начисленных дней/часов отпуска: <b><?php echo $_smarty_tpl->tpl_vars['credits_info']->value['vacation']['credit_days'];?>
д <?php echo $_smarty_tpl->tpl_vars['credits_info']->value['vacation']['credit_hours'];?>
ч</b><br/>
	Кол-во начисленных дней/часов больничного: <b><?php echo $_smarty_tpl->tpl_vars['credits_info']->value['sickleave']['credit_days'];?>
д <?php echo $_smarty_tpl->tpl_vars['credits_info']->value['sickleave']['credit_hours'];?>
ч</b><br/>
<?php }?>
</div>
<br/>
<span class='remark'>*Если не определена дата устройства сотрудника на работу, то расчет количества дней начисленного отпуска и больничного не ведется.<br/>
<span class='remark'>**Принцип начисления дней отпуска и больничного:<br/>
&nbsp;&nbsp;&nbsp;1) С начала этого года - для сотрудников, которые были устроены на работу в предыдущие годы<br/>
&nbsp;&nbsp;&nbsp;2) С момента устройства на работу - для сотрудников, которые были устроены на работу в этом году</span><br/>

<?php }
}
