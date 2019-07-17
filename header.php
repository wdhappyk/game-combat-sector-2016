<?php
defined('cms') or die('Error:restricted access');
session_start();
error_reporting(E_ALL); //
ob_start(); //стартуем сессии
Error_Reporting(E_ALL & ~E_NOTICE); //включаем показ ошибок
date_default_timezone_set('Europe/Moscow'); //устанавливаем Московское время

//загрузка страницы(время)
function gettime() {
	$time = explode(" ",microtime());
	return ((float)$time[0]+(float)$time[1]);
}

$gettime = gettime();

//конект к бд
$db_host = 'localhost'; //сервер БД
$db_user = ''; //Пользователь БД
$db_pass = ''; //Пароль БД
$db_name = ''; //Имя БД
$sql = new mysqli($db_host, $db_user, $db_pass, $db_name);

$conf = $sql->query("SELECT * FROM `conf`")->fetch_array(MYSQLI_ASSOC);

//проверяем на авторизацию
if ($conf['server'] != 0) { //если сервер включен
	if (isset($_COOKIE['userID']) && isset($_COOKIE['userMAIL'])) {
		$userSQL = $sql->query("SELECT * FROM `users` WHERE `id` = '".$_COOKIE['userID']."' AND `mail` = '".$_COOKIE['userMAIL']."'");
		$userRows = $userSQL->num_rows;

		if ($userRows != 0) {
			$uid = $userSQL->fetch_array(MYSQLI_ASSOC);
			$user = $uid['id'];
		} else {
			$user = false;
		}

	} else if (isset($_SESSION['userID']) && isset($_SESSION['userMAIL'])) {
		$userSQL = $sql->query("SELECT * FROM `users` WHERE `id` = '".$_SESSION['userID']."' AND `mail` = '".$_SESSION['userMAIL']."'");
		$userRows = $userSQL->num_rows;

		if ($userRows != 0) {
			$uid = $userSQL->fetch_array(MYSQLI_ASSOC);
			$user = $uid['id'];
		} else {
			$user = false;
		}

	} else {
		$user = false;
	}
}

echo '<head>';
echo '<meta http-equiv="content-type" content="text/html;charset=utf-8"/>';
echo '<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">';
echo '<title>'.$conf['title_game'].'</title>';
echo '<link rel="stylesheet" type="text/css" href="./style.css">';
echo '<link rel="icon" href="./img/logo_index.png" type="image/x-icon">';
echo '</head>';

echo '<body>';
echo '<div class="body">';
echo '<div class="header">';
echo '<center>'.$conf['title_game'].'</center>';
echo '</div>';
echo '<div class="body_bg">';

if ($user) { //если игрок
	//смотрим игрока
	$u = $sql->query("SELECT * FROM `users` WHERE `id` = '".$user."'")->fetch_array(MYSQLI_ASSOC);

	//функция воскрешения игрока
	function user_death($sql, $u) {
		//вычисляем сколько опыта должен потерять игрок
		$exp_minus = round(($u['exp'] * 0.25)); //25% опыта будет терять игрок
		//смотрим место спауна
		$spaun_k = $sql->query("SELECT * FROM `locations_spaun` WHERE `loc` = '".$u['loc']."'")->fetch_array(MYSQLI_ASSOC);
		//воскрешаем игрока
		$sql->query("UPDATE `users` SET `x` = '".$spaun_k['x']."', `y` = '".$spaun_k['y']."', `death_on` = '0', `hp` = '".(round(($u['hp_all'] * 0.1)))."', `en` = '".(round(($u['en_all'] * 0.5)))."', `exp` = '".($u['exp'] - $exp_minus)."' WHERE `id` = '".$u['id']."'");
		//создаём лог
		$sql->query("INSERT INTO `users_logi` SET `user` = '".$u['id']."', `text` = '<font color=\"#FFFF00\">Вы воскресли</font>', `dtime` = '".date("H:i")."'");
		if ($exp_minus != 0) $sql->query("INSERT INTO `users_logi` SET `user` = '".$u['id']."', `text` = '<font color=\"#0000FF\">Опыт -".$exp_minus."</font>', `dtime` = '".date("H:i")."'");

	}

	//если игрок мёртв
	if ($u['death_on'] != 0) {
		user_death($sql, $u); //воскрешаем
		header('Location: ./'); //обновляем страницу
		exit(); //останавливаем выполнение скрипта
	}

	//регенирация игрока|происходит раз в 10 секунд
	$regen_time = time() - $u['regen_time'];

	if ($regen_time >= 10) {
		//множитель регенерации
		$mn_regen = floor($regen_time / 10);
		//регенерируем 3% хп и 1% энергии
        $mn_hp = round(($u['hp_all'] * 0.03));
        $mn_en = round(($u['en_all'] * 0.01));
        //регулируем
        if ($mn_hp == 0) $mn_hp = 1;
        if ($mn_en == 0) $mn_en = 1;
        //прибавляем
		$regen_hp = $u['hp'] + ($mn_hp * $mn_regen);
		$regen_en = $u['en'] + ($mn_en * $mn_regen);
		//регулируем
		if ($regen_hp > $u['hp_all']) $regen_hp = $u['hp_all'];
		if ($regen_en > $u['en_all']) $regen_en = $u['en_all'];
		//стартуем время
		//смотрим сколько времени не хватило до ещё 1 пополнения
		$o_regen_time = round((($regen_time % 10) * 10));
		//добавляем игроку
		$sql->query("UPDATE `users` SET `regen_time` = '".(time() - $o_regen_time)."', `hp` = '".$regen_hp."', `en` = '".$regen_en."' WHERE `id` = '".$u['id']."'");

		//обновляем инфу о игроке во избежание смерти
		$u = $sql->query("SELECT * FROM `users` WHERE `id` = '".$user."'")->fetch_array(MYSQLI_ASSOC);
	}
	//регенерация игрока.конец

    //заагривание агресивынх ботов
    //смотрим максимайльный радиус агресии
    $max_radius_agr = $sql->query("SELECT * FROM `bots` WHERE `loc` = '".$u['loc']."' ORDER BY `radius_agr` DESC")->fetch_array(MYSQLI_ASSOC);
    //смотрим агресивных ботов вокруг игрока
    $agress_bots_sql = $sql->query("SELECT * FROM `bots` WHERE `type` = '1' AND `lvl` >= '".($u['lvl'] - 5)."' AND `death_on` = '0' AND `cell_target_id` = '0' AND `loc` = '".$u['loc']."' AND `x` >= '".($u['x'] - $max_radius_agr['radius_agr'])."' AND `x` <= '".($u['x'] + $max_radius_agr['radius_agr'])."' AND `y` >= '".($u['y'] - $max_radius_agr['radius_agr'])."' AND `y` <= '".($u['y'] + $max_radius_agr['radius_agr'])."'");
    $agress_bots_all = $agress_bots_sql->num_rows;
    //если есть
    if ($agress_bots_all != 0) {
        //смотрим бота
        $rba = $agress_bots_sql->fetch_array(MYSQLI_ASSOC);
        //заагриваем бота
        $sql->query("UPDATE `bots` SET `cell_target_type` = 'users', `cell_target_id` = '".$u['id']."' WHERE `id` = '".$rba['id']."'");
    }
    //заагривание агресивых ботов.конец
	
	//атака игрока ботом
    //функция погони за игроком/подхода к игроку
    function bots_beg_za_us($sql, $u, $bid, $bx, $by, $b_speed_hod, $b_speed_hod_all) {
        //если бот уже может бежать
        if (($b_speed_hod - (time() - $b_speed_hod_all)) <= 0) {
            //изменение координат в зависимости от положения кого-либо
            if ($bx > $u['x']) {
                $stena_po_x_all = $sql->query("SELECT * FROM `locations_predmet` WHERE `strike_off` = '1' AND `loc` = '".$u['loc']."' AND `x` = '".($bx - 1)."' AND `y` = '".$by."'")->num_rows;

                if ($stena_po_x_all == 0) $bx--;

            } else if ($bx < $u['x']) {
                $stena_po_x_all = $sql->query("SELECT * FROM `locations_predmet` WHERE `strike_off` = '1' AND `loc` = '".$u['loc']."' AND `x` = '".($bx + 1)."' AND `y` = '".$by."'")->num_rows;

                if ($stena_po_x_all == 0) $bx++;

            }

            if ($by > $u['y']) {
                $stena_po_y_all = $sql->query("SELECT * FROM `locations_predmet` WHERE `strike_off` = '1' AND `loc` = '".$u['loc']."' AND `x` = '".$bx."' AND `y` = '".($by - 1)."'")->num_rows;

                if ($stena_po_y_all == 0) $by--;

            } else if ($by < $u['y']) {
                $stena_po_y_all = $sql->query("SELECT * FROM `locations_predmet` WHERE `strike_off` = '1' AND `loc` = '".$u['loc']."' AND `x` = '".$bx."' AND `y` = '".($by + 1)."'")->num_rows;

                if ($stena_po_y_all == 0) $by++;

            }

            //меняем координаты бота если игрок смог сдвинуться и запускаем таймер
            if ($stena_po_x_all == 0 || $stena_po_y_all == 0) $sql->query("UPDATE `bots` SET `speed_hod` = '".time()."', `x` = '".$bx."', `y` = '".$by."' WHERE `id` = '".$bid."'");
        
        }

    }

	//смотрим есть ли заагренные боты
	$bots_agress_sql = $sql->query("SELECT * FROM `bots` WHERE `death_on` = '0' AND `loc` = '".$u['loc']."' AND `cell_target_id` = '".$u['id']."' AND `cell_target_type` = 'users'");
	$bots_agress_all = $bots_agress_sql->num_rows;

	//если агресивные боты есть
	if ($bots_agress_all != 0) {
		//атака | agb - agress bot
		while ($agb = $bots_agress_sql->fetch_array(MYSQLI_ASSOC)) {
            //если цель в радиусе агресии
            if ($u['x'] >= ($agb['x'] - $agb['radius_agr']) && $u['x'] <= ($agb['x'] + $agb['radius_agr']) && $u['y'] >= ($agb['y'] - $agb['radius_agr']) && $u['y'] <= ($agb['y'] + $agb['radius_agr'])) {
    			//если цель в радиусе атаки
           		if ($u['x'] >= ($agb['x'] - $agb['radius_att']) && $u['x'] <= ($agb['x'] + $agb['radius_att']) && $u['y'] >= ($agb['y'] - $agb['radius_att']) && $u['y'] <= ($agb['y'] + $agb['radius_att'])) {
           			//если откат произошёл, т.е. бот уже может атаковать
           			if (($agb['speed_att'] - (time() - $agb['speed_att_all'])) <= 0) {
           				//если бот дальнобойный, смотрим путь удара
           				//отмечаем что препятствий нет
           				$b_put_att = 0; //0 - нет
           				//смотрим путь удара
           				if ($agb['radius_att'] > 1) {
           					//отмечаем начальные координаты удара
           					$udar_x = $agb['x'];
           					$udar_y = $agb['y'];
           					//измеряем сколько ходов должен делать удар
                            if (abs((abs($agb['x']) - abs($u['x']))) >= abs((abs($agb['y']) - abs($u['y'])))) {
                            	$put_att = abs((abs($agb['x']) - abs($u['x'])));
                            } else {
                            	$put_att = abs((abs($agb['y']) - abs($u['y'])));
                          	}
                          	//смотрим путь
                            for ($p_ud = 1; $p_ud <= $put_att; $p_ud++) {
                            	//меняем координаты пули в зависимости от положения противника
                                if ($udar_x < $u['x']) {
                                    $udar_x++;
                                } else if ($udar_x > $u['x']) {
                                    $udar_x--;
                                }

                                if ($udar_y < $u['y']) {
                                    $udar_y++;
                                } else if ($udar_y > $u['y']) {
                                    $udar_y--;
                                }
                                //наконец смотрим стену/преграду через которую нельзя стрелять
                                $b_put_att_all = $sql->query("SELECT * FROM `locations_predmet` WHERE `loc` = '".$u['loc']."' AND `x` = '".$udar_x."' AND `y` = '".$udar_y."' AND `strike_off` = '1'")->num_rows;
                                //если преграда есть, говорим что есть
                                if ($b_put_att_all != 0) {
                                    $b_put_att++;
                                    //выходим из проверки, т.к. дальше смотреть бессмысленно
                                    break 1; //выходим из for
                                }

                            }

                        }
                        //смотрим путь.конец

                        //если препятствий нет
                        if ($b_put_att == 0) {
                          	//расчитываем урон
                            $rand_b_uron = rand($agb['att'], $agb['att_all']);
                            //расчитываем статы противника
                            $u_dodge = round(($u['agi'] / 10), 2) * 100; //уворот из 1000
                            $u_armor = round(($u['def'] / 10), 5); //ед урона блокируем

                            //статы бота
                           	$agb_dex = round(($agb['dex'] * 10), 1) + 6000; //шанс попадания из 10000
                            $damage_po_us = $rand_b_uron - $u_armor; //расчитываем окончательный урон

                            if ($damage_po_us <= 0) $damage_po_us = 0;

                            if (rand(0, 10000) <= $agb_dex) { //если попал

                                if (rand(0, 1000) > $u_dodge) { //если не увернулся

                                	$death_us_po_hp = $u['hp'] - $damage_po_us; //смотрим сколько хп останется у противника после атаки

                                    if ($death_us_po_hp <= 0) { //если 0 или меньше
                                        $damage_po_us = $u['hp']; //ставим столько сколько было у цели хп
                                        $us_death_on = 1; //убиваем цель
                                    } else {
                                    	$us_death_on = 0;
                                    }

                                    //снимаем противнику ХП
                                    $sql->query("UPDATE `users` SET `hp` = '".($u['hp'] - round($damage_po_us))."' WHERE `id` = '".$u['id']."'");
                                    //создаём лог
                                    $sql->query("INSERT INTO `users_logi` SET `user` = '".$u['id']."', `text` = '<font color=\"#FF0000\">[".$agb['name']."] нанёс вам ".round($damage_po_us)." ед. урона</font>', `dtime` = '".date("H:i")."'");

                                    //если бот убил игрока своим ударом
                                    if ($us_death_on != 0) {
                                        //убиваем игрока и добавляем PvE поражение и скидываем таргет
                                        $sql->query("UPDATE `users` SET `PvE_lose` = '".($u['PvE_lose'] + 1)."', `death_on` = '".$us_death_on."', `death_time` = '".time()."', `cell_target_type` = '', `cell_target_id` = '0' WHERE `id` = '".$u['id']."'");
                                        //создаём лог
                                        $sql->query("INSERT INTO `users_logi` SET `user` = '".$u['id']."', `text` = '<font color=\"#FF0000\">[".$agb['name']."] убил вас</font>', `dtime` = '".date("H:i")."'");
                                        //выкидываем шмот на локу
                                        $lut_us_in_bag_sql = $sql->query("SELECT * FROM `users_bag` WHERE `user` = '".$u['id']."' AND `type` >= '11'");
                                        $lut_us_in_bag_all = $lut_us_in_bag_sql->num_rows;
                                        //отнимаем у всех одетых вещей по 1 прочности
                                        $eq_on_us_sql = $sql->query("SELECT * FROM `users_bag` WHERE `pr` > '0' AND `ek` != '0' AND `user` = '".$u['id']."'");
                                        $eq_on_us_all = $eq_on_us_sql->num_rows;

                                        if ($eq_on_us_all != 0) {
                                            while ($eqw = $eq_on_us_sql->fetch_array(MYSQLI_ASSOC)) {
                                                $sql->query("UPDATE `users_bag` SET `pr` = '".($eqw['pr'] - 1)."' WHERE `id` = '".$eqw['id']."'");
                                            }
                                        }

                                        //выкидываем
                                        while ($luib = $lut_us_in_bag_sql->fetch_array(MYSQLI_ASSOC)) {
                                        	//если шмот не суммируется
                                            if ($luib['kol_vo_all'] == 0) { //если предмет не суммируется
                                                //добавляем на локу
                                                $sql->query("INSERT INTO `locations_shmots` SET `lvl` = '".$luib['lvl']."', `title` = '".$luib['title']."', `type` = '".$luib['type']."', `t_naw` = '".$luib['t_naw']."', `t_naw_lvl` = '".$luib['lvl']."', `speed_att` = '".$luib['speed_att']."', `speed_att_all` = '".$luib['speed_att_all']."', `kalibr` = '".$luib['kalibr']."', `patron` = '".$luib['patron']."', `patron_all` = '".$luib['patron_all']."', `att` = '".$luib['att']."', `att_all` = '".$luib['att_all']."', `rej_str` = '".$luib['rej_str']."', `rej_str_all` = '".$luib['rej_str_all']."', `radius_att` = '".$luib['radius_att']."', `pr` = '".$luib['pr']."', `pr_all` = '".$luib['pr_all']."', `ves` = '".$luib['ves']."', `def` = '".$luib['def']."', `str` = '".$luib['str']."', `agi` = '".$luib['agi']."', `dex` = '".$luib['dex']."', `hp` = '".$luib['hp']."', `hp_all` = '".$luib['hp_all']."', `en` = '".$luib['en']."', `en_all` = '".$luib['en_all']."', `speed_hod_all` = '".$luib['speed_hod_all']."', `ruki` = '".$luib['ruki']."', `kol_vo` = '".$luib['kol_vo']."', `kol_vo_all` = '".$luib['kol_vo_all']."', `vmest` = '".$luib['vmest']."', `cost` = '".$luib['cost']."', `k_stat` = '".$luib['k_stat']."', `loc` = '".$u['loc']."', `x` = '".$u['x']."', `y` = '".$u['y']."';");
                                                //удаляем шмот из рюкзака и создаём лог
                                                $sql->query("DELETE FROM `users_bag` WHERE `id` = '".$luib['id']."' LIMIT 1"); //удаляем
                                                $sql->query("INSERT INTO `users_logi` SET `user` = '".$u['id']."', `text` = 'Вы потеряли [".$luib['title']."]', `dtime` = '".date("H:i")."'");
                                                	
                                            } else { //если суммируется
                                                //проверяем, есть ли такой предмет на локе
                                                $loc_shmot_cop_sql = $sql->query("SELECT * FROM `locations_shmots` WHERE `loc` = '".$u['loc']."' AND `x` = '".$u['x']."' AND `y` = '".$u['y']."' AND `title` = '".$luib['title']."' AND `type` = '".$luib['type']."'");
                                                $loc_shmot_cop_all = $loc_shmot_cop_sql->num_rows;

                                                if ($loc_shmot_cop_all == 0) { //если такого предмета в рюкзаке нет
                                                    //добавляем на локу
                                                    $sql->query("INSERT INTO `locations_shmots` SET `lvl` = '".$luib['lvl']."', `title` = '".$luib['title']."', `type` = '".$luib['type']."', `t_naw` = '".$luib['t_naw']."', `t_naw_lvl` = '".$luib['lvl']."', `speed_att` = '".$luib['speed_att']."', `speed_att_all` = '".$luib['speed_att_all']."', `kalibr` = '".$luib['kalibr']."', `patron` = '".$luib['patron']."', `patron_all` = '".$luib['patron_all']."', `att` = '".$luib['att']."', `att_all` = '".$luib['att_all']."', `rej_str` = '".$luib['rej_str']."', `rej_str_all` = '".$luib['rej_str_all']."', `radius_att` = '".$luib['radius_att']."', `pr` = '".$luib['pr']."', `pr_all` = '".$luib['pr_all']."', `ves` = '".$luib['ves']."', `def` = '".$luib['def']."', `str` = '".$luib['str']."', `agi` = '".$luib['agi']."', `dex` = '".$luib['dex']."', `hp` = '".$luib['hp']."', `hp_all` = '".$luib['hp_all']."', `en` = '".$luib['en']."', `en_all` = '".$luib['en_all']."', `speed_hod_all` = '".$luib['speed_hod_all']."', `ruki` = '".$luib['ruki']."', `kol_vo` = '".$luib['kol_vo']."', `kol_vo_all` = '".$luib['kol_vo_all']."', `vmest` = '".$luib['vmest']."', `cost` = '".$luib['cost']."', `k_stat` = '".$luib['k_stat']."', `loc` = '".$u['loc']."', `x` = '".$u['x']."', `y` = '".$u['y']."';");
                                                } else { //если есть
                                                    $loc_shmot_cop = $loc_shmot_cop_sql->fetch_array(MYSQLI_ASSOC);
                                                    //добавляем в рюкзак
                                                    $sql->query("UPDATE `locations_shmots` SET `kol_vo` = '".($loc_shmot_cop['kol_vo'] + $luib['kol_vo'])."' WHERE `id` = '".$loc_shmot_cop['id']."'");
                                                }

                                                //удаляем шмот из рюкзака и создаём лог
                                                $sql->query("DELETE FROM `users_bag` WHERE `id` = '".$luib['id']."' LIMIT 1"); //удаляем
                                                $sql->query("INSERT INTO `users_logi` SET `user` = '".$u['id']."', `text` = 'Вы потеряли [".$luib['title']."] [x".$luib['kol_vo']."]', `dtime` = '".date("H:i")."'");

                                            }
                                            //добавление на локу.конец

                                        }
                                        //скидываем ботам таргет
                                        $sql->query("UPDATE `bots` SET `cell_target_type` = '', `cell_target_id` = '0' WHERE `cell_target_type` = 'users' AND `cell_target_id` = '".$u['id']."'");

                                        break 1; //выходим из цикла атаки
                                    }
                                        
                                } else { //если увернулся
                                	//создаём лог
                            		$sql->query("INSERT INTO `users_logi` SET `user` = '".$u['id']."', `text` = '<font color=\"#00FF00\">Вы увернулись от атаки [".$agb['name']."]</font>', `dtime` = '".date("H:i")."'");
                                
                                    //прокачиваем навык который качается при уворотах
                                    $naw_dodge_sql = $sql->query("SELECT * FROM `users_naw` WHERE `kach_ot` = 'dodge' AND `user` = '".$u['id']."'");
                                    $naw_dodge_all = $naw_dodge_sql->num_rows;

                                    if ($naw_dodge_all != 0) {
                                        $naw = $naw_dodge_sql->fetch_array(MYSQLI_ASSOC);

                                        if ($naw['lvl'] <= $agb['lvl']) {
                                            $rand_exp = rand(1, 99) / 100;
                                            $us_naw_exp_sql = $sql->query("SELECT * FROM `table_lvl_naw` WHERE `id` = '".$naw['lvl']."'");
                                            $us_naw_exp_all = $us_naw_exp_sql->num_rows;
                                            //если левл повысить можно
                                            if ($us_naw_exp_all != 0) {
                                                //подключаеся к таблице
                                                $us_naw_exp = $us_naw_exp_sql->fetch_array(MYSQLI_ASSOC);
                                                //если уровень пыта привысил шкалу
                                                if (($naw['exp'] + $rand_exp) >= $us_naw_exp['exp']) {

                                                    if ($naw['lvl'] < $u['lvl']) {
                                                        //повышаем уровень
                                                        $sql->query("UPDATE `users_naw` SET `lvl` = '".($naw['lvl'] + 1)."' WHERE `id` = '".$naw['id']."'");
                                                        //увеличиваем статы
                                                        $sql->query("UPDATE `users` SET `".$naw['kach_stat']."` = '".($u[$naw['kach_stat']] + $naw['kach_stat_plus'])."' WHERE `id` = '".$u['id']."'");
                                                        //создаём лог
                                                        $sql->query("INSERT INTO `users_logi` SET `user` = '".$u['id']."', `text` = '<font color=\"#0000FF\">Уровень навыка [".$naw['title']."] повышен</font>', `dtime` = '".date("H:i")."'");
                                                    } else {

                                                        if ($naw['exp'] < $us_naw_exp['exp']) { //поднимаем опыт до максимума если уже не поднято
                                                            $sql->query("UPDATE `users_naw` SET `exp` = '".$us_naw_exp['exp']."' WHERE `id` = '".$naw['id']."'");
                                                            //создаём лог
                                                            $sql->query("INSERT INTO `users_logi` SET `user` = '".$u['id']."', `text` = '<font color=\"#0000FF\">Навык [".$naw['title']."] + ".$rand_exp."</font>', `dtime` = '".date("H:i")."'");
                                                        }

                                                    }

                                                } else { //если ещё не привысил
                                                    //прибавляем если уровень навыка меньше или равен уровню игрока
                                                    if ($naw['lvl'] <= $u['lvl']) {
                                                        $sql->query("UPDATE `users_naw` SET `exp` = '".($naw['exp'] + $rand_exp)."' WHERE `id` = '".$naw['id']."'");
                                                        //создаём лог
                                                        $sql->query("INSERT INTO `users_logi` SET `user` = '".$u['id']."', `text` = '<font color=\"#0000FF\">Навык [".$naw['title']."] + ".$rand_exp."</font>', `dtime` = '".date("H:i")."'");
                                                    }

                                                }

                                            }

                                        }

                                    }

                                }

                            } else { //если не попал
                            	//создаём лог
                            	$sql->query("INSERT INTO `users_logi` SET `user` = '".$u['id']."', `text` = '<font color=\"#00FF00\">[".$agb['name']."] не попал по вам</font>', `dtime` = '".date("H:i")."'");
                            }

                        } else { //если припятствия есть
                        	//если радиус атаки > 2кл. подбегаем ближе к игроку
                            if ($agb['radius_att'] > 2) bots_beg_za_us($sql, $u, $agb['id'], $agb['x'], $agb['y'], $agb['speed_hod'], $agb['speed_hod_all']); 
                    	}

           				//запускаем боту таймер на атаку
           				$sql->query("UPDATE `bots` SET `speed_att` = '".time()."' WHERE `id` = '".$agb['id']."'");

           			}

           		} else { //если цель далеко
                    //бежим за целью
                    bots_beg_za_us($sql, $u, $agb['id'], $agb['x'], $agb['y'], $agb['speed_hod'], $agb['speed_hod_all']);
           		}

            } else { //если цель за радиусом агресии
                //скидываем таргет боту
                $sql->query("UPDATE `bots` SET `cell_target_type` = '', `cell_target_id` = '0' WHERE `id` = '".$agb['id']."'");
            }

		}

		//обновляем инфу о игроке
		$u = $sql->query("SELECT * FROM `users` WHERE `id` = '".$user."'")->fetch_array(MYSQLI_ASSOC);

		//если игрок мёртв
		if ($u['death_on'] != 0) {
			user_death($sql, $u); //воскрешаем
			header('Location: ./'); //обновляем страницу
			exit(); //останавливаем выполнение скрипта
		}

	}
	//атака игрока ботом.конец

    //воскрешение мёртвых ботов
    $death_bot_sql = $sql->query("SELECT * FROM `bots` WHERE `death_on` = '1'");
    $death_bot_all = $death_bot_sql->num_rows;

    if ($death_bot_all != 0) {

        while ($dbs = $death_bot_sql->fetch_array(MYSQLI_ASSOC)) {

            if (($dbs['death_time'] + $dbs['spaun_time_all']) < time()) {

                $sql->query("UPDATE `bots` SET `hp` = '".$dbs['hp_all']."', `death_on` = '0', `cell_target_id` = '0', `cell_target_type` = '', `x` = '".$dbs['spaun_x']."', `y` = '".$dbs['spaun_y']."' WHERE `id` = '".$dbs['id']."'");

            }

        }

    }
    //воскрешение мёртвых ботов.конец

    //снятие сломанных вещей
    $ek_shmot_pr_zero_sql = $sql->query("SELECT * FROM `users_bag` WHERE `pr` = '0' AND `ek` != '0' AND `user` = '".$u['id']."'");
    $ek_shmot_pr_zero_all = $ek_shmot_pr_zero_sql->num_rows;

    if ($ek_shmot_pr_zero_all != 0) {
        //снимаем
        while ($sep = $ek_shmot_pr_zero_sql->fetch_array(MYSQLI_ASSOC)) {

            if ($sep['type'] >= 3) { //если оружие

                if ($sep['ek'] == 1) { //если оружие в правой
                    $unEkR = 0;
                    $unEkL = $u['equip_weapon_l'];
                } else if ($sep['ek'] == 2) { //если в левой
                    $unEkR = $u['equip_weapon_r'];
                    $unEkL = 0;
                } else { //если двуручное
                    $unEkR = 0;
                    $unEkL = 0;
                }

                //снимаем и уменьшаем статы
                $sql->query("UPDATE `users` SET `equip_weapon_r` = '".$unEkR."', `equip_weapon_l` = '".$unEkL."', `def` = '".($u['def'] - $sep['def'])."', `str` = '".($u['str'] - $sep['str'])."', `agi` = '".($u['agi'] - $sep['agi'])."', `dex` = '".($u['dex'] - $sep['dex'])."', `hp_all` = '".($u['hp_all'] - $sep['hp_all'])."', `en_all` = '".($u['en_all'] - $sep['en_all'])."', `speed_hod_all` = '".($u['speed_hod_all'] - $sep['speed_hod_all'])."' WHERE `id` = '".$u['id']."'");
                $sql->query("UPDATE `users_bag` SET `ek` = '0' WHERE `id` = '".$sep['id']."'"); //отмечаем это для рюкзака
                $sql->query("INSERT INTO `users_logi` SET `user` = '".$u['id']."', `text` = 'Предмет [".$sep['title']."] сломан', `dtime` = '".date("H:i")."'"); //создаём лог

            } else { //если броня

                if ($sep['ek'] == 1) { //если броня
                    $unEkA = 0;
                    $unEkJ = $u['equip_jilet'];
                } else { //если жилет
                    $unEkA = $u['equip_armor'];
                    $unEkJ = 0;
                }

                //снимаем и уменьшаем статы
                $sql->query("UPDATE `users` SET `equip_armor` = '".$unEkA."', `equip_jilet` = '".$unEkJ."', `def` = '".($u['def'] - $sep['def'])."', `str` = '".($u['str'] - $sep['str'])."', `agi` = '".($u['agi'] - $sep['agi'])."', `dex` = '".($u['dex'] - $sep['dex'])."', `hp_all` = '".($u['hp_all'] - $sep['hp_all'])."', `en_all` = '".($u['en_all'] - $sep['en_all'])."', `speed_hod_all` = '".($u['speed_hod_all'] - $sep['speed_hod_all'])."' WHERE `id` = '".$u['id']."'");
                $sql->query("UPDATE `users_bag` SET `ek` = '0' WHERE `id` = '".$sep['id']."'"); //отмечаем это для рюкзака
                $sql->query("INSERT INTO `users_logi` SET `user` = '".$u['id']."', `text` = 'Предмет [".$sep['title']."] сломан', `dtime` = '".date("H:i")."'"); //создаём лог

            }

        }

    }
    //снятие сломанных вещей.конец

	//панель статов
	$uEXP = $sql->query("SELECT * FROM `table_lvl` WHERE `id` = '".$u['lvl']."'")->fetch_array(MYSQLI_ASSOC);
	
	//процент опыта
	if ($uEXP['exp'] > 0) {
		$uEXPp = round((($u['exp'] / $uEXP['exp']) * 100));
	} else {
		$uEXPp = 0;
	}

	//процент здоровья
	if ($u['hp_all'] > 0 && $u['hp'] > 0) {
		$hpp = round((($u['hp'] / $u['hp_all']) * 100));
	} else {
		$hpp = 0;
	}

	//процент энергии
	if ($u['en_all'] > 0 && $u['en'] > 0) {
		$enp = round((($u['en'] / $u['en_all']) * 100));
	} else {
		$enp = 0;
	}

	$u_lvl = $u['lvl'];

	//lvl UP!
	if ($u['exp'] >= $uEXP['exp']) { //если набрали достаточно опыта
		//повышаем лвл и даём слот на навык и даём свободные статы
		$sql->query("UPDATE `users` SET `lvl` = '".($u['lvl'] + 1)."', `stats_points` = '".($u['stats_points'] + $uEXP['stats_points'])."', `slots_naw` = '".($u['slots_naw'] + $uEXP['slots_naw'])."' WHERE `id` = '".$u['id']."'");
		//создаём лог
		$sql->query("INSERT INTO `users_logi` SET `user` = '".$u['id']."', `text` = 'Вы получили новый уровень!', `dtime` = '".date("H:i")."'");
		//выравниваем процент
		$uEXPp = $uEXPp - 100;
		$u_lvl++;
        //обновляем инфу о игроке
        $u = $sql->query("SELECT * FROM `users` WHERE `id` = '".$user."'")->fetch_array(MYSQLI_ASSOC);
	}

    //онлайн
    $us_offline = $sql->query("SELECT * FROM `users` WHERE `online_timer` <= '".(time() - 180)."'");
    $us_off_all = $us_offline->num_rows;

    if ($us_off_all != 0) {

        while ($us_off = $us_offline->fetch_array(MYSQLI_ASSOC)) {
            $sql->query("UPDATE `users` SET `online` = '0' WHERE `id` = '".$us_off['id']."'");
        }

    }

    if ($u['online_timer'] <= (time() - 100) || $u['online'] == 0) {
        $sql->query("UPDATE `users` SET `online` = '1', `online_timer` = '".time()."' WHERE `id` = '".$u['id']."'");
    }
    //онлайн.конец

	//ник[уровень]   баланс
	echo '<div class="text">';
	echo $u['name'].' ['.$u_lvl.']';
	echo '<div style="float: right; text-align: right;">';
	echo '<img src="./img/money.png" width="13px" style="margin-bottom: -2px;"> '.$u['money'];
	echo '</div>';
	echo '</div>';

	//полоски статов
	echo '<div class="line"></div>';
	echo '<div style="padding: 0; margin: 0; width: auto; border-bottom: 1px solid #666;">';
	echo '<div style="padding: 0; margin: 0; height: 20px; max-width: '.$hpp.'%; background: #FF0000 url(/img/hp_p.png); background-size: 100% 100%;">';
	echo '<div style="position: absolute; padding: 0; margin-left: 10px;">Здоровье: '.$u['hp'].'/'.$u['hp_all'].'</div>';
	echo '</div>';
	echo '</div>';
	echo '<div style="padding: 0; margin: 0; width: auto; border-bottom: 1px solid #666;">';
	echo '<div style="padding: 0; margin: 0; height: 20px; max-width: '.$enp.'%; background: #0000FF url(/img/en_p.png); background-size: 100% 100%;">';
	echo '<div style="position: absolute; padding: 0; margin-left: 10px;">Энергия: '.$u['en'].'/'.$u['en_all'].'</div>';
	echo '</div>';
	echo '</div>';
	echo '<div style="padding: 0; margin: 0; width: auto;">';
	echo '<div style="padding: 0; margin: 0; height: 20px; max-width: '.$uEXPp.'%; background: silver url(/img/exp_p.png); background-size: 100% 100%;">';
	echo '<div style="position: absolute; padding: 0; margin-left: 10px;">Опыт: '.$u['exp'].'/'.$uEXP['exp'].'</div>';
	echo '</div>';
	echo '</div>';

	//навигация
	echo '<div class="line"></div>';
	echo '<div class="panelMenu">';
	echo '<span class="butMenu"><a href="./char.php">Персонаж</a></span>';
	echo '<span class="butMenu"><a href="./char.php?a=equip">Снаряжение</a></span>';
	echo '<span class="butMenu"><a href="./char.php?a=bag">Сумка</a></span>';
	echo '<span class="butMenu"><a href="./char.php?a=pda">PDA</a></span>';
	echo '</div>';

    //новые сообщения
    $new_mail_all = $sql->query("SELECT * FROM `users_mail` WHERE `reed` = '0' AND `user` = '".$u['name']."'")->num_rows;
    //если есть
    if ($new_mail_all != 0) {
        echo '<div style="margin-top: 12px;" class="panelMenu">';
        echo '<span class="butMenu"><a href="./char.php?a=mail">Почта + '.$new_mail_all.'</a></span>';
        echo '</div>';
    }

	echo '<div class="line"></div>';

}

?>