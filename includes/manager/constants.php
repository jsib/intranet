<?php
//Месяцы - полный вариант
$MonthsFull=array(1=>'январь', 'февраль', 'март', 'апрель', 'май', 'июнь', 'июль', 'август', 'сентябрь', 'октябрь', 'ноябрь', 'декабрь');
//Месяцы - сокращенный вариант
$MonthsShort=array(	1=>'янв', 2=>'фев', 3=>'март', 4=>'апр', 5=>'май', 6=>'июн', 7=>'июл', 8=>'авг', 9=>'сент', 10=>'окт', 11=>'нояб', 12=>'дек');

// Additional tables

//Требования к свойствам объектов
const ERROR_EASY_STRING_REQUIREMENT="Поле '{name}' должно быть от 0 до 70 символов, может содержать буквы русского и английского алфавитов, цифры, знак пробела и знаки . , ) ( - @ +.";
const ERROR_USERNAME_REQUIREMENT="Имя пользователя должно быть от 1 до 70 символов, может содержать буквы русского и английского алфавитов, цифры, знак пробела, а также знаки точки.";
const ERROR_USERNAME_EXISTS="Пользователь с таким именем уже существует";
//const ERROR_OCCUPATION_REQUIREMENT="Должность должна быть от 1 до 70 символов, может содержать буквы русского и английского алфавитов и знак пробела, а также знаки точки.";
//const ERROR_SKYPE_REQUIREMENT="Skype должен быть от 1 до 70 символов, может содержать буквы русского и английского алфавитов и знак пробела, а также знаки точки.";

//Regular expressions patterns
//Old
const REGEXP_EASY_STRING="/^[a-zа-яё0-9 \.\(\)\@\+\-\,\_]{0,70}$/ui";
const REGEXP_USERNAME="/^[a-zа-яё0-9 \.]{1,255}$/ui";
//New
const REGEXP_NAME="/^[a-zа-яё0-9 \.]{1,255}$/ui";

//Text patterns
const TXT_OPTION_NOT_DEFINED="--не определено--";
const TXT_OPTION_ALL="Все";
const TXT_REQUIREMENTS_NAME="должно быть от 1 до 255 символов, может содержать буквы русского и английского алфавитов, цифры, знак пробела и знаки . , ) ( - @ +.";

//Errors patterns
const ERR_NO_PERMISSION="У вас нет разрешений на выполнение данного действия.";
const ERR_SYSTEM_ERROR='Ошибка выполнения скрипта. Пожалуйста, обратитесь к системному администратору.';


?>