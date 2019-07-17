<?php
define('cms', 1);
require_once 'header.php';

$conf = $sql->query("SELECT * FROM `conf`")->fetch_array(MYSQLI_ASSOC);

if ($user) { //если игрок
	$u = $sql->query("SELECT * FROM `users` WHERE `id` = '".$user."'")->fetch_array(MYSQLI_ASSOC);

	switch($_GET['a']) {

		/*-----ИНФОРМАЦИЯ-----*/
		default:
		echo '<div class="menu">Персонаж</div>';
		echo '<div class="mmenu">Информация</div>';
		echo '<div class="text">';
		echo 'ID: '.$u['id'].'<br/>';
		echo 'Имя: '.$u['name'].'<br/>';
		echo 'Пол: ';

		if ($u['paul'] == 0) echo 'мужчина';
		if ($u['paul'] == 1) echo 'женщина';

		echo '<br/>';
		echo 'Уровень: '.$u['lvl'].'<br/>';

		$uEXP = $sql->query("SELECT * FROM `table_lvl` WHERE `id` = '".$u['lvl']."'")->fetch_array(MYSQLI_ASSOC);

		echo 'Опыт: '.$u['exp'].'/'.$uEXP['exp'].'<br/>';
		echo 'Дата регистрации: '.$u['date_reg'].'<br/>';
		echo '</div>';
		echo '<div class="line"></div>';
		echo '<div class="mmenu">Статистика</div>';
		echo '<div class="text">';
		echo 'Победы PvE/PvP: '.$u['PvE_win'].'/'.$u['PvP_win'].'<br/>';
		echo 'Поражения PvE/PvP: '.$u['PvE_lose'].'/'.$u['PvP_lose'].'<br/>';

		$zvanka = $sql->query("SELECT * FROM `zvanka` WHERE `PvP_win_min` <= '".$u['PvP_win']."' AND `PvP_win_max` >= '".$u['PvP_win']."'")->fetch_array(MYSQLI_ASSOC);

		echo 'Звание: '.$zvanka['title'].'<br/>';
		echo '</div>';
		echo '<div class="line"></div>';
		echo '<div class="mmenu">Параметры ';

		if ($u['stats_points'] > 0) echo '['.$u['stats_points'].']';

		echo '</div>';
		echo '<div class="text">';
		echo 'Здоровье: '.$u['hp'].'/'.$u['hp_all'];

		if (($u['hp_all'] - $u['hp_k']) > 0) echo ' <font color="#666">(+'.($u['hp_all'] - $u['hp_k']).')</font>';
		if ($u['stats_points'] > 0) echo ' <font color="#666">[</font><a style="color: #FF0000;" href="?statUp=hp">+</a><font color="#666">]</font>';

		echo '<br/>';
		echo 'Сила: '.$u['str'];

		if (($u['str'] - $u['str_k']) > 0) echo ' <font color="#666">(+'.($u['str'] - $u['str_k']).')</font>';
		if ($u['stats_points'] > 0) echo ' <font color="#666">[</font><a style="color: #FF0000;" href="?statUp=str">+</a><font color="#666">]</font>';

		echo '<br/>';
		echo 'Ловкость: '.$u['agi'];

		if (($u['agi'] - $u['agi_k']) > 0) echo ' <font color="#666">(+'.($u['agi'] - $u['agi_k']).')</font>';
		if ($u['stats_points'] > 0) echo ' <font color="#666">[</font><a style="color: #FF0000;" href="?statUp=agi">+</a><font color="#666">]</font>';

		echo '<br/>';
		echo 'Меткость: '.$u['dex'];

		if (($u['dex'] - $u['dex_k']) > 0) echo ' <font color="#666">(+'.($u['dex'] - $u['dex_k']).')</font>';
		if ($u['stats_points'] > 0) echo ' <font color="#666">[</font><a style="color: #FF0000;" href="?statUp=dex">+</a><font color="#666">]</font>';

		/*прокачка*/
		if (isset($_GET['statUp'])) {

			if ($u['stats_points'] > 0) { //если можно прокачивать

				if ($_GET['statUp'] == 'hp') { //если повышаем ХП
					$sql->query("UPDATE `users` SET `hp` = '".($u['hp'] + 10)."', `hp_all` = '".($u['hp_all'] + 10)."', `hp_k` = '".($u['hp_k'] + 10)."' WHERE `id` = '".$u['id']."'");
				} else if ($_GET['statUp'] == 'str') { //силу
					$sql->query("UPDATE `users` SET `str` = '".($u['str'] + 1)."', `str_k` = '".($u['str_k'] + 1)."', `en_all` = '".($u['en_all'] + 1)."' WHERE `id` = '".$u['id']."'");
				} else if ($_GET['statUp'] == 'agi') { //ловкость
					$sql->query("UPDATE `users` SET `agi` = '".($u['agi'] + 1)."', `agi_k` = '".($u['agi_k'] + 1)."' WHERE `id` = '".$u['id']."'");
				} else if ($_GET['statUp'] == 'dex') { //меткость
					$sql->query("UPDATE `users` SET `dex` = '".($u['dex'] + 1)."', `dex_k` = '".($u['dex_k'] + 1)."' WHERE `id` = '".$u['id']."'");
				}

			}

			$sql->query("UPDATE `users` SET `stats_points` = '".($u['stats_points'] - 1)."' WHERE `id` = '".$u['id']."'");
			header('Location: ./char.php');
		}

		/*прокачка.конец*/

		echo '<br/>';
		echo 'Защита: '.$u['def'].'<br/>';
		echo 'Скорость передвижения: '.$u['speed_hod_all'].' сек<br/>';
		echo '</div>';
		echo '<div class="line"></div>';
		echo '<ul class="links">';
		echo '<li><a href="?a=equip">Снаряжение</a></li>';
		echo '<li><a href="?a=bag">Сумка</a></li>';
		echo '<li><a href="?a=pda">PDA</a></li>';
		echo '<li><a href="?a=naw">Навыки</a></li>';
		echo '</ul>';
		echo '<div class="line"></div>';
		echo '<div class="foot_a">';
		echo '<a href="./">Закрыть</a>';
		echo '</div>';
		break;
		/*-----ИНФОРМАЦИЯ.КОНЕЦ-----*/

		/*-----НАВЫКИ-----*/
		case 'naw':
		$nawAll = $sql->query("SELECT * FROM `users_naw` WHERE `user` = '".$u['id']."'")->num_rows;

		echo '<div class="menu">Навыки</div>';
		echo '<div class="text">';
		echo 'Изучено навыков '.$nawAll.'/'.$u['slots_naw'];
		echo '</div>';
		echo '<div class="line"></div>';

		if ($nawAll != 0) {

			$nawSQL = $sql->query("SELECT * FROM `users_naw` WHERE `user` = '".$user."' ORDER BY `id` ASC");

			echo '<ul class="links">';

			while ($ni = $nawSQL->fetch_array(MYSQLI_ASSOC)) {
				echo '<li><a href="?a=naw_inf&id='.$ni['id'].'">'.$ni['title'].' ['.$ni['lvl'].']</a></li>';
			}

			echo '</ul>';

		} else {
			echo '<div class="text">Навыков нет</div>';
		}

		echo '<div class="line"></div>';
		echo '<div class="foot_a">';
		echo '<a href="./">Закрыть</a>';
		echo '</div>';
		break;

		/*-----ИНФОРМАЦИЯ О НАВЫКЕ-----*/
		case 'naw_inf':
		echo '<div class="menu">Навык</div>';

		$nSQL = $sql->query("SELECT * FROM `users_naw` WHERE `id` = '".$_GET['id']."' AND `user` = '".$user."'");
		$nAll = $nSQL->num_rows;

		if ($nAll != 0) {
			$ni = $nSQL->fetch_array(MYSQLI_ASSOC);

			echo '<div class="text">';
			echo '<table border="0" cellpadding="0" cellspacing="0" style="padding: 0; margin: 0;" width="100%">';
			echo '<tr>';
			echo '<td width="50%">Название:</td>';
			echo '<td width="50%">'.$ni['title'].'</td>';
			echo '</tr>';

			echo '<tr>';
			echo '<td width="50%">Уровень:</td>';
			echo '<td width="50%">'.$ni['lvl'].'</td>';
			echo '</tr>';

			$nEXP = $sql->query("SELECT * FROM `table_lvl_naw` WHERE `id` = '".$ni['lvl']."'")->fetch_array(MYSQLI_ASSOC);

			echo '<tr>';
			echo '<td width="50%">Опыт:</td>';
			echo '<td width="50%">'.round($ni['exp'], 2).'/'.$nEXP['exp'].'</td>';
			echo '</tr>';

			echo '<tr>';
			echo '<td width="50%">Описание:</td>';
			echo '<td width="50%">'.$ni['opisanie'].'</td>';
			echo '</tr>';

			echo '</table>';
			echo '</div>';

			echo '<div class="line"></div>';
			echo '<ul class="links">';
			echo '<li><a href="?a=naw_delete&id='.$ni['id'].'">Забыть</a></li>';
			echo '</ul>';

		} else {
			echo '<div class="text">Навык не найден</div>';
		}

		echo '<div class="line"></div>';
		echo '<ul class="links">';
		echo '<li><a href="?a=naw">Навыки</a></li>';
		echo '</ul>';
		echo '<div class="line"></div>';
		echo '<div class="foot_a">';
		echo '<a href="./">Закрыть</a>';
		echo '</div>';
		break;

		//забывание навыка
		case 'naw_delete':
		echo '<div class="menu">Навык</div>';

		$nSQL = $sql->query("SELECT * FROM `users_naw` WHERE `id` = '".$_GET['id']."' AND `user` = '".$user."'");
		$nAll = $nSQL->num_rows;

		if ($nAll != 0) {
			$ni = $nSQL->fetch_array(MYSQLI_ASSOC);
			//удаляем навык
			$sql->query("DELETE FROM `users_naw` WHERE `id` = '".$ni['id']."'");
			//создаём лог
			$sql->query("INSERT INTO `users_logi` SET `user` = '".$u['id']."', `text` = '<font color=\"#0000FF\">Навык [".$ni['title']."] забыт</font>', `dtime` = '".date("H:i")."'");
			//кидаем назад
			header('Location: ?a=naw');

		} else {
			echo '<div class="text">Навык не найден</div>';
		}

		echo '<div class="line"></div>';
		echo '<ul class="links">';
		echo '<li><a href="?a=naw">Навыки</a></li>';
		echo '</ul>';
		echo '<div class="line"></div>';
		echo '<div class="foot_a">';
		echo '<a href="./">Закрыть</a>';
		echo '</div>';	

		break;
		/*-----НАВЫКИ.КОНЕЦ-----*/

		/*-----СНАРЯЖЕНИЕ-----*/
		case 'equip':
		echo '<div class="menu">Снаряжение</div>';

		if ($u['equip_armor'] != 0) {
			$ea = $sql->query("SELECT * FROM `users_bag` WHERE `id` = '".$u['equip_armor']."'")->fetch_array(MYSQLI_ASSOC);
			$eaTitle = '<a href="?a=bag_inf&id='.$ea['id'].'">'.$ea['title'].'</a> ['.$ea['lvl'].']';
		} else {
			$eaTitle = 'Слот пуст';
		}

		if ($u['equip_jilet'] != 0) {
			$ej = $sql->query("SELECT * FROM `users_bag` WHERE `id` = '".$u['equip_jilet']."'")->fetch_array(MYSQLI_ASSOC);
			$ejTitle = '<a href="?a=bag_inf&id='.$ej['id'].'">'.$ej['title'].'</a> ['.$ej['lvl'].']';
		} else {
			$ejTitle = 'Слот пуст';
		}

		if ($u['equip_weapon_r'] != 0) {
			$ewr = $sql->query("SELECT * FROM `users_bag` WHERE `id` = '".$u['equip_weapon_r']."'")->fetch_array(MYSQLI_ASSOC);

			if ($u['equip_weapon_r'] != $u['equip_weapon_l']) {
				$ewrTitle = '<a href="?a=bag_inf&id='.$ewr['id'].'">'.$ewr['title'].'</a> ['.$ewr['lvl'].']';
			} else {
				$ewTitle = '<a href="?a=bag_inf&id='.$ewr['id'].'">'.$ewr['title'].'</a> ['.$ewr['lvl'].']';
			}

		} else {
			$ewrTitle = 'Слот пуст';
		}

		if ($u['equip_weapon_l'] > 0 && $u['equip_weapon_l'] != $u['equip_weapon_r']) {
			$ewl = $sql->query("SELECT * FROM `users_bag` WHERE `id` = '".$u['equip_weapon_l']."'")->fetch_array(MYSQLI_ASSOC);
			$ewlTitle = '<a href="?a=bag_inf&id='.$ewl['id'].'">'.$ewl['title'].'</a> ['.$ewl['lvl'].']';
		} else {
			$ewlTitle = 'Слот пуст';
		}

		echo '<div class="text">Броня: '.$eaTitle.'</div>';
		echo '<div class="text">Жилет: '.$ejTitle.'</div>';

		if (isset($ewTitle)) {
			echo '<div class="text">Оружие: '.$ewTitle.'</div>';
		} else {
			echo '<div class="text">Правая рука: '.$ewrTitle.'</div>';
			echo '<div class="text">Левая рука: '.$ewlTitle.'</div>';
		}

		echo '<div class="line"></div>';
		echo '<ul class="links">';
		echo '<li><a href="?a=bag">Сумка</a></li>';
		echo '</ul>';
		echo '<div class="line"></div>';
		echo '<div class="foot_a">';
		echo '<a href="./">Закрыть</a>';
		echo '</div>';
		break;
		/*-----СНАРЯЖЕНИЕ.КОНЕЦ-----*/

		/*-----СУМКА-----*/
		case 'bag':
		echo '<div class="menu">Сумка</div>';
		echo '<div class="text">';

		//переносимый вес
		$perenos_ves = 0; //пока 0
		$perenos_ves_all = round(($u['ves_all'] + ($u['str'] * 0.250)), 3);
		//petenos_shmot_ves/ считываем вес
		//смотрим кол-во вещей в рюкзаке
		$lut_bag_sql = $sql->query("SELECT * FROM `users_bag` WHERE `user` = '".$u['id']."'");
		$lut_bag_all = $lut_bag_sql->num_rows;

		if ($lut_bag_all != 0) { //если есть

			while ($psv = $lut_bag_sql->fetch_array(MYSQLI_ASSOC)) {
				if ($psv['kol_vo_all'] == 0) {
					$perenos_ves += $psv['ves'];
				} else {
					$perenos_ves += $psv['ves'] * $psv['kol_vo'];
				}

				if ($psv['patron'] > 0) { //если заряжены патроны
					//смотрим вес патронов
					$ves_patron = $sql->query("SELECT * FROM `shmots` WHERE `type` = '11' AND `title` = '".$psv['kalibr']."'")->fetch_array(MYSQLI_ASSOC);
					//добавляем
					$perenos_ves += $ves_patron['ves'] * $psv['patron'];
				}

			}

		}

		echo 'Переносимый вес: '.$perenos_ves.'/'.$perenos_ves_all.' кг<br/>';
		echo '</div>';
		echo '<div class="line"></div>';
		echo '<div class="mmenu">Содержимое</div>';

		$lutBagAll = $sql->query("SELECT * FROM `users_bag` WHERE `user` = '".$u['id']."' AND `ek` = '0'")->num_rows;

		if ($lutBagAll != 0) {

			if (isset($_GET['str'])) {
				$str = $_GET['str'] * 10;
			} else {
				$str = 0;
			}

			$str2 = $str + 10;
			$lutBagSQL = $sql->query("SELECT * FROM `users_bag` WHERE `user` = '".$u['id']."' AND `ek` = '0' ORDER BY `id` DESC LIMIT ".$str.", ".$str2."");

			echo '<ul class="links">';

			while ($li = $lutBagSQL->fetch_array(MYSQLI_ASSOC)) {
				echo '<li><a href="?a=bag_inf&id='.$li['id'].'">'.$li['title'].' <font color="#666">['.$li['lvl'].' ур]';

				if ($li['kol_vo_all'] == 1) echo ' [x'.$li['kol_vo'].']';

				echo '</font></a></li>';
			}

			echo '</ul>';
			echo '<div class="line"></div>';

			$strAll = $lutBagAll / 10;

			echo '<div class="text">';

			for ($s = 0; $s <= $strAll; $s++) {
				echo '<font color="#666">[<a href="?a=bag&str='.$s.'">'.($s + 1).'</a>]</font>';
			}

			echo '</div>';

		} else {
			echo '<div class="text">Ничего нет</div>';
		}


		echo '<div class="line"></div>';
		echo '<div class="foot_a">';
		echo '<a href="./">Закрыть</a>';
		echo '</div>';
		break;

		//информация о предмете
		case 'bag_inf':
		echo '<div class="menu">Предмет</div>';

		$lSQL = $sql->query("SELECT * FROM `users_bag` WHERE `user` = '".$u['id']."' AND `id` = '".$_GET['id']."'");
		$lAll = $lSQL->num_rows;

		if ($lAll != 0) {
			$li = $lSQL->fetch_array(MYSQLI_ASSOC);

			echo '<div class="text">';
			echo '<img src="./img/shmots/'.$li['type'].'/'.$li['title'].'.png" width="50%"><br/>';
			echo '<table border="0" cellpadding="0" cellspacing="0" style="padding: 0; margin: 0;" width="100%">';

			echo '<tr>';
			echo '<td width="50%">Название:</td>';
			echo '<td width="50%">'.$li['title'].'</td>';
			echo '</tr>';

			if ($li['pr_all'] > 0) {
				echo '<tr>';
				echo '<td width="50%">Прочность:</td>';
				echo '<td width="50%">'.$li['pr'].'/'.$li['pr_all'].'</td>';
				echo '</tr>';
			}

			echo '<tr>';
			echo '<td width="50%">Масса:</td>';
			echo '<td width="50%">'.round($li['ves'], 3).' кг</td>';
			echo '</tr>';

			echo '<tr>';
			echo '<td width="50%">Тип:</td>';
			echo '<td width="50%">';

			if ($li['type'] == 1) echo 'Броня';
			if ($li['type'] == 2) echo 'Жилет';
			if ($li['type'] == 3) echo 'Холодное оружие';
			if ($li['type'] == 4) echo 'Метательное оружие';
			if ($li['type'] == 5) echo 'Пистолет';
			if ($li['type'] == 6) echo 'Пистолет-пулемёт';
			if ($li['type'] == 7) echo 'Пулемёт';
			if ($li['type'] == 8) echo 'Дробовик';
			if ($li['type'] == 9) echo 'Винтовка';
			if ($li['type'] == 10) echo 'Автомат';
			if ($li['type'] == 11) echo 'Боеприпас';
			if ($li['type'] == 12) echo 'Медикамент';
			if ($li['type'] == 13) echo 'Материал';
			if ($li['type'] == 14) echo 'Инструмент';
			if ($li['type'] == 15) echo 'Схема';
			if ($li['type'] == 16) echo 'Предмет';

			echo '</td>';
			echo '</tr>';

			if (!empty($li['t_naw'])) {
				echo '<tr>';
				echo '<td width="50%">Навык[уровень]</td>';
				echo '<td width="50%">'.$li['t_naw'].'['.$li['t_naw_lvl'].']</td>';
				echo '</tr>';
			}

			if ($li['att_all'] > 0) {
				echo '<tr>';
				echo '<td width="50%">Урон:</td>';
				echo '<td width="50%">'.$li['att'].'...'.$li['att_all'].'</td>';
				echo '</tr>';
			}

			if ($li['type'] >= 3 && $li['type'] <= 10) {
				echo '<tr>';
				echo '<td width="50%">Скорость атаки:</td>';
				echo '<td width="50%">'.$li['speed_att_all'].' сек</td>';
				echo '</tr>';

			
				echo '<tr>';
				echo '<td width="50%">Дальность:</td>';
				echo '<td width="50%">'.$li['radius_att'].' кл.</td>';
				echo '</tr>';

				echo '<tr>';
				echo '<td width="50%">Режим атаки:</td>';
				echo '<td width="50%">';

				if ($li['type'] != 3) {
					echo 'Одиночный';

					if ($li['rej_str_all'] == 2) echo '<br/>Очередь(3)';

				} else {
					echo 'Удар';
				}

				echo '</td>';
				echo '</tr>';

				if ($li['type'] != 3 && $li['type'] != 4) {
					echo '<tr>';
					echo '<td width="50%">Обойма:</td>';
					echo '<td width="50%">'.$li['patron_all'].'</td>';
					echo '</tr>';

					echo '<tr>';
					echo '<td width="50%">Калибр:</td>';
					echo '<td width="50%">'.$li['kalibr'].'</td>';
					echo '</tr>';
				}

			}

			if ($li['def'] > 0) {
				echo '<tr>';
				echo '<td width="50%">Защита:</td>';
				echo '<td width="50%">+'.$li['def'].'</td>';
				echo '</tr>';
			}

			if ($li['str'] > 0) {
				echo '<tr>';
				echo '<td width="50%">Сила:</td>';
				echo '<td width="50%">+'.$li['str'].'</td>';
				echo '</tr>';
			}

			if ($li['agi'] > 0) {
				echo '<tr>';
				echo '<td width="50%">Ловкость:</td>';
				echo '<td width="50%">+'.$li['agi'].'</td>';
				echo '</tr>';
			}

			if ($li['dex'] > 0) {
				echo '<tr>';
				echo '<td width="50%">Меткость:</td>';
				echo '<td width="50%">+'.$li['dex'].'</td>';
				echo '</tr>';
			}

			if ($li['hp_all'] > 0 || $li['hp'] > 0) {
				echo '<tr>';
				echo '<td width="50%">Здоровье:</td>';
				echo '<td width="50%">+'.$li['hp'].'/+'.$li['hp_all'].'</td>';
				echo '</tr>';
			}

			if ($li['en_all'] > 0 || $li['en'] > 0) {
				echo '<tr>';
				echo '<td width="50%">Энергия:</td>';
				echo '<td width="50%">+'.$li['en'].'/+'.$li['en_all'].'</td>';
				echo '</tr>';
			}

			if ($li['speed_hod_all'] > 0) {
				echo '<tr>';
				echo '<td width="50%">Скорость передвижения:</td>';
				echo '<td width="50%">+'.$li['speed_hod_all'].'</td>';
				echo '</tr>';
			}

			if ($li['kol_vo_all'] == 1) {
				echo '<tr>';
				echo '<td width="50%">Количество:</td>';
				echo '<td width="50%">'.$li['kol_vo'].'</td>';
				echo '</tr>';
			}

			if ($li['type'] == 2 && $li['vmest']) {
				echo '<tr>';
				echo '<td width="50%">Вместимость:</td>';
				echo '<td width="50%">'.$li['vmest'].'</td>';
				echo '</tr>';
			}

			echo '</table>';
			echo '</div>';
			echo '<div class="line"></div>';
			echo '<ul class="links">';

			if ($li['ek'] == 0) {

				if ($li['type'] <= 10) echo '<li><a href="?a=bag_isp&id='.$li['id'].'&equip">Надеть</a></li>';
				if ($li['type'] == 12 || $li['type'] == 15) echo '<li><a href="?a=bag_isp&id='.$li['id'].'&isp">Использовать</a></li>';

				echo '<li><a href="?a=bag_isp&id='.$li['id'].'&delete">Выбросить</a></li>';

			} else {
				echo '<li><a href="?a=bag_isp&id='.$li['id'].'&equip">Снять</a></li>';
				echo '<li><a href="?a=equip">Снаряжение</a></li>';
			}

			echo '</ul>';

		} else {
			echo '<div class="text">Предмет не найден</div>';
		}


		echo '<div class="line"></div>';
		echo '<ul class="links">';
		echo '<li><a href="?a=bag">Сумка</a></li>';
		echo '</ul>';
		echo '<div class="line"></div>';
		echo '<div class="foot_a">';
		echo '<a href="./">Закрыть</a>';
		echo '</div>';
		break;

		/*-----ДЕЙСТИЯ С ВЕЩАМИ-----*/
		case 'bag_isp':
		echo '<div class="menu">Предмет</div>';

		$lSQL = $sql->query("SELECT * FROM `users_bag` WHERE `user` = '".$u['id']."' AND `id` = '".$_GET['id']."'");
		$lAll = $lSQL->num_rows;

		if ($lAll != 0) {
			$li = $lSQL->fetch_array(MYSQLI_ASSOC);
			//проверям, есть ли требуемый навык
			$nawAll = $sql->query("SELECT * FROM `users_naw` WHERE `user` = '".$u['id']."' AND `title` = '".$li['t_naw']."' AND `lvl` >= '".$li['t_naw_lvl']."'")->num_rows;

			if ($li['ek'] == 0) { //если не экипированно

				//если нажато "снарядить"
				if (isset($_GET['equip'])) {

					if ($li['type'] <= 10) { //если экипировать можно

						if ($li['lvl'] <= $u['lvl']) { //ели подходит по левлу

							if ($li['type'] >= 3) { //если выбранно оружие

								if ($nawAll != 0) { //если требуемый навык есть

									if ($li['ruki'] == 1) { //если 1 ручное

										if (isset($_POST['equip'])) { //если нажата кнопка "снарядить"

											if ($_POST['ruka'] == 1) { //если в правую
												$ruka = $u['equip_weapon_r'];
												$rukaName = 'equip_weapon_r';
												$nEk = 1;
											} else { //если в левую
												$ruka = $u['equip_weapon_l'];
												$rukaName = 'equip_weapon_l';
												$nEk = 2;
											}

											if ($ruka == 0) { //если рука пуста

												//экипируем и добавляем статы
												$sql->query("UPDATE `users` SET `".$rukaName."` = '".$li['id']."', `def` = '".($u['def'] + $li['def'])."', `str` = '".($u['str'] + $li['str'])."', `agi` = '".($u['agi'] + $li['agi'])."', `dex` = '".($u['dex'] + $li['dex'])."', `hp_all` = '".($u['hp_all'] + $li['hp_all'])."', `en_all` = '".($u['en_all'] + $li['en_all'])."', `speed_hod_all` = '".($u['speed_hod_all'] + $li['speed_hod_all'])."' WHERE `id` = '".$u['id']."'");
												$sql->query("UPDATE `users_bag` SET `ek` = '".$nEk."' WHERE `id` = '".$li['id']."'"); //отмечаем это для рюкзака
												$sql->query("INSERT INTO `users_logi` SET `user` = '".$u['id']."', `text` = 'Предмет [".$li['title']."] был экипирован', `dtime` = '".date("H:i")."'"); //создаём лог

												header('Location: ?a=bag'); //направляем в сумку
											} else {
												echo '<div class="text"><font color="#FF0000">&times; Сперва снимите то что на вас одето!</font></div>';
											}

										}

										echo '<div class="mmenu">Снаряжение</div>';
										echo '<div class="text">';
										echo '<form method="post" action="">';
										echo 'Снарядить в ';
										echo '<select name="ruka">';
										echo '<option value="1">правую руку</option>';
										echo '<option value="2">левую руку</option>';
										echo '</select><br/>';
										echo '<input type="submit" name="equip" value="Снарядить"/>';
										echo '</form>';
										echo '</div>';

									} else { //если 2 ручное

										if ($u['equip_weapon_r'] == 0 && $u['equip_weapon_l'] == 0) {

											//экипируем и добавляем статы
											$sql->query("UPDATE `users` SET `equip_weapon_r` = '".$li['id']."', `equip_weapon_l` = '".$li['id']."', `def` = '".($u['def'] + $li['def'])."', `str` = '".($u['str'] + $li['str'])."', `agi` = '".($u['agi'] + $li['agi'])."', `dex` = '".($u['dex'] + $li['dex'])."', `hp_all` = '".($u['hp_all'] + $li['hp_all'])."', `en_all` = '".($u['en_all'] + $li['en_all'])."', `speed_hod_all` = '".($u['speed_hod_all'] + $li['speed_hod_all'])."' WHERE `id` = '".$u['id']."'");
											$sql->query("UPDATE `users_bag` SET `ek` = '3' WHERE `id` = '".$li['id']."'"); //отмечаем это для рюкзака
											$sql->query("INSERT INTO `users_logi` SET `user` = '".$u['id']."', `text` = 'Предмет [".$li['title']."] был экипирован', `dtime` = '".date("H:i")."'"); //создаём лог

											header('Location: ?a=bag'); //направляем в сумку
										} else {
											echo '<div class="text"><font color="#FF0000">&times; Сперва снимите то что на вас одето!</font></div>';
										}

									}

								} else {
									echo '<div class="text">Вы не можете снарядить это</div>';
								}

							} else { //если броня

								if ($li['type'] == 1) { //если броня
									$emptySl = $u['equip_armor'];
									$slot = 'equip_armor';
									$nEk = 1;
								} else { //если жилет
									$emptySl = $u['equip_jilet'];
									$slot = 'equip_jilet';
									$nEk = 2;
								}

								if ($emptySl == 0) { //если слот пуст

									//экипируем и добавляем статы
									$sql->query("UPDATE `users` SET `".$slot."` = '".$li['id']."', `def` = '".($u['def'] + $li['def'])."', `str` = '".($u['str'] + $li['str'])."', `agi` = '".($u['agi'] + $li['agi'])."', `dex` = '".($u['dex'] + $li['dex'])."', `hp_all` = '".($u['hp_all'] + $li['hp_all'])."', `en_all` = '".($u['en_all'] + $li['en_all'])."', `speed_hod_all` = '".($u['speed_hod_all'] + $li['speed_hod_all'])."' WHERE `id` = '".$u['id']."'");
									$sql->query("UPDATE `users_bag` SET `ek` = '".$nEk."' WHERE `id` = '".$li['id']."'"); //отмечаем это для рюкзака
									$sql->query("INSERT INTO `users_logi` SET `user` = '".$u['id']."', `text` = 'Предмет [".$li['title']."] был экипирован', `dtime` = '".date("H:i")."'"); //создаём лог

									header('Location: ?a=bag'); //направляем в сумку
								} else { //если что-то уже одето
									echo '<div class="text"><font color="#FF0000">&times; Сперва снимите то что на вас одето!</font></div>';
								}


							}

						} else { //если не подходит по левлу
							echo '<div class="text">Вы не можете снарядить это</div>';
						}


					} else { //если экипировать нельзя
						echo '<div class="text">Это нельзя одеть</div>';
					}

				} else if(isset($_GET['delete'])) {  //снаряжение.конец //выбросить.начало

					if ($li['kol_vo_all'] == 0) { //если предмет не суммируется

						//добавляем вещь на локацию
						$sql->query("INSERT INTO `locations_shmots` SET `lvl` = '".$li['lvl']."', `title` = '".$li['title']."', `type` = '".$li['type']."', `t_naw` = '".$li['t_naw']."', `t_naw_lvl` = '".$li['lvl']."', `speed_att` = '".$li['speed_att']."', `speed_att_all` = '".$li['speed_att_all']."', `kalibr` = '".$li['kalibr']."', `patron` = '".$li['patron']."', `patron_all` = '".$li['patron_all']."', `att` = '".$li['att']."', `att_all` = '".$li['att_all']."', `rej_str` = '".$li['rej_str']."', `rej_str_all` = '".$li['rej_str_all']."', `radius_att` = '".$li['radius_att']."', `pr` = '".$li['pr']."', `pr_all` = '".$li['pr_all']."', `ves` = '".$li['ves']."', `def` = '".$li['def']."', `str` = '".$li['str']."', `agi` = '".$li['agi']."', `dex` = '".$li['dex']."', `hp` = '".$li['hp']."', `hp_all` = '".$li['hp_all']."', `en` = '".$li['en']."', `en_all` = '".$li['en_all']."', `speed_hod_all` = '".$li['speed_hod_all']."', `ruki` = '".$li['ruki']."', `kol_vo` = '".$li['kol_vo']."', `kol_vo_all` = '".$li['kol_vo_all']."', `vmest` = '".$li['vmest']."', `cost` = '".$li['cost']."', `k_stat` = '".$li['k_stat']."', `loc` = '".$u['loc']."', `x` = '".$u['x']."', `y` = '".$u['y']."';");
						//удаляем из рюкзака
						$sql->query("DELETE FROM `users_bag` WHERE `id` = '".$li['id']."' LIMIT 1");
						//создаём лог
						$sql->query("INSERT INTO `users_logi` SET `user` = '".$u['id']."', `text` = 'Предмет [".$li['title']."] был выброшен', `dtime` = '".date("H:i")."'");
						
						header('Location: ?a=bag'); //направляем в сумку
					} else { //если суммируется

						if (isset($_POST['delete'])) {

							if ($_POST['kol_vo'] > 0) {
								$locShmotCopSQL = $sql->query("SELECT * FROM `locations_shmots` WHERE `loc` = '".$u['loc']."' AND `x` = '".$u['x']."' AND `y` = '".$u['y']."' AND `title` = '".$li['title']."' AND `type` = '".$li['type']."'");
								$locShmotCopAll = $locShmotCopSQL->num_rows;

								if ($_POST['kol_vo'] < $li['kol_vo']) {
									$kol_vo = $_POST['kol_vo'];
									$sql->query("UPDATE `users_bag` SET `kol_vo` = '".($li['kol_vo'] - $_POST['kol_vo'])."' WHERE `id` = '".$li['id']."'");
								} else {
									$kol_vo = $li['kol_vo'];
									$sql->query("DELETE FROM `users_bag` WHERE `id` = '".$li['id']."' LIMIT 1");
								}

								//помещаем предмет на локу
								if ($locShmotCopAll == 0) { //если на локе нет копии предмета
									$sql->query("INSERT INTO `locations_shmots` SET `lvl` = '".$li['lvl']."', `title` = '".$li['title']."', `type` = '".$li['type']."', `t_naw` = '".$li['t_naw']."', `t_naw_lvl` = '".$li['lvl']."', `speed_att` = '".$li['speed_att']."', `speed_att_all` = '".$li['speed_att_all']."', `kalibr` = '".$li['kalibr']."', `patron` = '".$li['patron']."', `patron_all` = '".$li['patron_all']."', `att` = '".$li['att']."', `att_all` = '".$li['att_all']."', `rej_str` = '".$li['rej_str']."', `rej_str_all` = '".$li['rej_str_all']."', `radius_att` = '".$li['radius_att']."', `pr` = '".$li['pr']."', `pr_all` = '".$li['pr_all']."', `ves` = '".$li['ves']."', `def` = '".$li['def']."', `str` = '".$li['str']."', `agi` = '".$li['agi']."', `dex` = '".$li['dex']."', `hp` = '".$li['hp']."', `hp_all` = '".$li['hp_all']."', `en` = '".$li['en']."', `en_all` = '".$li['en_all']."', `speed_hod_all` = '".$li['speed_hod_all']."', `ruki` = '".$li['ruki']."', `kol_vo` = '".$kol_vo."', `kol_vo_all` = '".$li['kol_vo_all']."', `vmest` = '".$li['vmest']."', `cost` = '".$li['cost']."', `k_stat` = '".$li['k_stat']."', `loc` = '".$u['loc']."', `x` = '".$u['x']."', `y` = '".$u['y']."';");
								} else { //если есть
									$locShmotCop = $locShmotCopSQL->fetch_array(MYSQLI_ASSOC);
									$sql->query("UPDATE `locations_shmots` SET `kol_vo` = '".($locShmotCop['kol_vo'] + $kol_vo)."' WHERE `id` = '".$locShmotCop['id']."'");
								}

								//создаём лог
								$sql->query("INSERT INTO `users_logi` SET `user` = '".$u['id']."', `text` = 'Предмет [".$li['title']."] [x".$kol_vo."] был выброшен', `dtime` = '".date("H:i")."'");

							}

							header('Location: ?a=bag'); //направляем в сумку
						}

						echo '<div class="mmenu">Выброс</div>';
						echo '<div class="text">';
						echo '<form method="post" action="">';
						echo 'Сколько выбросить?<br/>';
						echo '<input type="number" style="width: 50px;" name="kol_vo" value="'.$li['kol_vo'].'"/>';
						echo '<input type="submit" name="delete" value="Выбросить"/>';
						echo '</form>';
						echo '</div>';
					}



				} else if (isset($_GET['isp'])) { //выбросить.конец | //использолвать начало
					//если предмет можно использовать
					if ($li['type'] == 12 || $li['type'] == 15) {
						//если медикаменты
						if ($li['type'] == 12) {
							//если подходит по уровню
							if ($li['lvl'] <= $u['lvl']) {
								//используем
								$hp_plus = $u['hp'] + $li['hp'];
								$en_plus = $u['en'] + $li['en'];
								if ($hp_plus > $u['hp_all']) $hp_plus = $u['hp_all'];
								if ($en_plus > $u['en_all']) $en_plus = $u['en_all'];

								$sql->query("UPDATE `users` SET `hp` = '".$hp_plus."', `en` = '".$en_plus."' WHERE `id` = '".$u['id']."'");
								//создаём логи
								$sql->query("INSERT INTO `users_logi` SET `user` = '".$u['id']."', `text` = 'Вы использовали [".$li['title']."]', `dtime` = '".date("H:i")."'");
								if ($li['hp'] != 0) $sql->query("INSERT INTO `users_logi` SET `user` = '".$u['id']."', `text` = '<font color=\"#00FF00\">Здоровье +".$li['hp']."</font>', `dtime` = '".date("H:i")."'");
								if ($li['en'] != 0) $sql->query("INSERT INTO `users_logi` SET `user` = '".$u['id']."', `text` = '<font color=\"#00FF00\">Энергия +".$li['en']."</font>', `dtime` = '".date("H:i")."'");
									
								//отнимаем кол-во медикаментов или удаляем вообще
								if ($li['kol_vo_all'] != 0) { //если предмет суммируется

									if ($li['kol_vo'] > 1) {
										$sql->query("UPDATE `users_bag` SET `kol_vo` = '".($li['kol_vo'] - 1)."' WHERE `id` = '".$li['id']."'");
									} else {
										$sql->query("DELETE FROM `users_bag` WHERE `id` = '".$li['id']."' LIMIT 1");
									}

								} else {
									$sql->query("DELETE FROM `users_bag` WHERE `id` = '".$li['id']."' LIMIT 1");
								}

								//кидаем в рюкзак
								header('Location: ?a=bag');

							} else { //если не подходит по уровню
								echo '<div class="text">Вы не можете использовать это</div>';
							}

						}

					} else {
						echo '<div class="text">Это нельзя использовать</div>';
					}


				} //спользовать.конец

			} else { //если экипированно

				if (isset($_GET['equip'])) { //если нажато "снять"

					if ($li['type'] >= 3) { //если оружие

						if ($li['ek'] == 1) { //если оружие в правой
							$unEkR = 0;
							$unEkL = $u['equip_weapon_l'];
						} else if ($li['ek'] == 2) { //если в левой
							$unEkR = $u['equip_weapon_r'];
							$unEkL = 0;
						} else { //если двуручное
							$unEkR = 0;
							$unEkL = 0;
						}

						//снимаем и уменьшаем статы
						$sql->query("UPDATE `users` SET `equip_weapon_r` = '".$unEkR."', `equip_weapon_l` = '".$unEkL."', `def` = '".($u['def'] - $li['def'])."', `str` = '".($u['str'] - $li['str'])."', `agi` = '".($u['agi'] - $li['agi'])."', `dex` = '".($u['dex'] - $li['dex'])."', `hp_all` = '".($u['hp_all'] - $li['hp_all'])."', `en_all` = '".($u['en_all'] - $li['en_all'])."', `speed_hod_all` = '".($u['speed_hod_all'] - $li['speed_hod_all'])."' WHERE `id` = '".$u['id']."'");
						$sql->query("UPDATE `users_bag` SET `ek` = '0' WHERE `id` = '".$li['id']."'"); //отмечаем это для рюкзака
						$sql->query("INSERT INTO `users_logi` SET `user` = '".$u['id']."', `text` = 'Предмет [".$li['title']."] был снят', `dtime` = '".date("H:i")."'"); //создаём лог

						header('Location: ?a=bag'); //направляем в сумку

					} else { //если броня

						if ($li['ek'] == 1) { //если броня
							$unEkA = 0;
							$unEkJ = $u['equip_jilet'];
						} else { //если жилет
							$unEkA = $u['equip_armor'];
							$unEkJ = 0;
						}

						//снимаем и уменьшаем статы
						$sql->query("UPDATE `users` SET `equip_armor` = '".$unEkA."', `equip_jilet` = '".$unEkJ."', `def` = '".($u['def'] - $li['def'])."', `str` = '".($u['str'] - $li['str'])."', `agi` = '".($u['agi'] - $li['agi'])."', `dex` = '".($u['dex'] - $li['dex'])."', `hp_all` = '".($u['hp_all'] - $li['hp_all'])."', `en_all` = '".($u['en_all'] - $li['en_all'])."', `speed_hod_all` = '".($u['speed_hod_all'] - $li['speed_hod_all'])."' WHERE `id` = '".$u['id']."'");
						$sql->query("UPDATE `users_bag` SET `ek` = '0' WHERE `id` = '".$li['id']."'"); //отмечаем это для рюкзака
						$sql->query("INSERT INTO `users_logi` SET `user` = '".$u['id']."', `text` = 'Предмет [".$li['title']."] был снят', `dtime` = '".date("H:i")."'"); //создаём лог

						header('Location: ?a=bag'); //направляем в сумку
					}

				}
				//снятие.конец

			}


		} else {
			echo '<div class="text">Предмет не найден</div>';
		}

		echo '<div class="line"></div>';
		echo '<ul class="links">';

		if ($lAll != 0) echo '<li><a href="?a=bag_inf&id='.$_GET['id'].'">Вернуться</li>';

		echo '<li><a href="?a=bag">Сумка</a></li>';
		echo '</ul>';
		echo '<div class="line"></div>';
		echo '<div class="foot_a">';
		echo '<a href="./">Закрыть</a>';
		echo '</div>';
		break;
		/*-----СУМКА.КОНЕЦ-----*/

		/*-----PDA-----*/
		case 'pda':
		echo '<div class="menu">PDA</div>';
		echo '<ul class="links">';

		$admin = $sql->query("SELECT * FROM `admins` WHERE `uid` = '".$u['id']."'")->num_rows;

		if ($admin != 0) echo '<li><a href="./admin_console.php">Панель администратора</a></li>';

		$new_mail_all = $sql->query("SELECT * FROM `users_mail` WHERE `reed` = '0' AND `user` = '".$u['name']."'")->num_rows;

		echo '<li><a href="?a=mail">Почта ['.$new_mail_all.']</a></li>';
		echo '<li><a href="?a=quest">Задания</a></li>';
		echo '<li><a href="?a=setting">Настройки</a></li>';

		$online_us = $sql->query("SELECT * FROM `users` WHERE `online` = '1'")->num_rows;

		echo '<li><a href="?a=us_online">Онлайн ['.$online_us.']</a></li>';
		echo '<li><a href="http://vk.com/club91891019" target="_blank">Группа ВКонтакте</a></li>';
		echo '<li><a href="./logout.php">Выход</a></li>';
		echo '</ul>';
		echo '<div class="line"></div>';
		echo '<div class="foot_a">';
		echo '<a href="./">Закрыть</a>';
		echo '</div>';
		break;
		/*-----PDA.КОНЕЦ-----*/

		/*-----ОНЛАЙН-----*/
		case 'us_online':
		echo '<div class="menu">Онлайн</div>';

		$us_on_all = $sql->query("SELECT * FROM `users` WHERE `online` = '1'")->num_rows;
		$us_vs_all = $sql->query("SELECT * FROM `users`")->num_rows;

		echo '<div class="text">В игре: '.$us_on_all.'/'.$us_vs_all.'</div>';
		echo '<div class="line"></div>';

		if ($us_on_all != 0) {

			if (isset($_GET['list'])) {
				$str = $_GET['list'];

				if ($str < 10) header('Location: ?a=us_online');

			} else {
				$str = 10;
			}

			$str2 = $str - 10;

			$us_on_sql = $sql->query("SELECT * FROM `users` WHERE `online` = '1' ORDER BY `id` ASC LIMIT ".$str2.", ".$str."");

			echo '<ul class="links">';

			while ($uon = $us_on_sql->fetch_array(MYSQLI_ASSOC)) {
				echo '<li><a href="?a=mail_new_message&player='.$uon['name'].'">'.$uon['name'].'</a></li>';
			}

			echo '</ul>';
			echo '<div class="line"></div>';
			echo '<div class="text">';

			$strAll = $us_on_all / 10;

			for ($s = 0; $s <= $strAll; $s++) {
				echo '[<a href="?a=us_online&list='.($str2 * $s + 10).'">'.($s + 1).'</a>]';
			}

			echo '</div>';

		}

		echo '<div class="line"></div>';
		echo '<div class="foot_a">';
		echo '<a href="./">Закрыть</a>';
		echo '</div>';
		break;
		/*-----ОНЛАЙН.КОНЕЦ-----*/

		/*-----НАСТРОЙКИ-----*/
		case 'setting':
		$u_conf = $sql->query("SELECT * FROM `users_setting` WHERE `id` = '".$u['id']."'")->fetch_array(MYSQLI_ASSOC);

		echo '<div class="menu">Настройки</div>';

		//сохранение натроек
		if (isset($_POST['setting_save'])) {
			$sql->query("UPDATE `users_setting` SET `chat_on` = '".$_POST['chat_on']."', `logi_on` = '".$_POST['logi_on']."', `map_size` = '".$_POST['map_size']."', `map_kl_size` = '".$_POST['map_kl_size']."' WHERE `id` = '".$u_conf['id']."'");
		
			echo '<div class="text"><font color="#00FF00">Настройки сохранены</font></div>';
			echo '<div class="line"></div>';
		}
		//сохранение.конец

		echo '<ul class="links">';
		echo '<li><a href="?a=setting_pass">Сменить пароль</a></li>';
		echo '</ul>';
		echo '<div class="line"></div>';

		echo '<form method="post" action="">';
		echo '<div class="text">';
		echo 'Чат: <select name="chat_on">';
		echo '<option value="1">включен</option>';
		echo '<option value="0"';

		if ($u_conf['chat_on'] == 0)  echo ' selected';

		echo '>выключен</option>';
		echo '</select><br/>';
		echo 'Логи: <select name="logi_on">';
		echo '<option value="1">включены</option>';
		echo '<option value="0"';

		if ($u_conf['logi_on'] == 0) echo ' selected';

		echo '>выключены</option>';
		echo '</select><br/>';
		echo 'Размер карты: <select name="map_size">';
		echo '<option value="3">7x7</option>';
		echo '<option value="2"';

		if ($u_conf['map_size'] == 2) echo ' selected';

		echo '>5x5</option>';
		echo '<option value="1"';

		if ($u_conf['map_size'] == 1) echo ' selected';

		echo '>3x3</option>';
		echo '</select><br/>';
		echo 'Размер клеток карты: <select name="map_kl_size">';
		echo '<option value="40">40</option>';
		echo '<option value="35"';

		if ($u_conf['map_kl_size'] == 35) echo ' selected';

		echo '>35</option>';
		echo '<option value="30"';

		if ($u_conf['map_kl_size'] == 1) echo ' selected';

		echo '>30</option>';
		echo '</select><br/>';
		echo '</div>';
		echo '<div class="line"></div>';
		echo '<div class="text">';
		echo '<center><input style="margin: 0;" type="submit" name="setting_save" value="Сохранить"/></center>';
		echo '</div>';
		echo '</form>';
		echo '<div class="line"></div>';
		echo '<div class="foot_a">';
		echo '<a href="./">Закрыть</a>';
		echo '</div>';
		break;

		//смена пароля
		case 'setting_pass':
		$u_conf = $sql->query("SELECT * FROM `users_setting` WHERE `id` = '".$u['id']."'")->fetch_array(MYSQLI_ASSOC);

		echo '<div class="menu">Настройки</div>';

		if (isset($_POST['update_pass'])) {
			$error = '';

			if ($_POST['mail'] != $u['mail']) $error .= '&times; E-mail не верен<br/>';
			if (md5($_POST['pass']) != $u['pass']) $error .= '&times; Пароль не верен<br/>';
			if (mb_strlen($_POST['npass']) < 6 || mb_strlen($_POST['npass']) > 20) $error .= '&times; Длина пароля должна быть от 6 до 20 символов<br/>'; 

			if (empty($error)) {
				echo '<div class="text"><font color="#00FF00">Пароль успешно изменён</font></div>';
				echo '<div class="line"></div>';

				$sql->query("UPDATE `users` SET `pass` = '".md5($_POST['npass'])."' WHERE `id` = '".$u['id']."'");

			} else {
				echo '<div class="text"><font color="#FF0000">'.$error.'</font></div>';
				echo '<div class="line"></div>';
			}

		}

		echo '<div class="text">';
		echo '<form method="post" action="">';
		echo '&bull; E-mail:<br/>';
		echo '<input type="email" name="mail" value="@"/><br/>';
		echo '&bull; Пароль:<br/>';
		echo '<input type="password" name="pass" value=""/><br/>';
		echo '&bull; Новый пароль<font color="#666">(6-20 символов)</font>:<br/>';
		echo '<input type="password" name="npass" value=""/><br/>';
		echo '<input type="submit" name="update_pass" value="Изменить"/>';
		echo '</form>';
		echo '</div>';
		echo '<div class="line"></div>';
		echo '<ul class="links">';
		echo '<li><a href="?a=setting">Настройки</a></li>';
		echo '</ul>';
		echo '<div class="line"></div>';
		echo '<div class="foot_a">';
		echo '<a href="./">Закрыть</a>';
		echo '</div>';
		break;
		/*-----НАСТРОЙКИ.КОНЕЦ-----*/

		/*-----ЗАДАНИЯ-----*/
		case 'quest':
		$quest_sql = $sql->query("SELECT * FROM `users_quest` WHERE `status` = '0' AND `user` = '".$u['id']."' ORDER BY `id` ASC");
		$quest_all = $quest_sql->num_rows;

		echo '<div class="menu">Задания</div>';
		echo '<div class="text">Задания: '.$quest_all.'/10</div>';
		echo '<div class="line"></div>';
		echo '<div class="mmenu">Список</div>';

		if ($quest_all != 0) { //если задания есть
			echo '<ul class="links">';

			while ($qi = $quest_sql->fetch_array(MYSQLI_ASSOC)) {
				//подключаемся к заданию
				$qis = $sql->query("SELECT * FROM `quest` WHERE `id` = '".$qi['id']."'")->fetch_array(MYSQLI_ASSOC);

				echo '<li><a href="?a=quest_inf&id='.$qi['id'].'">'.$qis['title'].'</a></li>';

			}

			echo '</ul>';

		} else { //если заданий нет
			echo '<div class="text">Список пуст</div>';
		}

		echo '<div class="line"></div>';
		echo '<div class="foot_a">';
		echo '<a href="./">Закрыть</a>';
		echo '</div>';
		break;

		//просмотр задания
		case 'quest_inf':
		$quest_sql = $sql->query("SELECT * FROM `users_quest` WHERE `id` = '".$_GET['id']."' AND `status` = '0' AND `user` = '".$u['id']."'");
		$quest_all = $quest_sql->num_rows;

		echo '<div class="menu">Задание</div>';

		if ($quest_all != 0) {
			$qi = $sql->query("SELECT * FROM `quest` WHERE `id` = '".$_GET['id']."'")->fetch_array(MYSQLI_ASSOC);

			//выводим
		    echo '<div class="text">';
		    echo 'Название: '.$qi['title'].'<br/>';
		    echo 'Требудемый уровень: '.$qi['lvl_min'].'-'.$qi['lvl_max'].'<br/>';
		    echo 'Описание: '.$qi['opisanie'].'<br/>';

		    //смотрим требования
		    $quest_treb_sql = $sql->query("SELECT * FROM `quest_treb` WHERE `id` = '".$qi['id']."'");
		    $quest_treb_all = $quest_treb_sql->num_rows;

		    //если есть
		    if ($quest_treb_all != 0) {
			    $qt_kol_vo = 0; //счётчик

			    echo 'Требуется: ';

			    while ($qt = $quest_treb_sql->fetch_array(MYSQLI_ASSOC)) {
			   		//увеличиваем счётчик
			      	$qt_kol_vo++;
			      	//подключаемся к шмоту
			      	$qti = $sql->query("SELECT * FROM `shmots` WHERE `id` = '".$qt['sid']."'")->fetch_array(MYSQLI_ASSOC);
			      	//выводим
			      	echo $qti['title'].'<font color="#666">[x'.$qt['kol_vo'].']</font>';
			        						
			      	if ($qt_kol_vo != $quest_treb_all) {
			       		echo ', ';
			      	} else {
			      	 	echo '.';
			      	}

				}

		    }

		    echo '</div>';

		} else {
			echo '<div class="text">Задание не найдено</div>';
		}

		echo '<div class="line"></div>';
		echo '<ul class="links">';
		echo '<li><a href="?a=quest">Задания</a></li>';
		echo '</ul>';
		echo '<div class="line"></div>';
		echo '<div class="foot_a">';
		echo '<a href="./">Закрыть</a>';
		echo '</div>';

		break;
		/*-----ЗАДАНИЯ.КОНЕЦ-----*/

		/*-----ПОЧТА-----*/
		case 'mail':
		if (!isset($_GET['otp'])) {
			$mail_type = 'user';
		} else {
			$mail_type = 'ot_us';
		}

		$mail_all = $sql->query("SELECT * FROM `users_mail` WHERE `".$mail_type."` = '".$u['name']."'")->num_rows;

		echo '<div class="menu">Почта</div>';
		echo '<div class="panelMenu">';
		echo '<span class="butMenu"><a href="?a=mail">Входящие</a></span>';
		echo '<span class="butMenu"><a href="?a=mail&otp">Отправленные</a></span>';
		echo '</div>';
		echo '<div class="line"></div>';
		echo '<div class="foot_a">';
		echo '<a href="?a=mail_new_message">Отправить сообщение</a>';
		echo '</div>';
		echo '<div class="line"></div>';

		if ($mail_all != 0) { //если почта есть

			if (isset($_GET['str'])) {
				$str = $_GET['str'] * 10;
			} else {
				$str = 0;
			}

			$str2 = $str + 10;
			$mail_sql = $sql->query("SELECT * FROM `users_mail` WHERE `".$mail_type."` = '".$u['name']."' ORDER BY `id` DESC LIMIT ".$str.", ".$str2."");
			
			echo '<ul class="links">';

			while ($mi = $mail_sql->fetch_array(MYSQLI_ASSOC)) {
				echo '<li><a href="?a=mail_reed&id='.$mi['id'].'"><b>'.$mi['title'].'</b><font color="#666">['.$mi['date'].']</font><br/>';

				if ($mail_type == 'user') {
					echo '<b>От</b>: '.$mi['ot_us'].'<br/>';
				} else {
					echo '<b>Кому</b>: '.$mi['user'].'<br/>';
				}

				echo mb_substr($mi['text'],0,50,'UTF-8'); //обрезаем текст

				//выводим ... если надо
				if (mb_strlen($mi['text']) > 50) echo '...';

				echo '</a></li>';

			}

			echo '</ul>';
			echo '<div class="line"></div>';

			//страницы
			$mailAll = $mail_all / 10;

			echo '<div class="text">';

			for ($s = 0; $s <= $mailAll; $s++) {

				if (!isset($_GET['otp'])) {
					echo '<font color="#666">[<a href="?a=mail&str='.$s.'">'.($s + 1).'</a>]</font>';
				} else {
					echo '<font color="#666">[<a href="?a=mail&otp&str='.$s.'">'.($s + 1).'</a>]</font>';
				}

			}

		} else { //если почты нет
			echo '<div class="text">Сообщений нет</div>';
		}

		echo '<div class="line"></div>';
		echo '<div class="foot_a">';
		echo '<a href="./">Закрыть</a>';
		echo '</div>';
		break;

		//прочтение смс
		case 'mail_reed':
		$mail_sql = $sql->query("SELECT * FROM `users_mail` WHERE `id` = '".$_GET['id']."'");
		$mail_all = $mail_sql->num_rows;

		echo '<div class="menu">Сообщение</div>';

		if ($mail_all != 0) { //если сообщение сть
			//смотрим смс
			$mi = $mail_sql->fetch_array(MYSQLI_ASSOC);
			//если читать можно
			if ($mi['ot_us'] == $u['name'] || $mi['user'] == $u['name']) {
				//отмечаем что смс прочитано если ещё не читали
				if ($mi['reed'] == 0 && $mi['user'] == $u['name']) $sql->query("UPDATE `users_mail` SET `reed` = '1' WHERE `id` = '".$mi['id']."'");
				//выводим
				echo '<div class="text">';
				echo '<b>'.$mi['title'].'</b><font color="#666">['.$mi['date'].']</font><br/>';

				if ($mi['ot_us'] == $u['name']) {
					echo '<b>Кому</b>:'.$mi['user'].'<br/>';
				} else {
					echo '<b>От</b>: '.$mi['ot_us'].'<br/>';
				}

				echo $mi['text'];
				echo '</div>';

				if ($mi['user'] == $u['name']) {
					echo '<div class="line"></div>';
					echo '<ul class="links">';
					echo '<li><a href="?a=mail_new_message&player='.$mi['ot_us'].'">Ответить</a></li>';
					echo '</ul>';
				}

			} else { //если читать нельзя
				echo '<div class="text">Сообщение не найдено</div>';
			}

		} else { //если смс не существует
			echo '<div class="text">Сообщение не найдено</div>';
		}

		echo '<div class="line"></div>';
		echo '<ul class="links">';
		echo '<li><a href="?a=mail">Почта</a></li>';
		echo '</ul>';
		echo '<div class="line"></div>';
		echo '<div class="foot_a">';
		echo '<a href="./">Закрыть</a>';
		echo '</div>';
		break;

		//отправка смс
		case 'mail_new_message':
		echo '<div class="menu">Отправка сообщения</div>';

		if (isset($_GET['player'])) {
			$player = $_GET['player'];
		} else {
			$player = '';
		}

		$title = '';
		$text = '';

		//отправка сообщения
		if (isset($_POST['otp'])) {
			//отмечаем
			$player = $_POST['user'];
			$title = strip_tags($_POST['title']);
			$text = strip_tags($_POST['text']);
			//ошибки
			$error = '';
			//смотрим, существует ли такой игрок
			$user_sql = $sql->query("SELECT * FROM `users` WHERE `name` = '".$player."' AND `id` != '".$u['id']."'");
			$user_all = $user_sql->num_rows;

			if ($user_all == 0) $error .= '&times; Такого игрока не существует<br/>';
			if (mb_strlen($title) > 30) $error .= '&times; Тема слишком длинная<br/>';
			if (mb_strlen($text) == 0) $error .= '&times; Текст отсутствует<br/>';
			if (mb_strlen($text) > 500) $error .= '&times; Текст слишком длинный<br/>';

			//если ошибок нет отправляем
			if (empty($error)) {
				//вводим првильное имя игрока
				$us_name_mail = $user_sql->fetch_array(MYSQLI_ASSOC);
				$player = $us_name_mail['name'];
				//меняем тему если игрок не ввёл
				if (mb_strlen($title) == 0) $title = 'Без темы';
				//создаём смс
				$sql->query("INSERT INTO `users_mail` SET `timer` = '".time()."', `date` = '".date("d.m.Y")."', `ot_us` = '".$u['name']."', `title` = '".$title."', `text` = '".$text."', `user` = '".$player."';");
				//очищаем переменные
				$player = '';
				$title = '';
				$text = '';
				//уведомляем
				echo '<div class="text"><font color="#00FF00">Сообщение отправлено</font></div>';

			} else { //если ошибки есть
				//выводи ошибки
				echo '<div class="text"><font color="#FF0000">'.$error.'</font></div>';
			}

			echo '<div class="line"></div>';
		}

		echo '<div class="text">';
		echo '<form method="post" action="">';
		echo '<b>Кому</b>: <input type="text" placeholder="Имя получателя" name="user" value="'.$player.'"/><br/>';
		echo '<b>Тема</b>: <input type="text" placeholder="Не более 30 символов" name="title" value="'.$title.'"/><br/>';
		echo '<b>Текст</b>:<br/>';
		echo '<textarea style="height: 100px; width: 70%;" placeholder="Не более 500 симовлов" name="text">'.$text.'</textarea><br/>';
		echo '<input type="submit" name="otp" value="Отправить"/>';
		echo '</form>';
		echo '</div>';
		echo '<div class="line"></div>';
		echo '<ul class="links">';
		echo '<li><a href="?a=mail">Почта</a></li>';
		echo '</ul>';
		echo '<div class="line"></div>';
		echo '<div class="foot_a">';
		echo '<a href="./">Закрыть</a>';
		echo '</div>';
		break;
		/*-----ПОЧТА.КОНЕЦ-----*/

	}

} else { //если не игрок
	header('Location: ./');
}

include './foot.php';
?>