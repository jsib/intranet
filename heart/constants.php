<?php
//Text patterns
const TXT_UNKNOWN_ERROR="Произошла ошибка при выполнении программы (999). Пожалуйста, обратитесь к системному администратору.";
const TXT_SYSTEM_ERROR="Произошла ошибка при выполнении программы. Пожалуйста, обратитесь к системному администратору.";
const TXT_REQUIREMENT_USERNAME="Имя пользователя должно быть длиной от 1 до 70 символов, может содержать буквы русского и английского алфавитов, цифры, знак пробела, а также знаки точки.";
const TXT_PERMISSION_DENIED="У вас нет прав на выполнение данного действия";
const TXT_RESULT_NOT_DEFINED_FOR_THIS_OBJECT_AND_ACTION="Result '{result}' doesn't defined for object '{object}' and action '{action}'.";
const TXT_TRY_EDIT_NON_EXISTENT_ENTITY="Try to edit entity of object '{object}' with non-existen id '{id}'.";
const TXT_TRY_DELETE_NON_EXISTENT_ENTITY="Try to delete entity of object '{object}' with non-existen id '{id}'.";
const TXT_TEMPLATE_FILE_DOESNT_EXIST="Template file '{file}' doesn't exist.";

//Regular expressions patterns
const REGEXP_USERNAME="/^[a-zа-яё0-9 \.]{1,70}$/ui";
const REGEXP_OCCUPATION="/^[a-zа-я \.]{1,70}$/ui";
const REGEXP_SKYPE="/^[a-zа-я \.]{1,70}$/ui";
?>