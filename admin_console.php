<?php
define('cms', 1);
require_once 'header.php';

$conf = $sql->query("SELECT * FROM `conf`")->fetch_array(MYSQLI_ASSOC);

if ($user) { //если игрок
	$u = $sql->query("SELECT * FROM `users` WHERE `id` = '".$user."'")->fetch_array(MYSQLI_ASSOC);
	$admin = $sql->query("SELECT * FROM `admins` WHERE `uid` = '".$u['id']."'")->num_rows;

	if ($admin) { //если админ

		switch($_GET['a']) {

			default:
			echo '<div class="menu">Панель администратора</div>';
			echo '<ul class="links">';
			echo '<li><a href="?a=admin_list">Список администраторов</a></li>';
			echo '<li><a href="?a=add_admin">Добавить администратора</a></li>';
			echo '<li><a href="?a=update_admin">Изменить администратора</a></li>';
			echo '<li><a href="?a=delete_admin">Удалить администратора</a></li>';
			echo '<li><a href="?a=bot_list">Список ботов</a></li>';
			echo '<li><a href="?a=add_bot">Добавить бота</a></li>';
			echo '<li><a href="?a=update_bot">Изменить бота</a></li>';
			echo '<li><a href="?a=delete_bot">Удалить бота</a></li>';
			echo '<li><a href="?a=add_shmot_bot">Добавить товар/дроп боту</a></li>';
			echo '<li><a href="?a=delete_shmot_bot">Удалить товар/дроп бота</a></li>';
			echo '<li><a href="?a=add_naw_bot">Дабоваить навык боту</a></li>';
			echo '<li><a href="?a=delete_naw_bot">Удалить навык у бота</a></li>';
			echo '<li><a href="?a=conf">Настройки игры</a></li>';
			echo '<li><a href="?a=add_location">Добавить локацию</a></li>';
			echo '<li><a href="?a=update_location">Изменить локацию</a></li>';
			echo '<li><a href="?a=delete_location">Удалить локацию</a></li>';
			echo '<li><a href="?a=add_location_predmet">Добавить предмет на локацию</a></li>';
			echo '<li><a href="?a=update_location_predmet">Изменить предмет на локации</a></li>';
			echo '<li><a href="?a=delete_location_predmet">Удалить предмет с локации</a>';
			echo '<li><a href="?a=location_spaun_list">Список спаунов</a></li>';
			echo '<li><a href="?a=add_location_spaun">Добавить место спауна</a></li>';
			echo '<li><a href="?a=update_location_spaun">Изменить место спауна</a></li>';
			echo '<li><a href="?a=delete_location_spaun">Удалить место спауна</a></li>';
			echo '<li><a href="?a=quest_list">Список заданий</a></li>';
			echo '<li><a href="?a=add_quest">Добавить задание</a></li>';
			echo '<li><a href="?a=update_quest">Изменить задание</a></li>';
			echo '<li><a href="?a=delete_quest">Удалить задание</a></li>';
			echo '<li><a href="?a=quest_treb">Требование к заданию</a></li>';
			echo '<li><a href="?a=add_quest_nagrada">Добавить награду за задание</a></li>';
			echo '<li><a href="?a=delete_quest_nagrada">Удалить награду за задание</a></li>';
			echo '<li><a href="?a=shmot_list">Список предметов</a></li>';
			echo '<li><a href="?a=add_shmot">Добавить предмет</a></li>';
			echo '<li><a href="?a=update_shmot">Изменить предмет</a></li>';
			echo '<li><a href="?a=delete_shmot">Удалить предмет</a></li>';
			echo '<li><a href="?a=table_lvl_list">Таблица уровней(игроки)</a></li>';
			echo '<li><a href="?a=table_lvl_naw_list">Таблица уровней(навыки)</a></li>';
			echo '<li><a href="?a=user_list">Список игроков</a></li>';
			echo '<li><a href="?a=update_user">Изменить игрока</a></li>';
			echo '<li><a href="?a=delete_user">Удалить игрока</a></li>';
			echo '<li><a href="?a=zvanka">Таблица званий</a></li>';
			echo '</ul>';
			echo '<div class="line"></div>';
			echo '<div class="foot_a">';
			echo '<a href="./">Закрыть</a>';
			echo '</div>';
			break;

			//добавить локацию
			case 'add_location':
			echo '<div class="menu">Добавить локацию</div>';
			echo '<div class="text">';
			echo '<form method="post" action="">';
			echo 'Введите координаты <input type="submit" name="search" value="Проверить"/><br/>';
			echo 'Локация: <input type="number" name="loc" value="0"/><br/>';
			echo 'X: <input type="number" name="x" value="0"/><br/>';
			echo 'Y: <input type="number" name="y" value="0"/>';
			echo '</form>';
			echo '</div>';

			if (isset($_POST['loc'])) {
				echo '<div class="line"></div>';
				echo '<div class="text">';

				$loc_sql = $sql->query("SELECT * FROM `locations` WHERE `loc` = '".$_POST['loc']."' AND `x` = '".$_POST['x']."' AND `y` = '".$_POST['y']."'");
				$loc_all = $loc_sql->num_rows;

				if ($loc_all == 0) {

					if (isset($_POST['add'])) {
						$sql->query("INSERT INTO `locations` SET `loc` = '".$_POST['loc']."', 
							`x` = '".$_POST['x']."', 
							`y` = '".$_POST['y']."', 
							`strike_off` = '".$_POST['strike_off']."', 
							`type` = '".$_POST['type']."', 
							`img` = '".$_POST['img']."';");

						echo 'Локация создана';
						echo '</div>';
						echo '<div class="line"></div>';
						echo '<div class="text">';
					} else {

						echo '<form method="post" action="">';
						echo '<input type="hidden" name="loc" value="'.$_POST['loc'].'"/>';
						echo '<input type="hidden" name="x" value="'.$_POST['x'].'"/>';
						echo '<input type="hidden" name="y" value="'.$_POST['y'].'"/>';
						echo 'Стрельба: <select name="strike_off">';
						echo '<option value="0">разрешена</option>';
						echo '<option value="1">запрещена</option>';
						echo '</select><br/>';
						echo 'Тип: <select name="type">';
						echo '<option value="0">поверхность</option>';
						echo '<option value="1">вода</option>';
						echo '</select><br/>';
						echo 'Изображение №<input type="number" name="img" value="0"/><br/>';
						echo '<input type="submit" name="add" value="Добавить"/>';
						echo '</form>';

					}

				} else {
					echo 'Локация уже создана';
				}

				echo '</div>';

			}




			echo '<div class="line"></div>';
			echo '<div class="foot_a">';
			echo '<a href="./admin_console.php">Панель администратора</a>';
			echo '</div>';
			break;
			//добавить локацию.конец

			//список предметов
			case 'shmot_list':
			echo '<div class="menu">Список предметов</div>';

			$shmot_all = $sql->query("SELECT * FROM `shmots`")->num_rows;

			echo 'Высего предметов: '.$shmot_all;
			echo '<div class="line"></div>';
			echo '<div class="mmenu">Список</div>';

			if ($shmot_all != 0) {

				if (isset($_GET['str'])) {
					$str = $_GET['str'] * 10;
				} else {
					$str = 0;
				}

				$str2 = $str + 10;
				$shmot_sql = $sql->query("SELECT * FROM `shmots` ORDER BY `id` ASC LIMIT ".$str.", ".$str2."");

				echo '<ul class="links">';

				while ($s = $shmot_sql->fetch_array(MYSQLI_ASSOC)) {
					echo '<li><a href="?a=shmot_list&str='.($str / 10).'&id='.$s['id'].'">'.$s['title'].' <font color="#666">['.$s['lvl'].' ур]</font></a></li>';

					if (isset($_GET['id']) && $_GET['id'] == $s['id']) {
						echo '<div class="text">';
						echo '[<a href="?a=update_shmot&id='.$s['id'].'">изменить</a>] [<a href="?a=delete_shmot&id='.$s['id'].'">удалить</a>]';
						echo '</div>';
						echo '<div class="line"></div>';
					}

				}

				echo '</ul>';
				echo '<div class="line"></div>';

				$strAll = $shmot_all / 10;

				echo '<div class="text">';

				for ($s = 0; $s <= $strAll; $s++) {
					echo '<font color="#666">[<a href="?a=shmot_list&str='.$s.'">'.($s + 1).'</a>]</font>';
				}

				echo '</div>';

			} else {
				echo '<div class="text">Список пуст</div>';
			}

			echo '<div class="line"></div>';
			echo '<div class="foot_a">';
			echo '<a href="./admin_console.php">Панель администратора</a>';
			echo '</div>';
			break;
			//список предметов.конец

			//изменить предмет
			case 'update_shmot':
			$id = 0;

			if (isset($_GET['id'])) $id = $_GET['id'];
			if (isset($_POST['id'])) $id = $_POST['id'];

			echo '<div class="menu">Изменение предмета</div>';
			echo '<div class="text">';
			echo '<form method="post" action="">';
			echo 'ID предмета: <input type="text" name="id" value="'.$id.'"/> <input type="submit" name="search" value="Найти"/>';
			echo '</form>';
			echo '</div>';

			if (isset($_POST['id'])) {
				$shmot_sql = $sql->query("SELECT * FROM `shmots` WHERE `id` = '".$_POST['id']."'");
				$shmot_all = $shmot_sql->num_rows;

				if ($shmot_all != 0) {
					$s = $shmot_sql->fetch_array(MYSQLI_ASSOC);

					if (isset($_POST['update'])) {
						$sql->query("UPDATE `shmots` SET `lvl` = '".$_POST['lvl']."', `title` = '".$_POST['title']."', `type` = '".$_POST['type']."', `t_naw` = '".$_POST['t_naw']."', `t_naw_lvl` = '".$_POST['t_naw_lvl']."', `speed_att_all` = '".$_POST['speed_att_all']."', `kalibr` = '".$_POST['kalibr']."', `patron` = '".$_POST['patron']."', `patron_all` = '".$_POST['patron_all']."', `att` = '".$_POST['att']."', `att_all` = '".$_POST['att_all']."', `rej_str` = '".$_POST['rej_str_all']."', `rej_str_all` = '".$_POST['rej_str_all']."', `radius_att` = '".$_POST['radius_att']."', `pr` = '".$_POST['pr']."', `pr_all` = '".$_POST['pr_all']."', `ves` = '".$_POST['ves']."', `def` = '".$_POST['def']."', `str` = '".$_POST['str']."', `agi` = '".$_POST['agi']."', `dex` = '".$_POST['dex']."', `hp` = '".$_POST['hp']."', `hp_all` = '".$_POST['hp_all']."', `en` = '".$_POST['en']."', `en_all` = '".$_POST['en_all']."', `speed_hod_all` = '".$_POST['speed_hod_all']."', `ruki` = '".$_POST['ruki']."', `kol_vo` = '".$_POST['kol_vo']."', `kol_vo_all` = '".$_POST['kol_vo_all']."', `vmest` = '".$_POST['vmest']."', `cost` = '".$_POST['cost']."' WHERE `id` = '".$s['id']."'");
						
						echo '<div class="line"></div>';
						echo '<div class="text"><font color="#00FF00">Сохранено</font></div>';
						echo '<div class="line"></div>';
					}

					echo '<div class="text">';
					echo '<form method="post" action="">';
					echo 'Уровень: <input type="number" name="lvl" value="'.$s['lvl'].'"/><br/>';
					echo 'Название: <input type="text" name="title" value="'.$s['title'].'" required/><br/>';
					echo 'Тип: <select name="type">';
					echo '<option value="1">Броня</option>';
					echo '<option value="2"';
					if ($s['type'] == 2) echo ' selected';
					echo '>Жилет</option>';
					echo '<option value="3"';
					if ($s['type'] == 3) echo ' selected';
					echo '>Холодное оружие</option>';
					echo '<option value="4"';
					if ($s['type'] == 4) echo ' selected';
					echo '>Метательное оружие</option>';
					echo '<option value="5"';
					if ($s['type'] == 5) echo ' selected';
					echo '>Пистолет</option>';
					echo '<option value="6"';
					if ($s['type'] == 6) echo ' selected';
					echo '>Пистолет-пулемёт</option>';
					echo '<option value="7"';
					if ($s['type'] == 7) echo ' selected';
					echo '>Пулемёт</option>';
					echo '<option value="8"';
					if ($s['type'] == 8) echo ' selected';
					echo '>Дробовик</option>';
					echo '<option value="9"';
					if ($s['type'] == 9) echo ' selected';
					echo '>Винтовка</option>';
					echo '<option value="10"';
					if ($s['type'] == 10) echo ' selected';
					echo '>Автомат</option>';
					echo '<option value="11"';
					if ($s['type'] == 11) echo ' selected';
					echo '>Боеприпас</option>';
					echo '<option value="12"';
					if ($s['type'] == 12) echo ' selected';
					echo '>Медикамент</option>';
					echo '<option value="13"';
					if ($s['type'] == 13) echo ' selected';
					echo '>Материал</option>';
					echo '<option value="14"';
					if ($s['type'] == 14) echo ' selected';
					echo '>Инструмент</option>';
					echo '<option value="15"';
					if ($s['type'] == 15) echo ' selected';
					echo '>Схема</option>';
					echo '<option value="16"';
					if ($s['type'] == 16) echo ' selected';
					echo '>Предмет</option>';
					echo '</select><br/>';
					echo 'Требуемый навык: <input type="text" name="t_naw" value="'.$s['t_naw'].'" required/><br/>';
					echo 'Уровень требуемого навыка: <input type="number" name="t_naw_lvl" value="'.$s['t_naw_lvl'].'"/><br/>';
					echo 'Скорость атаки: <input type="number" name="speed_att_all" value="'.$s['speed_att_all'].'"/>сек.<br/>';
					echo 'Калибр: <input type="text" name="kalibr" value="'.$s['kalibr'].'"/><br/>';
					echo 'Обойма: <input type="number" name="patron" value="'.$s['patron'].'"/>-<input type="number" name="patron_all" value="'.$s['patron_all'].'"/><br/>';
					echo 'Урон: <input type="number" name="att" value="'.$s['att'].'"/>...<input type="number" name="att_all" value="'.$s['att_all'].'"/><br/>';
					echo 'Режим стрельбы: <select name="rej_str_all">';
					echo '<option value="1">Одиночиный/Удар</option>';
					echo '<option value="2"';
					if ($s['rej_str_all'] == 2) echo ' selected';
					echo '>Одиночный/Очередь</option>';
					echo '</select><br/>';
					echo 'Радиус атаки: <input type="number" name="radius_att" value="'.$s['radius_att'].'"/>кл.<br/>';
					echo 'Прочность: <input type="number" name="pr" value="'.$s['pr'].'"/>/<input type="number" name="pr_all" value="'.$s['pr_all'].'"/><br/>';
					echo 'Вес(в виде 0.000): <input type="text" name="ves" value="'.$s['ves'].'"/>кг.<br/>';
					echo 'Защита + <input type="number" name="def" value="'.$s['def'].'"/><br/>';
					echo 'Сила + <input type="number" name="str" value="'.$s['str'].'"/><br/>';
					echo 'Ловкость + <input type="number" name="str" value="'.$s['agi'].'"/><br/>';
					echo 'Меткость + <input type="number" name="dex" value="'.$s['dex'].'"/><br/>';
					echo 'Здоровье(для медикаментов) + <input type="number" name="hp" value="'.$s['hp'].'"/><br/>';
					echo 'Макс.Здоровье(для шмота) + <input type="number" name="hp_all" value="'.$s['hp_all'].'"/><br/>';
					echo 'Энергия(для медикаментов) + <input type="number" name="en" value="'.$s['en'].'"/><br/>';
					echo 'Макс.Энергия(для шмота) + <input type="number" name="en_all" value="'.$s['en_all'].'"/><br/>';
					echo 'Скорость передвижения(затормаживает) + <input type="number" name="speed_hod_all" value="'.$s['speed_hod_all'].'"/><br/>';
					echo 'В руки: <select name="ruki">';
					echo '<option value="0">не берётся</option>';
					echo '<option value="1"';
					if ($s['ruki'] == 1) echo ' selected';
					echo '>в 1(для оружия)</option>';
					echo '<option value="2"';
					if ($s['ruki'] == 2) echo ' selected';
					echo '>в 2(для оружия)</option>';
					echo '</select><br/>';
					echo 'Количество(для суммируемых)(не обязательно): <input type="number" name="kol_vo" value="'.$s['kol_vo'].'"/><br/>';
					echo 'Суммируется(наберается ли в пачку): <select name="kol_vo_all">';
					echo '<option value="0">нет</option>';
					echo '<option value="1"';
					if ($s['kol_vo_all'] == 1) echo ' selected';
					echo '>да</option>';
					echo '</select><br/>';
					echo 'Вместимость(для жилета): <input type="number" name="vmest" value="'.$s['vmest'].'"/>предметов<br/>';
					echo 'Цена(в виде 0.00): <input type="text" name="cost" value="'.$s['cost'].'"/><br/>';
					echo '<input type="hidden" name="id" value="'.$_POST['id'].'"/>';
					echo '<input type="submit" name="update" value="Изменить"/>';
					echo '</form>';
					echo '</div>';
				} else {
					echo '<div class="text">Ничего не найдено</div>';
				}

			}

			echo '<div class="line"></div>';
			echo '<div class="foot_a">';
			echo '<a href="./admin_console.php">Панель администратора</a>';
			echo '</div>';
			break;
			//изменить предмет.конец

			//удалить предмет
			case 'delete_shmot':
			$id = 0;

			if (isset($_GET['id'])) $id = $_GET['id'];
			if (isset($_POST['id'])) $id = $_POST['id'];

			echo '<div class="menu">Удаление предмета</div>';
			echo '<div class="text">';
			echo '<form method="post" action="">';
			echo 'ID предмета: <input type="text" name="id" value="'.$id.'"/> <input type="submit" name="delete" value="Удалить"/>';
			echo '</form>';
			echo '</div>';

			if (isset($_POST['id'])) {
				$shmot_sql = $sql->query("SELECT * FROM `bots` WHERE `id` = '".$_POST['id']."'");
				$shmot_all = $shmot_sql->num_rows;

				if ($shmot_all != 0) {
					echo '<div class="line"></div>';
					echo '<div class="text"><font color="#00FF00">Предмет удалён</font></div>';

					$sql->query("DELETE FROM `shmots` WHERE `id` = '".$_POST['id']."'");

				} else {
					echo '<div class="text">Ничего не найдено</div>';
				}

			}

			echo '<div class="line"></div>';
			echo '<div class="foot_a">';
			echo '<a href="./admin_console.php">Панель администратора</a>';
			echo '</div>';
			break;
			//удалить предмет.конец

			//список ботов
			case 'bot_list':
			echo '<div class="menu">Список ботов</div>';

			$bot_all = $sql->query("SELECT * FROM `bots`")->num_rows;

			echo 'Всего ботов: '.$bot_all;
			echo '<div class="line"></div>';
			echo '<div class="mmenu">Список</div>';

			if ($bot_all != 0) {

				if (isset($_GET['str'])) {
					$str = $_GET['str'] * 10;
				} else {
					$str = 0;
				}

				$str2 = $str + 10;
				$bot_sql = $sql->query("SELECT * FROM `bots` ORDER BY `id` ASC LIMIT ".$str.", ".$str2."");

				echo '<ul class="links">';

				while ($b = $bot_sql->fetch_array(MYSQLI_ASSOC)) {
					echo '<li><a href="?a=bot_list&str='.($str / 10).'&id='.$b['id'].'">'.$b['name'].' <font color="#666">['.$b['lvl'].' ур]</font></a></li>';

					if (isset($_GET['id']) && $_GET['id'] == $b['id']) {
						echo '<div class="text">';
						echo '[<a href="?a=update_bot&id='.$b['id'].'">изменить</a>] [<a href="?a=delete_bot&id='.$b['id'].'">удалить</a>]';
						echo '</div>';
						echo '<div class="line"></div>';
					}

				}

				echo '</ul>';
				echo '<div class="line"></div>';

				$strAll = $bot_all / 10;

				echo '<div class="text">';

				for ($s = 0; $s <= $strAll; $s++) {
					echo '<font color="#666">[<a href="?a=bot_list&str='.$s.'">'.($s + 1).'</a>]</font>';
				}

				echo '</div>';

			} else {
				echo '<div class="text">Список пуст</div>';
			}

			echo '<div class="line"></div>';
			echo '<div class="foot_a">';
			echo '<a href="./admin_console.php">Панель администратора</a>';
			echo '</div>';
			break;
			//список ботов.конец

			//удалить бота
			case 'delete_bot':
			$id = 0;

			if (isset($_GET['id'])) $id = $_GET['id'];
			if (isset($_POST['id'])) $id = $_POST['id'];

			echo '<div class="menu">Удаление бота</div>';
			echo '<div class="text">';
			echo '<form method="post" action="">';
			echo 'ID бота: <input type="text" name="id" value="'.$id.'"/> <input type="submit" name="delete" value="Удалить"/>';
			echo '</form>';
			echo '</div>';

			if (isset($_POST['id'])) {
				$bot_sql = $sql->query("SELECT * FROM `bots` WHERE `id` = '".$_POST['id']."'");
				$bot_all = $bot_sql->num_rows;

				if ($bot_all != 0) {
					echo '<div class="line"></div>';
					echo '<div class="text"><font color="#00FF00">Бот удалён, квесты остались!</font></div>';

					$sql->query("DELETE FROM `bots` WHERE `id` = '".$_POST['id']."'");
					$sql->query("DELETE FROM `bots_bag` WHERE `bot` = '".$_POST['id']."'");
					$sql->query("DELETE FROM `bots_damage` WHERE `cid` = '".$_POST['id']."'");
					$sql->query("DELETE FROM `bots_naw` WHERE `bot` = '".$_POST['id']."'");

				} else {
					echo '<div class="text">Ничего не найдено</div>';
				}

			}

			echo '<div class="line"></div>';
			echo '<div class="foot_a">';
			echo '<a href="./admin_console.php">Панель администратора</a>';
			echo '</div>';
			break;
			//удалить бота.конец

			//добавить бота
			case 'add_bot':
			echo '<div class="menu">Добавление бота</div>';

			if (isset($_POST['add'])) {
				$sql->query("INSERT INTO `bots` SET `type` = '".$_POST['type']."', `name` = '".$_POST['name']."', `skin` = '".$_POST['skin']."', `lvl` = '".$_POST['lvl']."', `exp` = '".$_POST['exp']."', `money` = '".$_POST['money']."', `death_on` = '".$_POST['death_on']."', `death_time_all` = '".$_POST['death_time_all']."', `chat` = '".$_POST['chat']."', `skup_luta_on` = '".$_POST['skup_luta_on']."', `kuznec_on` = '".$_POST['kuznec_on']."', `agi` = '".$_POST['agi']."', `hp` = '".$_POST['hp']."', `hp_all` = '".$_POST['hp_all']."', `dex` = '".$_POST['dex']."', `def` = '".$_POST['def']."', `speed_att_all` = '".$_POST['speed_att_all']."', `att` = '".$_POST['att']."', `att_all` = '".$_POST['att_all']."', `radius_att` = '".$_POST['radius_att']."', `speed_hod_all` = '".$_POST['speed_hod_all']."', `loc` = '".$_POST['loc']."', `x` = '".$_POST['x']."', `y` = '".$_POST['y']."', `radius_agr` = '".$_POST['radius_agr']."';");
						
				echo '<div class="line"></div>';
				echo '<div class="text"><font color="#00FF00">Бот создан</font></div>';
				echo '<div class="line"></div>';
			}

			echo '<div class="text">';
			echo '<form method="post" action="">';
			echo 'Тип: <select name="type">';
			echo '<option value="0">пассивный</option>';
			echo '<option value="1">агрессивный</option>';
			echo '<option value="2">нпц</option>';
			echo '</select><br/>';
			echo 'Имя: <input type="text" name="name" value="" required/><br/>';
			echo 'Скин: №<input type="number" name="skin" value="0"/><br/>';
			echo 'Уровень: <input type="number" name="lvl" value="0"/><br/>';
			echo 'Дроп опыта: <input type="number" name="exp" value="0"/><br/>';
			echo 'Дроп кредитов: <input type="number" name="money" value="0"/><br/>';
			echo 'Состояние: <select name="death_on">';
			echo '<option value="0">жив</option>';
			echo '<option value="1">мёртв</option>';
			echo '</select><br/>';
			echo 'Время спауна: <input type="number" name="death_time_all" value="0"/>сек.<br/>';
			echo 'Текст(для нпц): <input type="text" name="chat" value=""/><br/>';
			echo 'Покупка вещей(для нпц): <select name="skup_luta_on">';
			echo '<option value="0">нет</option>';
			echo '<option value="1">да</option>';
			echo '</select><br/>';
			echo 'Починка вещей(для нпц): <select name="kuznec_on">';
			echo '<option value="0">нет</option>';
			echo '<option value="1">да</option>';
			echo '</select><br/>';
			echo 'Ловкость: <input type="number" name="agi" value="0"/><br/>';
			echo 'Здоровье: <input type="number" name="hp" value="0"/>/<input type="number" name="hp_all" value="0"/><br/>';
			echo 'Меткость: <input type="number" name="dex" value="0"/><br/>';
			echo 'Защита: <input type="number" name="def" value="0"/><br/>';
			echo 'Скорость атаки: <input type="number" name="speed_att_all" value="0"/>сек.<br/>';
			echo 'Урон: <input type="number" name="att" value="0"/>...<input type="number" name="att_all" value="0"/><br/>';
			echo 'Радиус атаки: <input type="number" name="radius_att" value="0"/>кл.<br/>';
			echo 'Скорость передвижения: <input type="number" name="speed_hod_all" value="0"/>сек.<br/>';
			echo 'Координаты:<br/>';
			echo 'Локация - <input type="number" name="loc" value="0"/><br/>';
			echo 'X - <input type="number" name="x" value="0"/><br/>';
			echo 'Y - <input type="number" name="y" value="0"/><br/>';
			echo 'Радиус агрессии: <input type="number" name="radius_agr" value="0"/>кл.<br/>';
			echo '<input type="submit" name="add" value="Изменить"/>';
			echo '</form>';
			echo '</div>';
			echo '<div class="line"></div>';
			echo '<div class="foot_a">';
			echo '<a href="./admin_console.php">Панель администратора</a>';
			echo '</div>';
			break;
			//добавить бота.конец

			//изменить бота
			case 'update_bot':
			$id = 0;

			if (isset($_GET['id'])) $id = $_GET['id'];
			if (isset($_POST['id'])) $id = $_POST['id'];

			echo '<div class="menu">Изменение бота</div>';
			echo '<div class="text">';
			echo '<form method="post" action="">';
			echo 'ID бота: <input type="text" name="id" value="'.$id.'"/> <input type="submit" name="search" value="Найти"/>';
			echo '</form>';
			echo '</div>';

			if (isset($_POST['id'])) {
				$bot_sql = $sql->query("SELECT * FROM `bots` WHERE `id` = '".$_POST['id']."'");
				$bot_all = $bot_sql->num_rows;

				if ($bot_all != 0) {
					$b = $bot_sql->fetch_array(MYSQLI_ASSOC);

					if (isset($_POST['update'])) {
						$sql->query("UPDATE `bots` SET `type` = '".$_POST['type']."', `name` = '".$_POST['name']."', `skin` = '".$_POST['skin']."', `lvl` = '".$_POST['lvl']."', `exp` = '".$_POST['exp']."', `money` = '".$_POST['money']."', `death_on` = '".$_POST['death_on']."', `death_time_all` = '".$_POST['death_time_all']."', `chat` = '".$_POST['chat']."', `skup_luta_on` = '".$_POST['skup_luta_on']."', `kuznec_on` = '".$_POST['kuznec_on']."', `agi` = '".$_POST['agi']."', `hp` = '".$_POST['hp']."', `hp_all` = '".$_POST['hp_all']."', `dex` = '".$_POST['dex']."', `def` = '".$_POST['def']."', `speed_att_all` = '".$_POST['speed_att_all']."', `att` = '".$_POST['att']."', `att_all` = '".$_POST['att_all']."', `radius_att` = '".$_POST['radius_att']."', `speed_hod_all` = '".$_POST['speed_hod_all']."', `loc` = '".$_POST['loc']."', `x` = '".$_POST['x']."', `y` = '".$_POST['y']."', `radius_agr` = '".$_POST['radius_agr']."' WHERE `id` = '".$b['id']."'");
						
						echo '<div class="line"></div>';
						echo '<div class="text"><font color="#00FF00">Сохранено</font></div>';
						echo '<div class="line"></div>';
					}

					echo '<div class="text">';
					echo '<form method="post" action="">';
					echo 'Тип: <select name="type" required>';
					echo '<option value="0">пассивный</option>';
					echo '<option value="1"';
					if ($b['type'] == 1) echo ' selected';
					echo '>агрессивный</option>';
					echo '<option value="2"';
					if ($b['type'] == 2) echo ' selected';
					echo '>нпц</option>';
					echo '</select><br/>';
					echo 'Имя: <input type="text" name="name" value="'.$b['name'].'"/><br/>';
					echo 'Скин: №<input type="number" name="skin" value="'.$b['skin'].'"/><br/>';
					echo 'Уровень: <input type="number" name="lvl" value="'.$b['lvl'].'"/><br/>';
					echo 'Дроп опыта: <input type="number" name="exp" value="'.$b['exp'].'"/><br/>';
					echo 'Дроп кредитов: <input type="number" name="money" value="'.$b['money'].'"/><br/>';
					echo 'Состояние: <select name="death_on">';
					echo '<option value="0">жив</option>';
					echo '<option value="1"';
					if ($b['death_on'] == 1) echo ' selected';
					echo '>мёртв</option>';
					echo '</select><br/>';
					echo 'Время спауна: <input type="number" name="death_time_all" value="'.$b['death_time_all'].'"/>сек.<br/>';
					echo 'Текст(для нпц): <input type="text" name="chat" value="'.$b['chat'].'"/><br/>';
					echo 'Покупка вещей(для нпц): <select name="skup_luta_on">';
					echo '<option value="0">нет</option>';
					echo '<option value="1"';
					if ($b['skup_luta_on'] == 1) echo ' selected';
					echo '>да</option>';
					echo '</select><br/>';
					echo 'Починка вещей(для нпц): <select name="kuznec_on">';
					echo '<option value="0">нет</option>';
					echo '<option value="1"';
					if ($b['kuznec_on'] == 1) echo ' selected';
					echo '>да</option>';
					echo '</select><br/>';
					echo 'Ловкость: <input type="number" name="agi" value="'.$b['agi'].'"/><br/>';
					echo 'Здоровье: <input type="number" name="hp" value="'.$b['hp'].'"/>/<input type="number" name="hp_all" value="'.$b['hp_all'].'"/><br/>';
					echo 'Меткость: <input type="number" name="dex" value="'.$b['dex'].'"/><br/>';
					echo 'Защита: <input type="number" name="def" value="'.$b['def'].'"/><br/>';
					echo 'Скорость атаки: <input type="number" name="speed_att_all" value="'.$b['speed_att_all'].'"/>сек.<br/>';
					echo 'Урон: <input type="number" name="att" value="'.$b['att'].'"/>...<input type="number" name="att_all" value="'.$b['att_all'].'"/><br/>';
					echo 'Радиус атаки: <input type="number" name="radius_att" value="'.$b['radius_att'].'"/>кл.<br/>';
					echo 'Скорость передвижения: <input type="number" name="speed_hod_all" value="'.$b['speed_hod_all'].'"/>сек.<br/>';
					echo 'Координаты:<br/>';
					echo 'Локация - <input type="number" name="loc" value="'.$b['loc'].'"/><br/>';
					echo 'X - <input type="number" name="x" value="'.$b['x'].'"/><br/>';
					echo 'Y - <input type="number" name="y" value="'.$b['y'].'"/><br/>';
					echo 'Радиус агрессии: <input type="number" name="radius_agr" value="'.$b['radius_agr'].'"/>кл.<br/>';
					echo '<input type="hidden" name="id" value="'.$_POST['id'].'"/>';
					echo '<input type="submit" name="update" value="Изменить"/>';
					echo '</form>';
					echo '</div>';
				} else {
					echo '<div class="text">Ничего не найдено</div>';
				}

			}

			echo '<div class="line"></div>';
			echo '<div class="foot_a">';
			echo '<a href="./admin_console.php">Панель администратора</a>';
			echo '</div>';
			break;
			//изменить бота.конец



		}

	} else { //если не админ
		header('Location: ./');
	}

} else { //если не игрок
	header('Location: ./');
}

include './foot.php';
?>