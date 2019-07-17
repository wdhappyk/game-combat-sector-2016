<?php
define('cms', 1);
require_once 'header.php';

$conf = $sql->query("SELECT * FROM `conf`")->fetch_array(MYSQLI_ASSOC);

if ($user) { //если авторизирован
    $u = $sql->query("SELECT * FROM `users` WHERE `id` = '".$user."'")->fetch_array(MYSQLI_ASSOC);
    //смотрим НПЦ
    $npc_sql = $sql->query("SELECT * FROM `bots` WHERE `id` = '".$_GET['id']."' AND `type` = '2' AND `x` >= '".($u['x'] - 1)."' AND `x` <= '".($u['x'] + 1)."'AND `y` >= '".($u['y'] - 1)."' AND `y` <= '".($u['y'] + 1)."'");
    $npc_all = $npc_sql->num_rows;

    //если НПЦ есть
    if ($npc_all != 0) {
    	//подключаемся
    	$npc = $npc_sql->fetch_array(MYSQLI_ASSOC);
    	//смотрим что может делать нпц
    	//торговать
    	$npc_torg_all = $sql->query("SELECT * FROM `bots_bag` WHERE `bot` = '".$npc['id']."'")->num_rows;
    	//обучать навыкам
    	$npc_naw_all = $sql->query("SELECT * FROM `bots_naw` WHERE `bot` = '".$npc['id']."'")->num_rows;
    	//давать задания
    	$npc_quest_all = $sql->query("SELECT * FROM `quest` WHERE `bot` = '".$npc['id']."' OR `bot_end` = '".$npc['id']."'")->num_rows;

	    switch($_GET['a']) {

	        default:
	        echo '<div class="menu">'.$npc['name'].'</div>';
	        echo '<div class="text">';
	        echo $npc['chat'];
			echo '</div>';
			echo '<div class="line"></div>';
			//действия
			$a_npc = '<ul class="links">';

			//если нпц может торговать
			if ($npc_torg_all != 0) $a_npc .= '<li><a href="?a=shop&id='.$npc['id'].'">Купить вещи</a></li>';
			if ($npc['skup_luta_on'] == 1) $a_npc .= '<li><a href="?a=skup&id='.$npc['id'].'">Продать вещи</a></li>';
			//если может обучать навыкам
			if ($npc_naw_all != 0) $a_npc .= '<li><a href="?a=naw&id='.$npc['id'].'">Изучить навыки</a></li>';
			//если есть задания для игрока
			if ($npc_quest_all != 0) $a_npc .= '<li><a href="?a=quest&id='.$npc['id'].'">Задания</a></li>';
			//если нпц - кузнец
			if ($npc['kuznec_on'] == 1) $a_npc .= '<li><a href="?a=repair&id='.$npc['id'].'">Починить вещи</a></li>';

			$a_npc .= '</ul>';
			$a_npc .= '<div class="line"></div>';
			$a_npc .= '<div class="foot_a">';
			$a_npc .= '<a href="./">Уйти</a>';
			$a_npc .= '</div>';

	        echo $a_npc;
	        
	        break;

	        //починка вещей
	        case 'repair':
	        //если нпц кузне
	        if ($npc['kuznec_on'] == 1) {
	        	echo '<div class="menu">Починка вещей</div>';

	        	$pr_sql = $sql->query("SELECT * FROM `users_bag` WHERE `ek` = '0' AND `user` = '".$u['id']."'");
	        	$pr_all = $pr_sql->num_rows;

	        	if ($pr_all != 0) { //если вещи в сумке есть
	        		$pr_no_all = 0; //счётчик

	        		echo '<ul class="links">';

	        		while ($pr = $pr_sql->fetch_array(MYSQLI_ASSOC)) {
	        			//если вещ сломана
	        			if ($pr['pr'] < $pr['pr_all']) {
	        				$pr_no_all++;

	        				echo '<li><a href="?a=repair&lid='.$pr['id'].'&id='.$npc['id'].'">'.$pr['title'].' <font color="#666">['.$pr['lvl'].' ур]</font>';
	        				echo '<div style="float: right;"><img src="./img/money.png" width="13px" style="margin-bottom: -2px;"> '.ceil((((1 - ($pr['pr'] / $pr['pr_all'])) / 2) * ($pr['cost'] / 2))).' [чинить]</div></a></li>';
	        			
	        				if (isset($_GET['lid']) && $_GET['lid'] == $pr['id']) {
	        					//если денег достаточно
	        					if ($u['money'] >= ceil((((1 - ($pr['pr'] / $pr['pr_all'])) / 2) * $pr['cost']))) {
	        						//чиним
	        						$sql->query("UPDATE `users_bag` SET `pr` = '".$pr['pr_all']."' WHERE `id` = '".$pr['id']."'");
	        						//отнимаем деньги
	        						$sql->query("UPDATE `users` SET `money` = '".($u['money'] - ceil((((1 - ($pr['pr'] / $pr['pr_all'])) / 2) * ($pr['cost'] / 2))))."' WHERE `id` = '".$u['id']."'");
	        					}

	        					//обновялем страницу
	        					header('Location: ?a=repair&id='.$npc['id'].'');

	        				}

	        			}

	        		}

	        		if ($pr_no_all == 0) echo '<div class="text">Все вещи целы</div>';

	        		echo '</ul>';

	        	} else {
	        		echo '<div class="text">Сумка пуста</div>';
	        	}

	        	echo '<div class="line"></div>';
				echo '<ul class="links">';
				echo '<li><a href="?id='.$npc['id'].'">В начало разговора</a></li>';
				echo '</ul>';
				echo '<div class="line"></div>';
				echo '<div class="foot_a">';
				echo '<a href="./">Уйти</a>';
				echo '</div>';

	        } else { //ели не кузнец
	        	header('Location: ?id='.$npc['id'].''); //кидаем назад
	        }

	        break;
	        //починка вещей.конец

	        //задания
	        case 'quest':
	        //если у нпц есть задания
	        if ($npc_quest_all != 0) {
	        	echo '<div class="menu">Задания</div>';
	        	//выводим список заданий
	        	$npc_quest_sql = $sql->query("SELECT * FROM `quest` WHERE `bot` = '".$npc['id']."' OR `bot_end` = '".$npc['id']."'");

	        	echo '<ul class="links">';

	        	$quest_list = 0;

	        	while ($qi = $npc_quest_sql->fetch_array(MYSQLI_ASSOC)) {
		        	//если задание подходит по уровню
		        	if ($u['lvl'] >= $qi['lvl_min'] && $qi['lvl_max'] >= $u['lvl']) {
		        		//если квест можно выполнить
		        		$q_ok = 1; //можно(по умолчанию)

		        		if ($qi['t_quest'] != 0) $q_ok = $sql->query("SELECT * FROM `users_quest` WHERE `id` = '".$qi['t_quest']."' AND `status` = '1' AND `user` = '".$u['id']."'")->num_rows;
		        			
		        		//если требуемый квест выполнен
		        		if ($q_ok != 0) {
		        			//смотим взял ли игрок этот квест
		        			$u_quest_sql = $sql->query("SELECT * FROM `users_quest` WHERE `id` = '".$qi['id']."' AND `user` = '".$u['id']."'");
		        			$u_quest_all = $u_quest_sql->num_rows;
		        			$q_status = -1; //статус квеста

		        			if ($u_quest_all != 0) { //если квест взят
		        				$u_quest = $u_quest_sql->fetch_array(MYSQLI_ASSOC);
		        				$q_status = $u_quest['status']; //меняем статус
		        			}
		        			
		        			//если квест не выполнен
		        			if ($q_status != 1) {
		        				//прибавляем счётчик
		        				$quest_list++;
		        				//выводим квест
		        				echo '<li><a href="?a=quest_inf&qid='.$qi['id'].'&id='.$npc['id'].'">'.$qi['title'].' ['.$qi['lvl_min'].'-'.$qi['lvl_max'].']';

		        				if ($q_status == 0) echo '[в процессе]';

		        				echo '</a></li>';

		        			}

		        		}

		        	}

	        	}

	        	if ($quest_list == 0) echo '<div class="text">У меня нет для тебя заданий</div>';

	        	echo '</ul>';
	        	echo '<div class="line"></div>';
				echo '<ul class="links">';
				echo '<li><a href="?id='.$npc['id'].'">В начало разговора</a></li>';
				echo '</ul>';
				echo '<div class="line"></div>';
				echo '<div class="foot_a">';
				echo '<a href="./">Уйти</a>';
				echo '</div>';

	        } else { //если заданий нет
	        	header('Location: ?id='.$npc['id'].''); //кидаем назад
	        }

	        break;

	        //просмотр задания
	        case 'quest_inf':
	        //если нпц даёт задания
	        if ($npc_quest_all != 0) {
	        	$quest_sql = $sql->query("SELECT * FROM `quest` WHERE `id` = '".$_GET['qid']."' AND `bot` = '".$npc['id']."' OR `id` = '".$_GET['qid']."' AND `bot_end` = '".$npc['id']."'");
	        	$quest_all = $quest_sql->num_rows;

	        	echo '<div class="menu">Задание</div>';

	        	//если квест существует и его даёт этот бот
	        	if ($quest_all != 0) {
	        		$qi = $quest_sql->fetch_array(MYSQLI_ASSOC);

	        		//если задание подходит по уровню
		        	if ($u['lvl'] >= $qi['lvl_min'] && $qi['lvl_max'] >= $u['lvl']) {
		        		//если квест можно выполнить
		        		$q_ok = 1; //можно(по умолчанию)

		        		if ($qi['t_quest'] != 0) $q_ok = $sql->query("SELECT * FROM `users_quest` WHERE `id` = '".$qi['t_quest']."' AND `status` = '1' AND `user` = '".$u['id']."'")->num_rows;
		        			
		        		//если требуемый квест выполнен
		        		if ($q_ok != 0) {
		        			//смотим взял ли игрок этот квест
		        			$u_quest_sql = $sql->query("SELECT * FROM `users_quest` WHERE `id` = '".$qi['id']."' AND `user` = '".$u['id']."'");
		        			$u_quest_all = $u_quest_sql->num_rows;
		        			$q_status = -1; //статус квеста

		        			if ($u_quest_all != 0) { //если квест взят
		        				$u_quest = $u_quest_sql->fetch_array(MYSQLI_ASSOC);
		        				$q_status = $u_quest['status']; //меняем статус
		        			}
		        			
		        			//если квест не выполнен
		        			if ($q_status != 1) {
		        				//выводим
		        				echo '<div class="text">';
		        				echo 'Название: '.$qi['title'].'<br/>';
		        				echo 'Требудемый уровень: '.$qi['lvl_min'].'-'.$qi['lvl_max'].'<br/>';
		        				echo 'Описание: '.$qi['opisanie'].'<br/>';

		        				//смотрим требования
		        				$quest_treb_sql = $sql->query("SELECT * FROM `quest_treb` WHERE `id` = '".$qi['id']."'");
		        				$quest_treb_all = $quest_treb_sql->num_rows;
		        				//счётчик наград
		        				$q_nag = 0;

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

		        						//смотрим, выполнил ли игрок требование на эту вещь
		        						if ($qti['kol_vo_all'] == 1) { //если вещ суммируется
		        							$tls = $sql->query("SELECT * FROM `users_bag` WHERE `user` = '".$u['id']."' AND `title` = '".$qti['title']."' AND `type` = '".$qti['type']."' AND `kol_vo` >= '".$qt['kol_vo']."'")->num_rows;
		        							//если выполнено
		        							if ($tls != 0) $q_nag++;

		        						} else { //если не суммируется
		        							$tls = $sql->query("SELECT * FROM `users_bag` WHERE `user` = '".$u['id']."' AND `title` = '".$qti['title']."' AND `type` = '".$qti['type']."'")->num_rows;
		        							//если выполнено
		        							if ($tls >= $qt['kol_vo']) $q_nag++;

		        						}

		        					}

		        					echo '<br/>';

		        				}

		        				echo 'Можно выполнить ';

		        				if ($qi['type'] == 0) echo 'один раз<br/>';
		        				if ($qi['type'] == 1) echo 'раз в день<br/>';
		        				if ($qi['type'] == 2) echo 'неограниченное кол-во раз<br/>';

		        				echo '</div>';
		        				echo '<div class="line"></div>';
		        				echo '<ul class="links">';

		        				if ($q_status == -1) echo '<li><a href="?a=quest_d&vz&qid='.$qi['id'].'&id='.$npc['id'].'">Взять задание</a></li>';
		        				if ($q_status == 0) {
		        					if ($q_nag == $quest_treb_all && $qi['bot_end'] == $npc['id']) {
		        						echo '<li><a href="?a=quest_d&v&qid='.$qi['id'].'&id='.$npc['id'].'">';
		        						
		        						if ($qi['qid'] == 0) {
		        							echo 'Получить награду';
		        						} else {
		        							echo 'Завершить';
		        						}

		        						echo '</a></li>';
		        					}
		        					echo '<li><a href="?a=quest_d&x&qid='.$qi['id'].'&id='.$npc['id'].'">Отказаться</a></li>';
		        				}

		        				echo '</ul>';

		        			} else { //если квест выполнен
					        	echo '<div class="text">Задание не найдено</div>';
					        }

		        		} else { //если требуемый квест не выполнен
				        	echo '<div class="text">Задание не найдено</div>';
				        }

		        	} else { //если задание не подходит по уровню
			        	echo '<div class="text">Задание не найдено</div>';
			        }

		        } else { //если квеста нет
		        	echo '<div class="text">Задание не найдено</div>';
		        }
	        

	        	echo '<div class="line"></div>';
				echo '<ul class="links">';
				echo '<li><a href="?a=quest&id='.$npc['id'].'">Задания</a></li>';
				echo '</ul>';
				echo '<div class="line"></div>';
				echo '<div class="foot_a">';
				echo '<a href="./">Уйти</a>';
				echo '</div>';

	        } else { //если заданий нет
	        	header('Location: ?id='.$npc['id'].''); //кидаем назад
	        }

	        break;

	        //действия с заданием
	        case 'quest_d':
	        //если нпц даёт задания
	        if ($npc_quest_all != 0) {
	        	$quest_sql = $sql->query("SELECT * FROM `quest` WHERE `id` = '".$_GET['qid']."' AND `bot` = '".$npc['id']."' OR `id` = '".$_GET['qid']."' AND `bot_end` = '".$npc['id']."'");
	        	$quest_all = $quest_sql->num_rows;

	        	echo '<div class="menu">Задание</div>';

	        	//если квест существует и его даёт этот бот
	        	if ($quest_all != 0) {
	        		$qi = $sql->query("SELECT * FROM `quest` WHERE `id` = '".$_GET['qid']."'")->fetch_array(MYSQLI_ASSOC);
		        	//если квест можно выполнить
		        	$q_ok = 1; //можно(по умолчанию)

		        	if ($qi['t_quest'] != 0) $q_ok = $sql->query("SELECT * FROM `users_quest` WHERE `id` = '".$qi['t_quest']."' AND `status` = '1' AND `user` = '".$u['id']."'")->num_rows;
		        			
		        	//если требуемый квест выполнен
		        	if ($q_ok != 0) {
		        		//смотим взял ли игрок этот квест
		        		$u_quest_sql = $sql->query("SELECT * FROM `users_quest` WHERE `id` = '".$qi['id']."' AND `user` = '".$u['id']."'");
		        		$u_quest_all = $u_quest_sql->num_rows;
		        		$q_status = -1; //статус квеста

		        		if ($u_quest_all != 0) { //если квест взят
		        			$u_quest = $u_quest_sql->fetch_array(MYSQLI_ASSOC);
		        			$q_status = $u_quest['status']; //меняем статус
		        		}
		        			
		        		//если квест не выполнен
		        		if ($q_status != 1) {
		        			//если игрок захотел взять квест
		        			if (isset($_GET['vz'])) {
		        				//если задание подходит по уровню
		        				if ($u['lvl'] >= $qi['lvl_min'] && $qi['lvl_max'] >= $u['lvl']) {
			        				//смотрим сколько квестов на данный момент у игрока
			        				$u_quest_all = $sql->query("SELECT * FROM `users_quest` WHERE `status` = '0' AND `user` = '".$u['id']."'")->num_rows;
			        				//если заданий < 10
			        				if ($u_quest_all < 10) {
			        					//добавляем квест игроку
			        					$sql->query("INSERT INTO `users_quest` SET `id` = '".$qi['id']."', `user` = '".$u['id']."'");
			        					//создаём лог
			        					$sql->query("INSERT INTO `users_logi` SET `user` = '".$u['id']."', `text` = 'Задание [".$qi['title']."] получено', `dtime` = '".date("H:i")."'");

			        					//кидаем к нпц
			        					header('Location: ?a=quest&id='.$npc['id'].'');
			        				} else { //если 10 заданий уже взято
			        					echo '<div class="text">Вы взяли уже слишком много заданий</div>';
			        				}

			        			} else { //если не подходит по уровню
		        					echo '<div class="text">Задание не найдено</div>';
		        				}

		        			} else if (isset($_GET['v'])) { //елси игрок сказал что выполнил задание
		        				//если бот принимает здачу этого задания
		        				if ($qi['bot_end'] == $npc['id']) {
		        					//смотрим можно ли получить награду
		        					//смотрим требования
			        				$quest_treb_sql = $sql->query("SELECT * FROM `quest_treb` WHERE `id` = '".$qi['id']."'");
			        				$quest_treb_all = $quest_treb_sql->num_rows;
		        					//счётчик наград
			        				$q_nag = 0;

			        				//если есть
			        				if ($quest_treb_all != 0) {
			        					$qt_kol_vo = 0; //счётчик

			        					while ($qt = $quest_treb_sql->fetch_array(MYSQLI_ASSOC)) {
			        						//увеличиваем счётчик
			        						$qt_kol_vo++;
			        						//подключаемся к шмоту
			        						$qti = $sql->query("SELECT * FROM `shmots` WHERE `id` = '".$qt['sid']."'")->fetch_array(MYSQLI_ASSOC);

			        						//смотрим, выполнил ли игрок требование на эту вещь
			        						if ($qti['kol_vo_all'] == 1) { //если вещ суммируется
			        							$tls = $sql->query("SELECT * FROM `users_bag` WHERE `user` = '".$u['id']."' AND `title` = '".$qti['title']."' AND `type` = '".$qti['type']."' AND `kol_vo` >= '".$qt['kol_vo']."'")->num_rows;
			        							//если выполнено
			        							if ($tls != 0) $q_nag++;

			        						} else { //если не суммируется
			        							$tls = $sql->query("SELECT * FROM `users_bag` WHERE `user` = '".$u['id']."' AND `title` = '".$qti['title']."' AND `type` = '".$qti['type']."'")->num_rows;
			        							//если выполнено
			        							if ($tls >= $qt['kol_vo']) $q_nag++;

			        						}

			        					}

		        					}

		        					//если игрок выполнил все условия
			        				if ($q_nag == $quest_treb_all) {
			        					//удаляем из рюкзака то что требовал нпц
			        					$quest_treb_sql = $sql->query("SELECT * FROM `quest_treb` WHERE `id` = '".$qi['id']."'");

				        				while ($qt = $quest_treb_sql->fetch_array(MYSQLI_ASSOC)) {
				        					//увеличиваем счётчик
				        					$qt_kol_vo++;
				        					//подключаемся к шмоту
				        					$qti = $sql->query("SELECT * FROM `shmots` WHERE `id` = '".$qt['sid']."'")->fetch_array(MYSQLI_ASSOC);

				        					//смотрим
				        					if ($qti['kol_vo_all'] == 0) { //если вещ не суммируется
				        						$limit = 1;
				        						if ($qt['kol_vo'] > 1) $limit = $qt['kol_vo'];

				        						$tls = $sql->query("DELETE FROM `users_bag` WHERE `user` = '".$u['id']."' AND `title` = '".$qti['title']."' AND `type` = '".$qti['type']."' LIMIT ".$limit."");

				        					} else { //если суммируется
				        						//смотрим сколько предмета в рюкзаке
				        						$kol_vo_luta = $sql->query("SELECT * FROM `users_bag` WHERE `user` = '".$u['id']."' AND `title` = '".$qti['title']."' AND `type` = '".$qti['type']."'")->fetch_array(MYSQLI_ASSOC);

				        						if ($kol_vo_luta['kol_vo'] == $qt['kol_vo']) {
				        							$sql->query("DELETE FROM `users_bag` WHERE `id` = '".$kol_vo_luta['id']."'");
				        						} else {
				        							$sql->query("UPDATE `users_bag` SET `kol_vo` = '".($kol_vo_luta['kol_vo'] - $qt['kol_vo'])."' WHERE `id` = '".$kol_vo_luta['id']."'");
				        						}

				        					}

				        				}

				        				if ($qi['qid'] == 0) {
				        					//уведомляем
				        					$sql->query("INSERT INTO `users_logi` SET `user` = '".$u['id']."', `text` = 'Задание [".$qi['title']."] выполнено', `dtime` = '".date("H:i")."'");
			        					} else {
			        						//уведомляем
			        						$sql->query("INSERT INTO `users_logi` SET `user` = '".$u['id']."', `text` = 'Задание [".$qi['title']."] было обновлено', `dtime` = '".date("H:i")."'");
			        						$sql->query("INSERT INTO `users_quest` SET `id` = '".$qi['qid']."', `user` = '".$u['id']."';");
			        					}

			        					//указываем что квест выполнен если кв 1 разовое или ежедневное, иначе удаляем
				        				if ($qi['type'] != 2) {
				        					$sql->query("UPDATE `users_quest` SET `status` = '1' WHERE `id` = '".$qi['id']."'");
				        				} else {
				        					$sql->query("DELETE FROM `users_quest` WHERE `id` = '".$qi['id']."' AND `user` = '".$u['id']."'");
				        				}
			        						
			        					//даём награду
			        					$sql->query("UPDATE `users` SET `exp` = '".($u['exp'] + $qi['exp'])."', `money` = '".($u['money'] + $qi['money'])."' WHERE `id` = '".$u['id']."'");
			        					//создаём логи
			        					if ($qi['exp'] != 0) $sql->query("INSERT INTO `users_logi` SET `user` = '".$u['id']."', `text` = '<font color=\"#0000FF\">Опыт +".$qi['exp']."</font>', `dtime` = '".date("H:i")."'");
			        					if ($qi['money'] != 0) $sql->query("INSERT INTO `users_logi` SET `user` = '".$u['id']."', `text` = '<font color=\"#0000FF\">Кредиты +".$qi['money']."</font>', `dtime` = '".date("H:i")."'");
			        					//награда в виде предметов
			        					$nagrada_shmot_sql = $sql->query("SELECT * FROM `quest_nagrada` WHERE `id` = '".$qi['id']."'");
			        					$nagrada_shmot_all = $nagrada_shmot_sql->num_rows;

			        					//если есть
			        					if ($nagrada_shmot_all != 0) {
			        						//выдаём
			        						while ($nsq = $nagrada_shmot_sql->fetch_array(MYSQLI_ASSOC)) {
			        							//подключаемся к шмотке
			        							$ns = $sql->query("SELECT * FROM `shmots` WHERE `id` = '".$nsq['sid']."'")->fetch_array(MYSQLI_ASSOC);
			        							//выдаём её
			        							if ($ns['kol_vo_all'] == 0) { //если шмот не суммируется
			        								//добавляем в рюкзак
					                                $sql->query("INSERT INTO `users_bag` SET `lvl` = '".$ns['lvl']."', `title` = '".$ns['title']."', `type` = '".$ns['type']."', `t_naw` = '".$ns['t_naw']."', `t_naw_lvl` = '".$ns['lvl']."', `speed_att` = '".$ns['speed_att']."', `speed_att_all` = '".$ns['speed_att_all']."', `kalibr` = '".$ns['kalibr']."', `patron` = '".$ns['patron']."', `patron_all` = '".$ns['patron_all']."', `att` = '".$ns['att']."', `att_all` = '".$ns['att_all']."', `rej_str` = '".$ns['rej_str']."', `rej_str_all` = '".$ns['rej_str_all']."', `radius_att` = '".$ns['radius_att']."', `pr` = '".$ns['pr']."', `pr_all` = '".$ns['pr_all']."', `ves` = '".$ns['ves']."', `def` = '".$ns['def']."', `str` = '".$ns['str']."', `agi` = '".$ns['agi']."', `dex` = '".$ns['dex']."', `hp` = '".$ns['hp']."', `hp_all` = '".$ns['hp_all']."', `en` = '".$ns['en']."', `en_all` = '".$ns['en_all']."', `speed_hod_all` = '".$ns['speed_hod_all']."', `ruki` = '".$ns['ruki']."', `kol_vo` = '".$ns['kol_vo']."', `kol_vo_all` = '".$ns['kol_vo_all']."', `vmest` = '".$ns['vmest']."', `cost` = '".$ns['cost']."', `k_stat` = '".$ns['k_stat']."', `user` = '".$u['id']."';");
					                                //создаём лог
					                                $sql->query("INSERT INTO `users_logi` SET `user` = '".$u['id']."', `text` = 'Вы получили [".$ns['title']."]', `dtime` = '".date("H:i")."'");

			        							} else { //если суммируется
			        								//проверяем, есть ли такой предмет в рюкзаке
					                                $bag_shmot_cop_sql = $sql->query("SELECT * FROM `users_bag` WHERE `user` = '".$u['id']."' AND `title` = '".$ns['title']."' AND `type` = '".$ns['type']."'");
					                                $bag_shmot_cop_all = $bag_shmot_cop_sql->num_rows;

					                                if ($bag_shmot_cop_all == 0) { //если такого предмета в рюкзаке нет
					                                    //добавляем в рюкзак
					                                    $sql->query("INSERT INTO `users_bag` SET `lvl` = '".$ns['lvl']."', `title` = '".$ns['title']."', `type` = '".$ns['type']."', `t_naw` = '".$ns['t_naw']."', `t_naw_lvl` = '".$ns['lvl']."', `speed_att` = '".$ns['speed_att']."', `speed_att_all` = '".$ns['speed_att_all']."', `kalibr` = '".$ns['kalibr']."', `patron` = '".$ns['patron']."', `patron_all` = '".$ns['patron_all']."', `att` = '".$ns['att']."', `att_all` = '".$ns['att_all']."', `rej_str` = '".$ns['rej_str']."', `rej_str_all` = '".$ns['rej_str_all']."', `radius_att` = '".$ns['radius_att']."', `pr` = '".$ns['pr']."', `pr_all` = '".$ns['pr_all']."', `ves` = '".$ns['ves']."', `def` = '".$ns['def']."', `str` = '".$ns['str']."', `agi` = '".$ns['agi']."', `dex` = '".$ns['dex']."', `hp` = '".$ns['hp']."', `hp_all` = '".$ns['hp_all']."', `en` = '".$ns['en']."', `en_all` = '".$ns['en_all']."', `speed_hod_all` = '".$ns['speed_hod_all']."', `ruki` = '".$ns['ruki']."', `kol_vo` = '".$nsq['kol_vo']."', `kol_vo_all` = '".$ns['kol_vo_all']."', `vmest` = '".$ns['vmest']."', `cost` = '".$ns['cost']."', `k_stat` = '".$ns['k_stat']."', `user` = '".$u['id']."';");
					                                } else { //если есть
					                                    $bag_shmot_cop = $bag_shmot_cop_sql->fetch_array(MYSQLI_ASSOC);
					                                    //добавляем в рюкзак
					                                    $sql->query("UPDATE `users_bag` SET `kol_vo` = '".($bag_shmot_cop['kol_vo'] + $nsq['kol_vo'])."' WHERE `id` = '".$bag_shmot_cop['id']."'");
					                                }

					                                //создаём лог
					                                $sql->query("INSERT INTO `users_logi` SET `user` = '".$u['id']."', `text` = 'Вы получили [".$ns['title']."] [x".$nsq['kol_vo']."]', `dtime` = '".date("H:i")."'");

			        							}


			        						}

			        					}
			        					//награда в виде лута.конец
			        					//кидаем к нпц
			        					header('Location: ?a=quest&id='.$npc['id'].'');
									//получение награды.конец
			        				} else { //если игрок не выполнил всех условий
			        					echo '<div class="text">Задание не выполнено!</div>';
			        				}

		        				} else {
		        					echo '<div class="text">Нпц не может дать награду за это задание</div>';
		        				}

		        			} else if (isset($_GET['x'])) { //если игрок решил отказаться от квеста
		        				//удаляем задание
		        				$sql->query("DELETE FROM `users_quest` WHERE `id` = '".$qi['id']."' AND `user` = '".$u['id']."' LIMIT 1");
		        				//создаём лог
		        				$sql->query("INSERT INTO `users_logi` SET `user` = '".$u['id']."', `text` = 'Вы отказались от задания [".$qi['title']."]', `dtime` = '".date("H:i")."'");
		        				//кидаем к нпц
			        			header('Location: ?a=quest&id='.$npc['id'].'');

		        			} else { //если ничего не выбрано
		        				echo '<div class="text">Действие не выбрано</div>';
		        			}

	        			} else { //если квест выполнен
					    	echo '<div class="text">Задание не найдено</div>';
					    }

		        	} else { //если требуемый квест не выполнен
				    	echo '<div class="text">Задание не найдено</div>';
				    }

		        } else { //если квеста нет
		        	echo '<div class="text">Задание не найдено</div>';
		        }

	        	echo '<div class="line"></div>';
				echo '<ul class="links">';
				echo '<li><a href="?a=quest&id='.$npc['id'].'">Задания</a></li>';
				echo '</ul>';
				echo '<div class="line"></div>';
				echo '<div class="foot_a">';
				echo '<a href="./">Уйти</a>';
				echo '</div>';

	        } else { //если заданий нет
	        	header('Location: ?id='.$npc['id'].''); //кидаем назад
	        }

	        break;
	        //задания.конец

	        //обучение навыкам
	        case 'naw':
	        //если нпц обучает навыкам
	        if ($npc_naw_all != 0) {
	        	echo '<div class="menu">Изучение навыков</div>';
	        	//выводим список навыков
	        	$npc_naw_sql = $sql->query("SELECT * FROM `bots_naw` WHERE `bot` = '".$npc['id']."' ORDER BY `id` ASC");

	        	echo '<ul class="links">';

	        	while ($ni = $npc_naw_sql->fetch_array(MYSQLI_ASSOC)) {
	        		echo '<li><a href="?a=naw_inf&nid='.$ni['id'].'&id='.$npc['id'].'">'.$ni['title'].'</a></li>';
	        	}

	        	echo '</ul>';
	        	echo '<div class="line"></div>';
				echo '<ul class="links">';
				echo '<li><a href="?id='.$npc['id'].'">В начало разговора</a></li>';
				echo '</ul>';
				echo '<div class="line"></div>';
				echo '<div class="foot_a">';
				echo '<a href="./">Уйти</a>';
				echo '</div>';

	        } else { //если нпц не обучает навыкам
	        	header('Location: ?id='.$npc['id'].''); //кидаем назад
	        }

	        break;

	        //информация о навыке
	        case 'naw_inf':
	        //если нпц обучает навыкам
	        if ($npc_naw_all != 0) {
	        	//смотрим навык
	        	$naw_sql = $sql->query("SELECT * FROM `bots_naw` WHERE `id` = '".$_GET['nid']."' AND `bot` = '".$npc['id']."'");
	        	$naw_all = $naw_sql->num_rows;

	        	echo '<div class="menu">Навык</div>';

	        	//если навык есть
	        	if ($naw_all != 0) {
	        		$ni = $naw_sql->fetch_array(MYSQLI_ASSOC);

					echo '<div class="text">';
					echo '<table border="0" cellpadding="0" cellspacing="0" style="padding: 0; margin: 0;" width="100%">';
					echo '<tr>';
					echo '<td width="50%">Название:</td>';
					echo '<td width="50%">'.$ni['title'].'</td>';
					echo '</tr>';

					echo '<tr>';
					echo '<td width="50%">Описание:</td>';
					echo '<td width="50%">'.$ni['opisanie'].'</td>';
					echo '</tr>';

					echo '<tr>';
					echo '<td width="50%">Цена:</td>';
					echo '<td width="50%">'.round($ni['cost'], 2).' кредитов</td>';
					echo '</tr>';

					echo '</table>';
					echo '</div>';
					echo '<div class="line"></div>';
					echo '<ul class="links">';
					echo '<li><a href="?a=naw_shop&nid='.$ni['id'].'&id='.$npc['id'].'">Изучить</a></li>';
					echo '</ul>';

	        	} else {
					echo '<div class="text">Навык не найден</div>';
				}

				echo '<div class="line"></div>';
				echo '<ul class="links">';
				echo '<li><a href="?a=naw&id='.$npc['id'].'">Изучение навыков</a></li>';
				echo '</ul>';
				echo '<div class="line"></div>';
				echo '<div class="foot_a">';
				echo '<a href="./">Уйти</a>';
				echo '</div>';

	        } else { //если нпц не обучает навыкам
	        	header('Location: ?id='.$npc['id'].''); //кидаем назад
	        }

	        break;

	        //изучение навыка
	        case 'naw_shop':
	        //если нпц обучает навыкам
	        if ($npc_naw_all != 0) {
	        	//смотрим навык
	        	$naw_sql = $sql->query("SELECT * FROM `bots_naw` WHERE `id` = '".$_GET['nid']."' AND `bot` = '".$npc['id']."'");
	        	$naw_all = $naw_sql->num_rows;

	        	echo '<div class="menu">Изучение навыка</div>';

	        	//если навык есть
	        	if ($naw_all != 0) {
	        		$ni = $naw_sql->fetch_array(MYSQLI_ASSOC);
	        		$nawAll = $sql->query("SELECT * FROM `users_naw` WHERE `user` = '".$u['id']."'")->num_rows;
	        		$cop_us_naw = $sql->query("SELECT * FROM `users_naw` WHERE `title` = '".$ni['title']."' AND `user` = '".$u['id']."'")->num_rows;

	        		//если есть свободные слоты
	        		if ($nawAll < $u['slots_naw']) {
		        		//если денег хватает
		        		if ($u['money'] >= ceil($ni['cost'])) {
		        			//если игрок не изучал такой навык
		        			if ($cop_us_naw == 0) {
		        				//добавляем навык
		        				$sql->query("INSERT INTO `users_naw` SET `title` = '".$ni['title']."', `kach_ot` = '".$ni['kach_ot']."', `kach_stat` = '".$ni['kach_stat']."', `kaсh_stat_plus` = '".$ni['kaсh_stat_plus']."', `opisanie` = '".$ni['opisanie']."', `user` = '".$u['id']."';");
		        				//отнимаем деньги
		        				$sql->query("UPDATE `users` SET `money` = '".($u['money'] - ceil($ni['cost']))."' WHERE `id` = '".$u['id']."'");
		        				//создаём логи
								$sql->query("INSERT INTO `users_logi` SET `user` = '".$u['id']."', `text` = 'Вы изучили навык [".$ni['title']."]', `dtime` = '".date("H:i")."'");
								$sql->query("INSERT INTO `users_logi` SET `user` = '".$u['id']."', `text` = '<font color=\"#0000FF\">Кредиты -".ceil($ni['cost'])."</font>', `dtime` = '".date("H:i")."'");

								header('Location: ?a=naw&id='.$npc['id'].''); //кидаем назад

		        			} else { //если изучал
		        				echo '<div class="text">Вы уже знаете этот навык</div>';
		        			}

		        		} else { //если денег не хватает
		        			echo '<div class="text">Недостаточно средств</div>';
		        		}

		        	} else { //если нет свободных статов
		        		echo '<div class="text">Вы изучили максимальное число навыков</div>';
		        	}

	        	} else {
					echo '<div class="text">Навык не найден</div>';
				}

				echo '<div class="line"></div>';
				echo '<ul class="links">';
				echo '<li><a href="?a=naw&id='.$npc['id'].'">Изучение навыков</a></li>';
				echo '</ul>';
				echo '<div class="line"></div>';
				echo '<div class="foot_a">';
				echo '<a href="./">Уйти</a>';
				echo '</div>';

	        } else { //если нпц не обучает навыкам
	        	header('Location: ?id='.$npc['id'].''); //кидаем назад
	        }

	        break;
	        //обучение навыкам.конец

	        //скуп вещей
	        case 'skup':
	        //если нпц скупает вещи
	        if ($npc['skup_luta_on'] == 1) {
	        	echo '<div class="menu">Продажа вещей</div>';
	        	//выводим список вещей из рюкзака
	        	$lutBagAll = $sql->query("SELECT * FROM `users_bag` WHERE `user` = '".$u['id']."' AND `ek` = '0'")->num_rows;

				if ($lutBagAll != 0) {

					if (isset($_GET['str'])) {
						$str = $_GET['str'] * 10;
					} else {
						$str = 0;
					}

					$str2 = $str + 10;
					$lutBagSQL = $sql->query("SELECT * FROM `users_bag` WHERE `user` = '".$u['id']."' AND `ek` = '0' ORDER BY `id` DESC LIMIT ".$str.", ".$str2."");

					echo '<form method="post" action="">';
					echo '<ul class="links">';

					while ($li = $lutBagSQL->fetch_array(MYSQLI_ASSOC)) {
						echo '<li>'.$li['title'].' <font color="#666">['.$li['lvl'].' ур]';

						if ($li['kol_vo_all'] == 1) echo ' [x'.$li['kol_vo'].']';

						echo '</font>';

						//кнопки
						if ($li['pr_all'] != 0) {
							$proc_pr = round(($li['pr'] / $li['pr_all']));
						} else {
							$proc_pr = 1;
						}

						echo '<div style="float: right;"><img src="./img/money.png" width="13px" style="margin-bottom: -2px;"> '.round(($li['cost'] * (0.7 * $proc_pr)), 2).' ';

						if ($li['kol_vo_all'] == 0) { //если шмот не суммируется
							echo '<input type="checkbox" name="id_'.$li['id'].'" value=""/>';

							if (isset($_POST['id_'.$li['id']])) {
								//удаляем вещ из рюкзака
								$sql->query("DELETE FROM `users_bag` WHERE `id` = '".$li['id']."' LIMIT 1");
								//прибавляем деньги
								$sql->query("UPDATE `users` SET `money` = '".($u['money'] + floor(round(($li['cost'] * (0.7 * $proc_pr)), 2)))."' WHERE `id` = '".$u['id']."'");
								//создаём логи
								$sql->query("INSERT INTO `users_logi` SET `user` = '".$u['id']."', `text` = 'Вы продали [".$li['title']."]', `dtime` = '".date("H:i")."'");
								$sql->query("INSERT INTO `users_logi` SET `user` = '".$u['id']."', `text` = '<font color=\"#0000FF\">Кредиты +".floor(round(($li['cost'] * (0.7 * $proc_pr)), 2))."</font>', `dtime` = '".date("H:i")."'");
							}

						} else { //если шмот суммируется
							echo '<input style="font-size: 12px; padding: 0; width: 40px;" type="number" name="id_'.$li['id'].'" value="0"/>';

							if (isset($_POST['id_'.$li['id']])) {
								//проверяем на введённые данные
								if(!preg_match('/^[0-9]{1,}$/iu', $_POST['id_'.$li['id']])) {
									$kol_vo = 0;
								} else {
									$kol_vo = $_POST['id_'.$li['id']];
								}

								$kol_vo = abs($kol_vo);

								//если введено больше 0
								if ($kol_vo > 0) {
									//если введёное число меньше чем колво предмета
									if ($kol_vo < $li['kol_vo']) {
										//уменьшаем
										$sql->query("UPDATE `users_bag` SET `kol_vo` = '".($li['kol_vo'] - $kol_vo)."' WHERE `id` = '".$li['id']."'");
									} else {
										//удаляем
										$sql->query("DELETE FROM `users_bag` WHERE `id` = '".$li['id']."' LIMIT 1");
										//делаем кол-во нормальным
										$kol_vo = $li['kol_vo'];
									}

									//прибавляем деньги
									$li_cost = floor(round(($li['cost'] * (0.7 * $proc_pr)), 2) * $kol_vo);
									$sql->query("UPDATE `users` SET `money` = '".($u['money'] + $li_cost)."' WHERE `id` = '".$u['id']."'");
									//создаём логи
									$sql->query("INSERT INTO `users_logi` SET `user` = '".$u['id']."', `text` = 'Вы продали [".$li['title']."] [x".$kol_vo."]', `dtime` = '".date("H:i")."'");
									$sql->query("INSERT INTO `users_logi` SET `user` = '".$u['id']."', `text` = '<font color=\"#0000FF\">Кредиты +".$li_cost."</font>', `dtime` = '".date("H:i")."'");

								}

							}

						}

						echo '</div>';
						echo '</li>';
					}

					echo '</ul>';
					echo '<div class="line"></div>';
					echo '<center><input type="submit" name="skup_ok" value="Продать"/></center>';
					//если была нажата кнопка "продать"
					if (isset($_POST['skup_ok'])) header('Location: ?a=skup&id='.$npc['id'].''); //обновляем стр

					echo '</form>';
					echo '<div class="line"></div>';

					$strAll = $lutBagAll / 10;

					echo '<div class="text">';

					for ($s = 0; $s <= $strAll; $s++) {
						echo '<font color="#666">[<a href="?a=skup&str='.$s.'&id='.$npc['id'].'">'.($s + 1).'</a>]</font>';
					}

					echo '</div>';

				} else {
					echo '<div class="text">Ничего нет</div>';
				}

				echo '<div class="line"></div>';
				echo '<ul class="links">';
				echo '<li><a href="?id='.$npc['id'].'">В начало разговора</a></li>';
				echo '</ul>';
				echo '<div class="line"></div>';
				echo '<div class="foot_a">';
				echo '<a href="./">Уйти</a>';
				echo '</div>';

	        } else { //если нпц не скупает лут
	        	header('Location: ?id='.$npc['id'].''); //кидаем назад
	        }

	        break;

	        //покупка вещей
	        case 'shop':
	        //если нпц тогругет
	        if ($npc_torg_all != 0) {
	        	echo '<div class="menu">Покупка вещей</div>';
	        	//выводим список вещей
	        	if (isset($_GET['str'])) {
	        		$str = $_GET['str'] * 10;
	        	} else {
	        		$str = 0;
	        	}

	        	$str2 = $str + 10;
	        	$lut_sql = $sql->query("SELECT * FROM `bots_bag` WHERE `bot` = '".$npc['id']."' ORDER BY `lvl` DESC LIMIT ".$str.", ".$str2."");

	        	//выводим
	        	echo '<ul class="links">';

	        	while ($li = $lut_sql->fetch_array(MYSQLI_ASSOC)) {
	        		echo '<li><a href="?a=shop_lut_inf&lid='.$li['id'].'&id='.$npc['id'].'">'.$li['title'].' <font color="#666">['.$li['lvl'].' ур]</font></a></li>';
	        	}

	        	echo '</ul>';
				echo '<div class="line"></div>';

				$strAll = $npc_torg_all / 10;

				echo '<div class="text">';

				for ($s = 0; $s <= $strAll; $s++) {
					echo '<font color="#666">[<a href="?a=shop&str='.$s.'&id='.$npc['id'].'">'.($s + 1).'</a>]</font>';
				}

				echo '</div>';
				echo '<div class="line"></div>';
				echo '<ul class="links">';
				echo '<li><a href="?id='.$npc['id'].'">В начало разговора</a></li>';
				echo '</ul>';
				echo '<div class="line"></div>';
				echo '<div class="foot_a">';
				echo '<a href="./">Уйти</a>';
				echo '</div>';

	        } else { //если нпц не торгует
	        	header('Location: ?id='.$npc['id'].''); //кидаем назад
	        }

	        break;

	        //информация о предмете
			case 'shop_lut_inf':
			//если нпц торгует
			if ($npc_torg_all != 0) {
				echo '<div class="menu">Предмет</div>';

				$lSQL = $sql->query("SELECT * FROM `bots_bag` WHERE `bot` = '".$npc['id']."' AND `id` = '".$_GET['lid']."'");
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

					if ($li['type'] == 2 && $li['vmest']) {
						echo '<tr>';
						echo '<td width="50%">Вместимость:</td>';
						echo '<td width="50%">'.$li['vmest'].'</td>';
						echo '</tr>';
					}

					echo '<tr>';
					echo '<td width="50%">Стоимость:</td>';
					echo '<td width="50%">'.round($li['cost'], 2).' кредитов</td>';
					echo '</tr>';
					echo '</table>';
					echo '</div>';
					echo '<div class="line"></div>';
					echo '<ul class="links">';
					echo '<li><a href="?a=shop_lut&lid='.$li['id'].'&id='.$npc['id'].'">Купить</a></li>';
					echo '</ul>';

				} else {
					echo '<div class="text">Предмет не найден</div>';
				}


				echo '<div class="line"></div>';
				echo '<ul class="links">';
				echo '<li><a href="?a=shop&id='.$npc['id'].'">Покупка вещей</a></li>';
				echo '</ul>';
				echo '<div class="line"></div>';
				echo '<div class="foot_a">';
				echo '<a href="./">Уйти</a>';
				echo '</div>';

			} else { //если нпц не торгует
	        	header('Location: ?id='.$npc['id'].''); //кидаем назад
	        }

			break;

			//покупка вещи
			case 'shop_lut':
			//если нпц торгует
			if ($npc_torg_all != 0) {
				echo '<div class="menu">Покупка предмета</div>';

				$lSQL = $sql->query("SELECT * FROM `bots_bag` WHERE `bot` = '".$npc['id']."' AND `id` = '".$_GET['lid']."'");
				$lAll = $lSQL->num_rows;

				if ($lAll != 0) {
					$li = $lSQL->fetch_array(MYSQLI_ASSOC);

					if ($li['kol_vo_all'] == 0) { //если предмет не суммируется
						//если денег хватает
						if ($u['money'] >= ceil($li['cost'])) {
							//добавляем в рюкзак
                            $sql->query("INSERT INTO `users_bag` SET `lvl` = '".$li['lvl']."', `title` = '".$li['title']."', `type` = '".$li['type']."', `t_naw` = '".$li['t_naw']."', `t_naw_lvl` = '".$li['lvl']."', `speed_att` = '".$li['speed_att']."', `speed_att_all` = '".$li['speed_att_all']."', `kalibr` = '".$li['kalibr']."', `patron` = '".$li['patron']."', `patron_all` = '".$li['patron_all']."', `att` = '".$li['att']."', `att_all` = '".$li['att_all']."', `rej_str` = '".$li['rej_str']."', `rej_str_all` = '".$li['rej_str_all']."', `radius_att` = '".$li['radius_att']."', `pr` = '".$li['pr']."', `pr_all` = '".$li['pr_all']."', `ves` = '".$li['ves']."', `def` = '".$li['def']."', `str` = '".$li['str']."', `agi` = '".$li['agi']."', `dex` = '".$li['dex']."', `hp` = '".$li['hp']."', `hp_all` = '".$li['hp_all']."', `en` = '".$li['en']."', `en_all` = '".$li['en_all']."', `speed_hod_all` = '".$li['speed_hod_all']."', `ruki` = '".$li['ruki']."', `kol_vo` = '".$li['kol_vo']."', `kol_vo_all` = '".$li['kol_vo_all']."', `vmest` = '".$li['vmest']."', `cost` = '".$li['cost']."', `k_stat` = '".$li['k_stat']."', `user` = '".$u['id']."';");
                            //отнимаем деньги
                            $sql->query("UPDATE `users` SET `money` = '".($u['money'] - ceil($li['cost']))."' WHERE `id` = '".$u['id']."'");
                            //создаём логи
                            $sql->query("INSERT INTO `users_logi` SET `user` = '".$u['id']."', `text` = '<font color=\"#0000FF\">Кредиты -".ceil($li['cost'])."</font>', `dtime` = '".date("H:i")."'");
                            $sql->query("INSERT INTO `users_logi` SET `user` = '".$u['id']."', `text` = 'Вы купили [".$li['title']."]', `dtime` = '".date("H:i")."'");

                            header('Location: ?a=shop&id='.$npc['id'].''); //кидаем назад
						} else { //если денег не хватает
							echo '<div class="text">Недостаточно средств</div>';
						}

					} else { //если предмет суммируется

						//если игрок нажал "купить"
						if (isset($_POST['shop'])) {
							//проверяем на введённые данные
							if(!preg_match('/^[0-9]{1,}$/iu', $_POST['kol_vo'])) {
								$kol_vo = 0;
							} else {
								$kol_vo = $_POST['kol_vo'];
							}

							$kol_vo = abs($kol_vo);

							if ($kol_vo > 0) { //если введён не 0
								$li_cost = ceil($li['cost'] * $kol_vo);

								//если денег хватает
								if ($u['money'] >= $li_cost) {
									//проверяем, есть ли такой предмет в рюкзаке
	                                $bag_shmot_cop_sql = $sql->query("SELECT * FROM `users_bag` WHERE `user` = '".$u['id']."' AND `title` = '".$li['title']."' AND `type` = '".$li['type']."'");
	                                $bag_shmot_cop_all = $bag_shmot_cop_sql->num_rows;

	                                if ($bag_shmot_cop_all == 0) { //если такого предмета в рюкзаке нет
	                                    //добавляем в рюкзак
	                                    $sql->query("INSERT INTO `users_bag` SET `lvl` = '".$li['lvl']."', `title` = '".$li['title']."', `type` = '".$li['type']."', `t_naw` = '".$li['t_naw']."', `t_naw_lvl` = '".$li['lvl']."', `speed_att` = '".$li['speed_att']."', `speed_att_all` = '".$li['speed_att_all']."', `kalibr` = '".$li['kalibr']."', `patron` = '".$li['patron']."', `patron_all` = '".$li['patron_all']."', `att` = '".$li['att']."', `att_all` = '".$li['att_all']."', `rej_str` = '".$li['rej_str']."', `rej_str_all` = '".$li['rej_str_all']."', `radius_att` = '".$li['radius_att']."', `pr` = '".$li['pr']."', `pr_all` = '".$li['pr_all']."', `ves` = '".$li['ves']."', `def` = '".$li['def']."', `str` = '".$li['str']."', `agi` = '".$li['agi']."', `dex` = '".$li['dex']."', `hp` = '".$li['hp']."', `hp_all` = '".$li['hp_all']."', `en` = '".$li['en']."', `en_all` = '".$li['en_all']."', `speed_hod_all` = '".$li['speed_hod_all']."', `ruki` = '".$li['ruki']."', `kol_vo` = '".$kol_vo."', `kol_vo_all` = '".$li['kol_vo_all']."', `vmest` = '".$li['vmest']."', `cost` = '".$li['cost']."', `k_stat` = '".$li['k_stat']."', `user` = '".$u['id']."';");
	                                } else { //если есть
	                                    $bag_shmot_cop = $bag_shmot_cop_sql->fetch_array(MYSQLI_ASSOC);
	                                    //добавляем в рюкзак
	                                    $sql->query("UPDATE `users_bag` SET `kol_vo` = '".($bag_shmot_cop['kol_vo'] + $kol_vo)."' WHERE `id` = '".$bag_shmot_cop['id']."'");
	                                }

	                                //отнимаем деньги
	                                $sql->query("UPDATE `users` SET `money` = '".($u['money'] - $li_cost)."' WHERE `id` = '".$u['id']."'");
	                                //создаём логи
	                                $sql->query("INSERT INTO `users_logi` SET `user` = '".$u['id']."', `text` = '<font color=\"#0000FF\">Кредиты -".$li_cost."</font>', `dtime` = '".date("H:i")."'");
	                                $sql->query("INSERT INTO `users_logi` SET `user` = '".$u['id']."', `text` = 'Вы купили [".$li['title']."] [x".$kol_vo."]', `dtime` = '".date("H:i")."'");

	                                header('Location: ?a=shop&id='.$npc['id'].''); //кидаем назад
	                            } else { //если денег не хватает
	                            	echo '<div class="text">';
				                    echo '<font color="#FF0000">&times; Не достаточно средств</font>';
				                    echo '</div>';
				                    echo '<div class="line"></div>';
	                            }

							}

						}

						//форма покупки
						echo '<form method="post" action="">';
						echo 'Сколько?<br/>';
						echo '<input type="number" size="10" name="kol_vo" value="1"/><br/>';
						echo '<input type="submit" name="shop" value="Купить"/>';
						echo '</form>';

					}


				} else {
					echo '<div class="text">Предмет не найден</div>';
				}

				echo '<div class="line"></div>';
				echo '<ul class="links">';
				echo '<li><a href="?a=shop&id='.$npc['id'].'">Покупка вещей</a></li>';
				echo '</ul>';
				echo '<div class="line"></div>';
				echo '<div class="foot_a">';
				echo '<a href="./">Уйти</a>';
				echo '</div>';

			} else { //если нпц не торгует
	        	header('Location: ?id='.$npc['id'].''); //кидаем назад
	        }

			break;
	        //покупка вещей.конец


		}

	} else { //если нету
		header('Location: ./'); //бросаем на главную
	}

} else { //если не игрок
	header('Location: ./');
}

include './foot.php';
?>