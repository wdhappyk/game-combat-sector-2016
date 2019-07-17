<?php
define('cms', 1);
require_once 'header.php';

if ($user) {
	//удаляем куки
	setcookie("userID", $uid['id'], (time() - (86400 * 7)));
	setcookie("userMAIL", $login, (time() - (86400 * 7)));
	session_destroy(); //уничтожаем сесии

	$sql->query("UPDATE `users` SET `online` = '0' WHERE `id` = '".$user."'");
}

header('Location: ./');
?>