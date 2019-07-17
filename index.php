<?php
define('cms', 1);
require_once 'header.php';

$conf = $sql->query("SELECT * FROM `conf`")->fetch_array(MYSQLI_ASSOC);

if ($user) { //если авторизирован
    $u = $sql->query("SELECT * FROM `users` WHERE `id` = '".$user."'")->fetch_array(MYSQLI_ASSOC);
    $u_conf = $sql->query("SELECT * FROM `users_setting` WHERE `id` = '".$u['id']."'")->fetch_array(MYSQLI_ASSOC);

    switch($_GET['a']) {

        /*-----ЛОКАЦИЯ-----*/
        default:
        echo '<div class="menu">Локация</div>';

        /*меню оружия*/
        echo '<div class="text">';

        //показ оружия
        //это запускием по любому
        if ($u['target_weapon'] == 0) { //ели выбрана правая рука
            //указываем слот
            $ew_slot = 'Правая рука';
            $ew_table = 'equip_weapon_r';
        } else { //если выбрана левая рука
           //указываем слот
            $ew_slot = 'Левая рука';
            $ew_table = 'equip_weapon_l';
        }

        if ($u[$ew_table] != 0) { //если что-нибудь взято
            //смотрим оружие
            $ew = $sql->query("SELECT * FROM `users_bag` WHERE `id` = '".$u[$ew_table]."'")->fetch_array(MYSQLI_ASSOC);
            //отмечаем что слот не пуст
            $ewYes = 1;
        } else {
            //отмечаем что слот пуст
            $ewYes = 0;
        }

        //указываем название оружие, кол-во патрон и др.
        //создаём пустые переменные
        //называем оружие и указываем кол-во патрон
        $ew_title = '';
        $ew_patron = '';
        //указываем режим стрельбы
        $ew_rej_att = 1; //1 - кол-во ударов
        $ew_rej_att_title = 'удар';
        //изменение режима стрельбы
        $ew_rej_izm = '';
        //указываем радиус атаки
        $ew_radius_att = 1; //1 - начальный радиус
        //указываем радиус видимости
        $radius_vid = 3; //минимальный
        //указываем радиус атаки
        $ew_rad_att = 1; //начальный
        //переменная на ссылку с изменением слота, пока пусто
        $ew_izm = '';
        //скорость атаки
        $ew_speed_att = $u['speed_att'] - (time() - 1); //последняя атака - (время на данный момент - 1)
        //смотри, можно ли изменять слот
        //если взято оружие в левую и в правую руку и оно не двуручное или в руки вообще ничего не взято
        if ($u['equip_weapon_r'] != $u['equip_weapon_l'] || $u['equip_weapon_r'] == 0 && $u['equip_weapon_l'] == 0) {
            $ew_izm .= '[<a href="?weapon_slot_izm">изм</a>] '; //добавляем ссылку
        } else { //если в руках двуручное оружие
            $ew_slot = 'Руки';
        }

        if ($ewYes == 1) { //если в руках оружие
            //называем оружие и указываем кол-во патрон, состояние перед названим
            $ew_title = ': '.$ew['title'];
            //если оружие имеет магазин, указываем кол-во патрон
            if ($ew['patron_all'] != 0) $ew_patron = ' ['.$ew['patron'].'|'.$ew['patron_all'].']';
            //если оружие метательное
            if ($ew['type'] == 4) $ew_patron = ' [x'.$ew['kol_vo'].']';
            //указываем режим атаки если оружие не холодное
            if ($ew['rej_str'] != 0) $ew_rej_att = $ew['rej_str'];
            //указываем название режима стрельбы
            if ($ew['rej_str'] == 1) $ew_rej_att_title = 'одиночный'; //одиночный
            if ($ew['rej_str'] == 2) {
                $ew_rej_att_title = 'очередь(3)'; //очередь(3)
                $ew_rej_att = 3; //кол-во выстрелов
            }
            //указываем радиус видимости
            if ($ew['radius_att'] > 3) $radius_vid = $ew['radius_att'];
            //указываем радиус атаки
            $ew_rad_att = $ew['radius_att'];
            //указываем ссылку на изменение режима стрельбы
            if ($ew['rej_str_all'] == 2) $ew_rej_izm = '[<a href="?weapon_rej_izm">изм</a>] ';
            //если магазин не полный то добавляем ссылку на перезарядку
            if ($ew['patron'] < $ew['patron_all']) $ew_patron .= ' [<a href="?weapon_ref_patron">перезарядить</a>]';
            //скорость атаки
            $ew_speed_att = $ew['speed_att'] - (time() - $ew['speed_att_all']);  //последняя атака - (время на данный момент - 1)

            //функции связанные с оружием
            //функция перезарядки
            if (isset($_GET['weapon_ref_patron'])) { //если игрок захотел презарядить оружие
                //функция
                function weapon_ref_patron($sql, $ew, $u) {
                    //если патроны нужны
                    if ($ew['patron'] != $ew['patron_all']) {
                        //смотрим патроны
                        $ew_patron_sql = $sql->query("SELECT * FROM `users_bag` WHERE `user` = '".$u['id']."' AND `type` = '11' AND `title` = '".$ew['kalibr']."'");
                        $ew_patron_all = $ew_patron_sql->num_rows; //есть ли патроны

                        //если патроны в рюкзаке есть
                        if ($ew_patron_all != 0) {
                            //смотрим инфу о патронах
                            $ew_patron_db = $ew_patron_sql->fetch_array(MYSQLI_ASSOC);
                            $ew_tr_patron = $ew['patron_all'] - $ew['patron']; //смотрим сколько патронов нужно

                            //если патронов больше чем нужно
                            if ($ew_patron_db['kol_vo'] > $ew_tr_patron) {
                                //отнимаем патроны
                                $sql->query("UPDATE `users_bag` SET `kol_vo` = '".($ew_patron_db['kol_vo'] - $ew_tr_patron)."' WHERE `id` = '".$ew_patron_db['id']."'");
                            } else { //если патронов меньше или как раз
                                $ew_tr_patron = $ew_patron_db['kol_vo']; //меням
                                //удаляем патроны
                                $sql->query("DELETE FROM `users_bag` WHERE `id` = '".$ew_patron_db['id']."'");
                            }
                            //добавляем патроны в обойму
                            $sql->query("UPDATE `users_bag` SET `patron` = '".($ew['patron'] + $ew_tr_patron)."' WHERE `id` = '".$ew['id']."'");
                        } else { //если нужных патронов нет
                            //создаём лог
                            $sql->query("INSERT INTO `users_logi` SET `user` = '".$u['id']."', `text` = '<font color=\"#FF0000\">Нет боеприпасов</font>', `dtime` = '".date("H:i")."'");
                        }

                    } //если патроны нужны.закрывем

                }
                //функция.конец
                weapon_ref_patron($sql, $ew, $u); //запускаем функцию
                header('Location: ./'); //обновляем страницу
            }
            //функция перезарядки.конец
            //функция смены типа стрельбы
            if (isset($_GET['weapon_rej_izm'])) { //если игрок захотел сменить тип стрельбы
                //функция
                function weapon_rej_izm($sql, $ew) {
                    //если оружие имеет более 1 типа стрельбы
                    if ($ew['rej_str_all'] == 2) {
                        //если выбран одиночный тип стрельбы
                        if ($ew['rej_str'] == 1) {
                            $ew_rej_str_izm = 2; //меняем на очередь
                        } else { //если тип стрельбы - очередь(3)
                            $ew_rej_str_izm = 1; //меняем на одиночный
                        }
                        //меняем
                        $sql->query("UPDATE `users_bag` SET `rej_str` = '".$ew_rej_str_izm."' WHERE `id` = '".$ew['id']."'");
                    }
                }
                //функция.конец
                weapon_rej_izm($sql, $ew); //запускаем функцию
                header('Location: ./'); //обновляем страницу
            }
            //функция смены типа стрльбы.конец

        } //если в руках оружие.закрываем

        //выводим
        echo $ew_izm.''.$ew_slot.''.$ew_title.''.$ew_patron.'<br/>';
        echo $ew_rej_izm.'Режим атаки: '.$ew_rej_att_title;

        //функция смены слота
        if (isset($_GET['weapon_slot_izm'])) { //если игрок захотел сменить слот
            //функция
            function weapon_slot_izm($sql, $u) {
                if ($u['target_weapon'] == 0) { //если выбрана правая рука
                    $u_target_weapon = 1; //меняем на левую
                } else { // если выбрана левая рука
                    $u_target_weapon = 0; //меняем на правую
                }
                //меням
                $sql->query("UPDATE `users` SET `target_weapon` = '".$u_target_weapon."' WHERE `id` = '".$u['id']."'");
            }
            weapon_slot_izm($sql, $u); //запускаем функцию
            header('Location: ./'); //обновляем страницу
        }
        //функция смены слота.конец

        echo '</div>';
        /*меню оружия.конец*/

        echo '<div class="line"></div>';

        /*меню выбора целей*/
        echo '<div class="text">';

        //функция выбра цели
        function cell_target($sql, $u, $radius_vid, $table, $id) {
            //проверяем цель на существование
            $u_cell_target_all = $sql->query("SELECT * FROM `".$table."` WHERE `id` = '".$id."' AND `loc` = '".$u['loc']."' AND `x` >= '".($u['x'] - $radius_vid)."' AND `x` <= '".($u['x'] + $radius_vid)."' AND `y` >= '".($u['y'] - $radius_vid)."' AND `y` <= '".($u['y'] + $radius_vid)."'")->num_rows;

            //если такая цель существует
            if ($u_cell_target_all != 0) {
                //берём в таргет
                $sql->query("UPDATE `users` SET `cell_target_type` = '".$table."', `cell_target_id` = '".$id."' WHERE `id` = '".$u['id']."'");
            } else { //если цели не существует
                //создаём лог
                $sql->query("INSERT INTO `users_logi` SET `user` = '".$u['id']."', `text` = 'Цель не найдена', `dtime` = '".date("H:i")."'");
            }

        }
        //функция выбора цели.конец
        //запуск функции выбора цели
        //выбор игрока
        if (isset($_GET['loc_users_target']) || isset($_POST['loc_users_target'])) { //ели попытка выбора
            //достаём ID
            if (isset($_GET['loc_users_target'])) $loc_users_target_id = $_GET['loc_users_target'];
            if (isset($_POST['loc_users_target'])) $loc_users_target_id = $_POST['loc_users_select'];
            if ($loc_users_target_id != $u['id']) { //если игрок выбрал не себя
                cell_target($sql, $u, $radius_vid, 'users', $loc_users_target_id); //запускаем функцию
            }
            header('Location: ./'); //обновляем стр
        }
        //выбор бота
        if (isset($_GET['loc_bots_target']) || isset($_POST['loc_bots_target'])) { //если попытка выбора
            //достаём ID
            if (isset($_GET['loc_bots_target'])) $loc_bots_target_id = $_GET['loc_bots_target'];
            if (isset($_POST['loc_bots_target'])) $loc_bots_target_id = $_POST['loc_bots_select'];

            echo cell_target($sql, $u, $radius_vid, 'bots', $loc_bots_target_id); //запускаем функцию
            header('Location: ./'); //обновляем стр
        }
        //выбор шмота
        if (isset($_GET['loc_shmots_target']) || isset($_POST['loc_shmots_target'])) { //если была попытка выбора
            //достаём ID
            if (isset($_GET['loc_shmots_target'])) $loc_shmots_target_id = $_GET['loc_shmots_target'];
            if (isset($_POST['loc_shmots_target'])) $loc_shmots_target_id = $_POST['loc_shmots_select'];

            cell_target($sql, $u, $radius_vid, 'locations_shmots', $loc_shmots_target_id); //запускаем функцию
            header('Location: ./'); //обновляем стр
        }
        //выбор локационного предмета(для обыска)
        if (isset($_GET['loc_predmet_target'])) { //если была попытка выбора
            //смотрим, можно ли обыскивать предмет
            $loc_prdmet_obsk = $sql->query("SELECT * FROM `locations_predmet` WHERE `id` = '".$_GET['loc_predmet_target']."' AND `lut_on` = '1'")->num_rows;
            //если предмет можно обыскать
            if ($loc_prdmet_obsk != 0) cell_target($sql, $u, $radius_vid, 'locations_predmet', $_GET['loc_predmet_target']); //запускаем функцию
            header('Location: ./'); //обновляем стр
        }
        //запуск функции выбора цели.конец
        //функция сброса цели
        function cell_target_del($sql, $u) {
            //сбрасываем таргет
            $sql->query("UPDATE `users` SET `cell_target_id` = '0', `cell_target_type` = '' WHERE `id` = '".$u['id']."'");
        }
        //функция сброса цели.конец

        if ($u['cell_target_id'] == 0) { //если цель не выбрана
        
            $kol_vo_cell = 0; //отмечаем что целей нет

            //игроки
            $cell_loc_users_sql = $sql->query("SELECT * FROM `users` WHERE `id` != '".$u['id']."' AND `online` = '1' AND `death_on` = '0' AND `loc` = '".$u['loc']."' AND `x` >= '".($u['x'] - $radius_vid)."' AND `x` <= '".($u['x'] + $radius_vid)."' AND `y` >= '".($u['y'] - $radius_vid)."' AND `y` <= '".($u['y'] + $radius_vid)."'");
            $cell_loc_users_all = $cell_loc_users_sql->num_rows;

            if ($cell_loc_users_all != 0) { //если есть игроки
                $kol_vo_cell++; //отмечаем что цели есть

                echo '<form method="post" action="">';
                echo 'Игроки: <select name="loc_users_select">';

                while ($lui = $cell_loc_users_sql->fetch_array(MYSQLI_ASSOC)) { //создаём список
                    echo '<option value="'.$lui['id'].'">'.$lui['name'].'</option>';
                }

                echo '</select>';
                echo '<input type="submit" style="float: right;" name="loc_users_target" value="Выбрать"/>';
                echo '</form>';
            }
            //игроки.конец

            //боты/НПЦ
            $cell_loc_bots_sql = $sql->query("SELECT * FROM `bots` WHERE `death_on` = '0' AND `loc` = '".$u['loc']."' AND `x` >= '".($u['x'] - $radius_vid)."' AND `x` <= '".($u['x'] + $radius_vid)."' AND `y` >= '".($u['y'] - $radius_vid)."' AND `y` <= '".($u['y'] + $radius_vid)."'");
            $cell_loc_bots_all = $cell_loc_bots_sql->num_rows;

            if ($cell_loc_bots_all != 0) {//если есть боты
                $kol_vo_cell++; //отмечаем что цели есть

                echo '<form method="post" action="">';
                echo 'Боты: <select name="loc_bots_select">';

                while ($lbi = $cell_loc_bots_sql->fetch_array(MYSQLI_ASSOC)) { //создаём список
                    echo '<option value="'.$lbi['id'].'">'.$lbi['name'].'</option>';
                }

                echo '</select>';
                echo '<input type="submit" style="float: right;" name="loc_bots_target" value="Выбрать"/>';
                echo '</form>';
            }
            //боты/НПЦ.конец

            //выброшенные предметы
            $cell_loc_shmots_sql = $sql->query("SELECT * FROM `locations_shmots` WHERE `loc` = '".$u['loc']."' AND `x` >= '".($u['x'] - $radius_vid)."' AND `x` <= '".($u['x'] + $radius_vid)."' AND `y` >= '".($u['y'] - $radius_vid)."' AND `y` <= '".($u['y'] + $radius_vid)."'");
            $cell_loc_shmots_all = $cell_loc_shmots_sql->num_rows;

            if ($cell_loc_shmots_all != 0) { //если есть выброшенные предметы
                $kol_vo_cell++; //отмечам что цели есть

                echo '<form method="post" action="">';
                echo 'Предметы: <select name="loc_shmots_select">';

                while ($lsi = $cell_loc_shmots_sql->fetch_array(MYSQLI_ASSOC)) { //создаём список
                    echo '<option value="'.$lsi['id'].'">'.$lsi['title'];

                    if ($lsi['kol_vo_all'] != 0) echo ' [x'.$lsi['kol_vo'].']'; //если суммируется, выводим кол-во

                    echo '</option>';
                }

                echo '</select>';
                echo '<input type="submit" style="float: right;" name="loc_shmots_target" value="Выбрать"/>';
                echo '</form>';
            }
            //выброшенные предметы.конец
            //если целей нет
            if ($kol_vo_cell == 0) echo 'Целей в радиусе видимости нет';

            $cti = 0; //отмечаем для карты, что цель не выбрана

        } else { //если цель выбрана
            //смотрим цель
            $cell_target_sql = $sql->query("SELECT * FROM `".$u['cell_target_type']."` WHERE `id` = '".$u['cell_target_id']."' AND `loc` = '".$u['loc']."' AND `x` >= '".($u['x'] - $radius_vid)."' AND `x` <= '".($u['x'] + $radius_vid)."' AND `y` >= '".($u['y'] - $radius_vid)."' AND `y` <= '".($u['y'] + $radius_vid)."'");
            $cell_target_all = $cell_target_sql->num_rows;

            //если цель существует
            if ($cell_target_all != 0) {
                //сморим цель, cti - cell_target_inf
                $cti = $cell_target_sql->fetch_array(MYSQLI_ASSOC);
                //создаём пустые переменные
                $cell_target_kol_vo = ''; //кол-во
                //тип цели
                $cell_target_type = $u['cell_target_type'];
                //действия с целью
                $cell_target_deistv = '';
                //здоровье цели(если предмет, то прочнось), название, статус цели(жив, мёртв)
                if ($cell_target_type == 'users' || $cell_target_type == 'bots') { //если не предмет
                    $cell_target_hp = '<font color="#666">['.round((($cti['hp'] / $cti['hp_all']) * 100)).'%]</font> ';
                    $cell_target_title = $cti['name'];
                    $cell_target_death_on = $cti['death_on'];
                    //действия
                    //диалог
                    //если игрок или НПЦ
                    //если игрок
                    if ($cell_target_type == 'users') $cell_target_deistv .= '<a class="cell" href="./char.php?a=mail_new_message&player='.$cti['name'].'">сказать</a>';
                    //ели НПЦ
                    if ($cell_target_type == 'bots' && $cti['type'] == 2) {
                        if ($cti['x'] >= ($u['x'] - 1) && $cti['x'] <= ($u['x'] + 1) && $cti['y'] >= ($u['y'] - 1) && $cti['y'] <= ($u['y'] + 1)) $cell_target_deistv .= '<a class="cell" href="./npc.php?id='.$cti['id'].'">говорить</a>';
                    }
                    //атака
                    //смотрим, можно ли стрелять на этой клетке
                    $loc_strike_off = $sql->query("SELECT * FROM `locations` WHERE `loc` = '".$u['loc']."' AND `x` = '".$u['x']."' AND `y` = '".$u['y']."' AND `strike_off` = '1'")->num_rows;
                    //если стрелять на этой клетке можно
                    if ($loc_strike_off == 0) {
                        //если игрок или бот и атаковать можно
                        //типы ботов: 0 - нейтрал(атакует если его тронуть), 1 - агресивный(сам может напасть), 2 - НПЦ(атаковать нельзя)
                        if ($cell_target_type == 'users' || $cell_target_type == 'bots' && $cti['type'] != 2) {
                            //создаём ссылку на атаку
                            $cell_target_deistv .= '<a class="cell" href="?cell_target_damage">атаковать';

                            //функция атаки
                            if (isset($_GET['cell_target_damage'])) {
                                //функция
                                function cell_target_damage($sql, $u, $cti, $ew, $ewYes, $ew_rad_att, $ew_rej_att, $ew_speed_att, $cell_target_type) {
                                    //смотрим, есть ли навык владения оружием у игрока или он вообще не нужен
                                    $ew_naw_isp = 1; //навык есть

                                    if ($ewYes == 1) { //сли оружие смотрим
                                        $ew_naw_isp = $sql->query("SELECT * FROM `users_naw` WHERE `user` = '".$u['id']."' AND `title` = '".$ew['t_naw']."' AND `lvl` >= '".$ew['t_naw_lvl']."'")->num_rows;
                                    }

                                    //если навык есть
                                    if ($ew_naw_isp != 0) {
                                        //если цель в радиусе атаки
                                        if ($cti['x'] >= ($u['x'] - $ew_rad_att) && $cti['x'] <= ($u['x'] + $ew_rad_att) && $cti['y'] >= ($u['y'] - $ew_rad_att) && $cti['y'] <= ($u['y'] + $ew_rad_att)) {
                                            //поворачиваемся к цели
                                            if ($cti['x'] < $u['x'] && $cti['y'] > $u['y']) {
                                                $sql->query("UPDATE `users` SET `skin_p` = '1' WHERE `id` = '".$u['id']."'");
                                            } else if ($cti['x'] == $u['x'] && $cti['y'] > $u['y']) {
                                                $sql->query("UPDATE `users` SET `skin_p` = '2' WHERE `id` = '".$u['id']."'");
                                            } else if ($cti['x'] > $u['x'] && $cti['y'] > $u['y']) {
                                                $sql->query("UPDATE `users` SET `skin_p` = '3' WHERE `id` = '".$u['id']."'");
                                            } else if ($cti['x'] < $u['x'] && $cti['y'] == $u['y']) {
                                                $sql->query("UPDATE `users` SET `skin_p` = '4' WHERE `id` = '".$u['id']."'");
                                            } else if ($cti['x'] > $u['x'] && $cti['y'] == $u['y']) {
                                                $sql->query("UPDATE `users` SET `skin_p` = '5' WHERE `id` = '".$u['id']."'");
                                            } else if ($cti['x'] < $u['x'] && $cti['y'] < $u['y']) {
                                                $sql->query("UPDATE `users` SET `skin_p` = '6' WHERE `id` = '".$u['id']."'");
                                            } else if ($cti['x'] == $u['x']  && $cti['y'] < $u['y']) {
                                                $sql->query("UPDATE `users` SET `skin_p` = '7' WHERE `id` = '".$u['id']."'");
                                            } else if ($cti['x'] > $u['x'] && $cti['y'] < $u['y']) {
                                                $sql->query("UPDATE `users` SET `skin_p` = '8' WHERE `id` = '".$u['id']."'");
                                            }

                                            //заклинивание оружия
                                            $ew_prochnost = 100; //пока что 100% целостность

                                            if ($ewYes == 1 && $ew['type'] != 4 && $ew['type'] != 3) { //если одето оружие и оно не метательное
                                                $ew_prochnost = round((($ew['pr'] / $ew['pr_all']) * 100)); //смотрим на сколько % оружие цело
                                            }

                                            //нажимаем курок =)
                                            if (rand(1, 100) <= $ew_prochnost) { //если выстрел произошёл удачно
                                                //если откат произошёл и атаковать можно
                                                if ($ew_speed_att <= 0) {
                                                    //указываем урон от патрон и урон
                                                    $ew_uron = round(($u['str'] * 0.6), 3);
                                                    $ew_uron_all = round(($ew_uron + ($ew_uron * 0.15)), 3);
                                                    $ew_patron_uron = 0; //для холодного или рук
                                                    $ew_patron_uron_all = 0; //макс. урон патронов
                                                    $ew_b_patron = 1; //кол-во патрон, 1 т.к. для удара
                                                    //если в руках оружие, берём его урон и кол-во патрон
                                                    if ($ewYes == 1) {
                                                        $ew_uron = $ew['att'];
                                                        $ew_uron_all = $ew['att_all'];
                                                    }
                                                    //смотрим урон патрон(если в рукаж огнестрельное оружие)
                                                    if ($ewYes == 1 && !empty($ew['kalibr'])) {
                                                        //подключаемся к патронам
                                                        $ew_p_uron = $sql->query("SELECT * FROM `shmots` WHERE `title` = '".$ew['kalibr']."' AND `type` = '11'")->fetch_array(MYSQLI_ASSOC);
                                                        //указываем урон патрон
                                                        $ew_patron_uron = $ew_p_uron['att'];
                                                        $ew_patron_uron_all = $ew_p_uron['att_all'];
                                                        //указываем кол-во патрон
                                                        $ew_b_patron = $ew['patron'] - $ew_rej_att;
                                                    }
                                                    //смотрим колво патрон если оружие метательное
                                                    if ($ewYes == 1 && $ew['type'] == 4) {
                                                        //указываем кол-во патрон
                                                        $ew_b_patron = $ew['kol_vo'] - 1;
                                                    }

                                                    //если патроны в магазине имеются
                                                    if ($ew_b_patron >= 0) {
                                                        //указываем статус цели
                                                        $death_on = 0; //жива
                                                        //если оружие огнестрельное //отнимаем патрон(ы) у оружия
                                                        if ($ewYes == 1 && !empty($ew['kalibr'])) $sql->query("UPDATE `users_bag` SET `patron` = '".$ew_b_patron."' WHERE `id` = '".$ew['id']."'");
                                                        //запускаем цикл на атаку
                                                        for ($n_att = 1; $n_att <= $ew_rej_att; $n_att++) {
                                                            //расчитываем урон
                                                            $rand_uron = rand(($ew_uron + $ew_patron_uron), ($ew_uron_all + $ew_patron_uron_all)); //тут с учётом урона патрон
                                                            //расчитываем статы противника
                                                            $cti_dodge = round(($cti['agi'] / 10), 2) * 100; //уворот из 1000
                                                            $cti_armor = round(($cti['def'] / 10), 5); //ед урона блокируем

                                                            //статы юзера
                                                            $u_dex = round(($u['dex'] * 10), 1) + 6000; //шанс попадания из 10000
                                                            $damage = $rand_uron - $cti_armor; //расчитываем окончательный урон

                                                            if ($damage < 0) $damage = 0;
                                                            //отмечам что стен пока на пути пули нет
                                                            $pul_stena = 0;

                                                            //если оружие огнестрельное
                                                            if ($ewYes == 1 && !empty($ew['kalibr'])) {
                                                                //отнимаем патрон(ы) у оружия
                                                                $sql->query("UPDATE `users_bag` SET `patron` = '".$ew_b_patron."' WHERE `id` = '".$ew['id']."'");

                                                            }
                                                            //если оружие метательное
                                                            if ($ewYes == 1 && $ew['type'] == 4) {
                                                                //отнимаем колво, если колво = 0, удаляем оружие
                                                                if ($ew_b_patron > 0) {
                                                                    $sql->query("UPDATE `users_bag` SET `kol_vo` = '".$ew_b_patron."' WHERE `id` = '".$ew['id']."'");
                                                                } else {
                                                                    $sql->query("DELETE FROM `users_bag` WHERE `id` = '".$ew['id']."'");
                                                                    //очищаем оружейный слот
                                                                    //если взято в парвую руку
                                                                    if ($ew['ek'] == 1) {
                                                                        $sql->query("UPDATE `users` SET `equip_weapon_r` = '0' WHERE `id` = '".$u['id']."'");
                                                                    } else { //если в левую
                                                                        $sql->query("UPDATE `users` SET `equip_weapon_l` = '0' WHERE `id` = '".$u['id']."'");
                                                                    }

                                                                }
                                                            }

                                                            //смотрим путь пули
                                                            if ($ewYes == 1 && !empty($ew['kalibr']) || $ewYes == 1 && $ew['type'] == 4) {
                                                                //смотрим, есть ли на пути пули стены
                                                                //отмечаем начальные координаты пули
                                                                $px = $u['x'];
                                                                $py = $u['y'];
                                                                //измеряем сколько ходов должна сделать пуля
                                                                if (abs((abs($u['x']) - abs($cti['x']))) >= abs((abs($u['y']) - abs($cti['y'])))) {
                                                                    $put_pul = abs((abs($u['x']) - abs($cti['x'])));
                                                                } else {
                                                                    $put_pul = abs((abs($u['y']) - abs($cti['y'])));
                                                                }
                                                                    
                                                                //смотрим путь
                                                                for ($pp = 1; $pp <= $put_pul; $pp++) {
                                                                    //меняем координаты пули в зависимости от положения противника
                                                                    if ($px < $cti['x']) {
                                                                        $px++;
                                                                    } else if ($px > $cti['x']) {
                                                                        $px--;
                                                                    }

                                                                    if ($py < $cti['y']) {
                                                                        $py++;
                                                                    } else if ($py > $cti['y']) {
                                                                        $py--;
                                                                    }
                                                                    //наконец смотрим стену/преграду через которую нельзя стрелять
                                                                    $pul_stena_all = $sql->query("SELECT * FROM `locations_predmet` WHERE `loc` = '".$u['loc']."' AND `x` = '".$px."' AND `y` = '".$py."' AND `strike_off` = '1'")->num_rows;
                                                                    //если преграда есть, говорим что есть
                                                                    if ($pul_stena_all != 0) {
                                                                        $pul_stena++;
                                                                        //выходим из проверки, т.к. дальше смотреть бессмысленно
                                                                        break 1; //выходим из for
                                                                    }

                                                                }

                                                            }
                                                            //см.путь пули.конец

                                                            //ломаем оружие с шансом 3% если оно ещё не сломано
                                                            if ($ewYes == 1 && $ew['type'] != 4 && $ew['pr'] > 0 && rand(0, 100) <= 3) { //если оно не метательное
                                                                //ломаем
                                                                $sql->query("UPDATE `users_bag` SET `pr` = '".($ew['pr'] - 1)."' WHERE `id` = '".$ew['id']."'");
                                                            }

                                                            if ($pul_stena == 0) { //если препятсвий нет

                                                                if (rand(0, 10000) <= $u_dex) { //если попали

                                                                    if (rand(0, 1000) > $cti_dodge) { //если не увернулся

                                                                        $death_po_hp = $cti['hp'] - $damage; //смотрим сколько хп останется у противника после атаки

                                                                        if ($death_po_hp <= 0) { //если 0 или меньше
                                                                            $damage = $cti['hp']; //ставим столько сколько было у цели зп
                                                                            $death_on = 1; //убиваем цель
                                                                        }

                                                                        //снимаем противнику ХП
                                                                        $sql->query("UPDATE `".$cell_target_type."` SET `hp` = '".($cti['hp'] - round($damage))."' WHERE `id` = '".$cti['id']."'");
                                                                        //создаём лог
                                                                        $sql->query("INSERT INTO `users_logi` SET `user` = '".$u['id']."', `text` = '<font color=\"#00FF00\">Вы попали по [".$cti['name']."] и нанесли ".round($damage)." ед. урона</font>', `dtime` = '".date("H:i")."'");
                                                                        //если цель - игрок, создаём ему лог
                                                                        if ($cell_target_type == 'users') {
                                                                            $sql->query("INSERT INTO `users_logi` SET `user` = '".$cti['id']."', `text` = '<font color=\"#FF0000\">[".$u['name']."] попал по вам и нанёс ".round($damage)." ед. урона</font>', `dtime` = '".date("H:i")."'");
                                                                        }
                                                                        //отмечам что мы нанесли цели урон
                                                                        $my_uron_celi_sql = $sql->query("SELECT * FROM `".$cell_target_type."_damage` WHERE `cid` = '".$cti['id']."' AND `aid` = '".$u['id']."'");
                                                                        $my_uron_celi_all = $my_uron_celi_sql->num_rows;

                                                                        //если до этого урон не был нанесён
                                                                        if ($my_uron_celi_all == 0) {
                                                                            //отмечаем
                                                                            $sql->query("INSERT INTO `".$cell_target_type."_damage` SET `cid` = '".$cti['id']."', `aid` = '".$u['id']."', `damage` = '".$damage."', `timer` = '".time()."';");
                                                                        } else { //если урон уже был нанесён
                                                                            //смотрим сколько было до этого
                                                                            $my_uron_celi = $my_uron_celi_sql->fetch_array(MYSQLI_ASSOC);
                                                                            //отмечаем
                                                                            $sql->query("UPDATE `".$cell_target_type."_damage` SET `damage` = '".($damage + $my_uron_celi['damage'])."', `timer` = '".time()."' WHERE `id` = '".$my_uron_celi['id']."'");
                                                                        }

                                                                        //если цель - бот, пытаемся взять в таргет
                                                                        if ($cell_target_type == 'bots') {
                                                                            if ($cti['p_att_time'] <= (time() - 15) || $cti['cell_target_id'] == 0) {
                                                                                $sql->query("UPDATE `bots` SET `cell_target_type` = 'users', `cell_target_id` = '".$u['id']."', `p_att_time` = '".time()."' WHERE `id` = '".$cti['id']."'");
                                                                            }
                                                                            if ($cti['cell_target_id'] == $u['id']) {
                                                                                $sql->query("UPDATE `bots` SET `p_att_time` = '".time()."' WHERE `id` = '".$cti['id']."'");
                                                                            }
                                                                        }

                                                                        //прокачка навыка владения оружием(если в руках оружие)
                                                                        if ($ewYes == 1) {
                                                                            //подключаемся к навыку
                                                                            $us_naw_isp = $sql->query("SELECT * FROM `users_naw` WHERE `title` = '".$ew['t_naw']."' AND `user` = '".$u['id']."'")->fetch_array(MYSQLI_ASSOC);
                                                                            
                                                                            if ($cti['lvl'] >= $us_naw_isp['lvl']) {    
                                                                                //расчитываем сколько прибавлять
                                                                                $rand_exp_naw = rand(1, 99) / 100; //в сотых
                                                                                //если опыт привысил максимальную шкалу
                                                                                //смотрим сколько опыта надо до левла
                                                                                $us_naw_exp_sql = $sql->query("SELECT * FROM `table_lvl_naw` WHERE `id` = '".$us_naw_isp['lvl']."'");
                                                                                $us_naw_exp_all = $us_naw_exp_sql->num_rows;
                                                                                //если левл повысить можно
                                                                                if ($us_naw_exp_all != 0) {
                                                                                    //подключаеся к таблице
                                                                                    $us_naw_exp = $us_naw_exp_sql->fetch_array(MYSQLI_ASSOC);
                                                                                    //если уровень пыта привысил шкалу
                                                                                    if (($us_naw_isp['exp'] + $rand_exp_naw) >= $us_naw_exp['exp']) {

                                                                                        if ($us_naw_isp['lvl'] < $u['lvl']) {
                                                                                            //повышаем уровень
                                                                                            $sql->query("UPDATE `users_naw` SET `lvl` = '".($us_naw_isp['lvl'] + 1)."' WHERE `id` = '".$us_naw_isp['id']."'");
                                                                                            //создаём лог
                                                                                            $sql->query("INSERT INTO `users_logi` SET `user` = '".$u['id']."', `text` = '<font color=\"#0000FF\">Уровень навыка [".$us_naw_isp['title']."] повышен</font>', `dtime` = '".date("H:i")."'");
                                                                                        } else {

                                                                                            if ($us_naw_isp['exp'] < $us_naw_exp['exp']) { //поднимаем опыт до максимума если уже не поднято
                                                                                                $sql->query("UPDATE `users_naw` SET `exp` = '".$us_naw_exp['exp']."' WHERE `id` = '".$us_naw_isp['id']."'");
                                                                                                //создаём лог
                                                                                                $sql->query("INSERT INTO `users_logi` SET `user` = '".$u['id']."', `text` = '<font color=\"#0000FF\">Навык [".$us_naw_isp['title']."] + ".$rand_exp_naw."</font>', `dtime` = '".date("H:i")."'");
                                                                                            }

                                                                                        }

                                                                                    } else { //если ещё не привысил
                                                                                        //прибавляем если уровень навыка меньше или равен уровню игрока
                                                                                        if ($us_naw_isp['lvl'] <= $u['lvl']) {
                                                                                            $sql->query("UPDATE `users_naw` SET `exp` = '".($us_naw_isp['exp'] + $rand_exp_naw)."' WHERE `id` = '".$us_naw_isp['id']."'");
                                                                                            //создаём лог
                                                                                            $sql->query("INSERT INTO `users_logi` SET `user` = '".$u['id']."', `text` = '<font color=\"#0000FF\">Навык [".$us_naw_isp['title']."] + ".$rand_exp_naw."</font>', `dtime` = '".date("H:i")."'");
                                                                                        }

                                                                                    }

                                                                                }

                                                                            }

                                                                        }

                                                                    } else { //если увернулся
                                                                        //создаём лог
                                                                        $sql->query("INSERT INTO `users_logi` SET `user` = '".$u['id']."', `text` = '<font color=\"#FF0000\">[".$cti['name']."] увернулся</font>', `dtime` = '".date("H:i")."'");
                                                                        //если цель - игрок, создаём ему лог
                                                                        if ($cell_target_type == 'users') {
                                                                            //прокачиваем навык который качается при уворотах
                                                                            $naw_dodge_sql = $sql->query("SELECT * FROM `users_naw` WHERE `kach_ot` = 'dodge' AND `user` = '".$cti['id']."'");
                                                                            $naw_dodge_all = $naw_dodge_sql->num_rows;

                                                                            if ($naw_dodge_all != 0) {
                                                                                $naw = $naw_dodge_sql->fetch_array(MYSQLI_ASSOC);
                                                                                $rand_exp = rand(1, 99) / 100;
                                                                                $us_naw_exp_sql = $sql->query("SELECT * FROM `table_lvl_naw` WHERE `id` = '".$naw['lvl']."'");
                                                                                $us_naw_exp_all = $us_naw_exp_sql->num_rows;
                                                                                //если левл повысить можно
                                                                                if ($us_naw_exp_all != 0) {
                                                                                    //подключаеся к таблице
                                                                                    $us_naw_exp = $us_naw_exp_sql->fetch_array(MYSQLI_ASSOC);
                                                                                    //если уровень пыта привысил шкалу
                                                                                    if (($naw['exp'] + $rand_exp) >= $us_naw_exp['exp']) {

                                                                                        if ($naw['lvl'] < $cti['lvl']) {
                                                                                            //повышаем уровень
                                                                                            $sql->query("UPDATE `users_naw` SET `lvl` = '".($naw['lvl'] + 1)."' WHERE `id` = '".$naw['id']."'");
                                                                                            //увеличиваем статы
                                                                                            $sql->query("UPDATE `users` SET `".$naw['kach_stat']."` = '".($cti[$naw['kach_stat']] + $naw['kach_stat_plus'])."' WHERE `id` = '".$cti['id']."'");
                                                                                            //создаём лог
                                                                                            $sql->query("INSERT INTO `users_logi` SET `user` = '".$cti['id']."', `text` = '<font color=\"#0000FF\">Уровень навыка [".$naw['title']."] повышен</font>', `dtime` = '".date("H:i")."'");
                                                                                        } else {

                                                                                            if ($naw['exp'] < $us_naw_exp['exp']) { //поднимаем опыт до максимума если уже не поднято
                                                                                                $sql->query("UPDATE `users_naw` SET `exp` = '".$us_naw_exp['exp']."' WHERE `id` = '".$naw['id']."'");
                                                                                                //создаём лог
                                                                                                $sql->query("INSERT INTO `users_logi` SET `user` = '".$cti['id']."', `text` = '<font color=\"#0000FF\">Навык [".$naw['title']."] + ".$rand_exp."</font>', `dtime` = '".date("H:i")."'");
                                                                                            }

                                                                                        }

                                                                                    } else { //если ещё не привысил
                                                                                        //прибавляем если уровень навыка меньше или равен уровню игрока
                                                                                        if ($naw['lvl'] <= $cti['lvl']) {
                                                                                            $sql->query("UPDATE `users_naw` SET `exp` = '".($naw['exp'] + $rand_exp)."' WHERE `id` = '".$naw['id']."'");
                                                                                            //создаём лог
                                                                                            $sql->query("INSERT INTO `users_logi` SET `user` = '".$cti['id']."', `text` = '<font color=\"#0000FF\">Навык [".$naw['title']."] + ".$rand_exp."</font>', `dtime` = '".date("H:i")."'");
                                                                                        }

                                                                                    }

                                                                                }

                                                                            }

                                                                            $sql->query("INSERT INTO `users_logi` SET `user` = '".$cti['id']."', `text` = '<font color=\"#00FF00\">Вы увернулись от атаки [".$u['name']."]</font>', `dtime` = '".date("H:i")."'");
                                                                        }
                                                                    }

                                                                } else { //если не попали
                                                                    //создаём лог
                                                                    $sql->query("INSERT INTO `users_logi` SET `user` = '".$u['id']."', `text` = '<font color=\"#FF0000\">Вы не попали по [".$cti['name']."]</font>', `dtime` = '".date("H:i")."'");
                                                                    //если цель - игрок, создаём ему лог
                                                                    if ($cell_target_type == 'users') {
                                                                        $sql->query("INSERT INTO `users_logi` SET `user` = '".$cti['id']."', `text` = '<font color=\"#00FF00\">[".$u['name']."] не попал по вам</font>', `dtime` = '".date("H:i")."'");
                                                                    }
                                                                }

                                                            } else { //если встреченно припятсвие
                                                                //создаём лог
                                                                $sql->query("INSERT INTO `users_logi` SET `user` = '".$u['id']."', `text` = 'Вы попали в препятсвие', `dtime` = '".date("H:i")."'");
                                                            }

                                                        }

                                                        //если цель убили
                                                        if ($death_on != 0) {
                                                            //убиваем цель
                                                            $sql->query("UPDATE `".$cell_target_type."` SET `death_on` = '".$death_on."', `death_time` = '".time()."' WHERE `id` = '".$cti['id']."'");
                                                            //если цель - игрок, добавляем PvP поражение
                                                            if ($cell_target_type == 'users') $sql->query("UPDATE `users` SET `PvP_lose` = '".($cti['PvP_lose'] + 1)."' WHERE `id` = '".$cti['id']."'");
                                                            //создаём лог
                                                            $sql->query("INSERT INTO `users_logi` SET `user` = '".$u['id']."', `text` = '<font color=\"#00FF00\">Вы убили [".$cti['name']."]</font>', `dtime` = '".date("H:i")."'");
                                                            //если цель - игрок, создаём лог о смерти
                                                            if ($cell_target_type == 'users') {
                                                                $sql->query("INSERT INTO `users_logi` SET `user` = '".$cti['id']."', `text` = '<font color=\"#00FF00\">[".$u['name']."] убил вас</font>', `dtime` = '".date("H:i")."'");
                                                                //указываем откуда и какие вещи брать на дроп
                                                                $cell_drop_sql = $sql->query("SELECT * FROM `users_bag` WHERE `user` = '".$cti['id']."' AND `type` >= '11'");
                                                                //отнимаем у всех одетых вещей по 1 прочности
                                                                $eq_on_us_sql = $sql->query("SELECT * FROM `users_bag` WHERE `pr` > '0' AND `ek` != '0' AND `user` = '".$cti['id']."'");
                                                                $eq_on_us_all = $eq_on_us_sql->num_rows;

                                                                if ($eq_on_us_all != 0) {
                                                                    while ($eqw = $eq_on_us_sql->fetch_array(MYSQLI_ASSOC)) {
                                                                        $sql->query("UPDATE `users_bag` SET `pr` = '".($eqw['pr'] - 1)."' WHERE `id` = '".$eqw['id']."'");
                                                                    }
                                                                }

                                                            } else { //если бот
                                                                //указываем откуда и какие вещи брать на дроп
                                                                $cell_drop_sql = $sql->query("SELECT * FROM `bots_bag` WHERE `bot` = '".$cti['id']."'");
                                                            }

                                                            //смотрим сколько игроков атаковало эту цель
                                                            $atakeri_celi_sql = $sql->query("SELECT * FROM `".$cell_target_type."_damage` WHERE `cid` = '".$cti['id']."'");
                                                            $atakeri_celi_all = $atakeri_celi_sql->num_rows; //кол-во

                                                            if ($atakeri_celi_all != 0) { //если атакеры есть(а они есть по любому)
                                                                //говорим всем что цель умерла
                                                                while ($at_log = $atakeri_celi_sql->fetch_array(MYSQLI_ASSOC)) {
                                                                    //создаём лог всем кроме игрока который убил цель
                                                                    if ($at_log['aid'] != $u['id']) $sql->query("INSERT INTO `users_logi` SET `user` = '".$at_log['aid']."', `text` = '[".$cti['name']."] умер', `dtime` = '".date("H:i")."'");
                                                                    //если игрок вышел из боя живым, добавляем победу
                                                                    //смотрим игрок
                                                                    $at_log_u = $sql->query("SELECT * FROM `users` WHERE `id` = '".$at_log['aid']."'")->fetch_array(MYSQLI_ASSOC);
                                                                    if ($at_log_u['death_on'] == 0) {
                                                                        if ($cell_target_type == 'bots') { //если бот
                                                                            $vin_type = 'PvE_win'; //пве победы
                                                                        } else { //если игрок
                                                                            $vin_type = 'PvP_win'; //пвп победы
                                                                        }
                                                                        //добавляем
                                                                        $sql->query("UPDATE `users` SET `".$vin_type."` = '".($at_log_u[$vin_type] + 1)."' WHERE `id` = '".$at_log_u['id']."'");
                                                                    }
                                                                }

                                                                //если цель - бот, выдаём атакующим бота дроп в виде монет и опыта
                                                                //делаем снова запрос
                                                                $atakeri_celi_sql = $sql->query("SELECT * FROM `".$cell_target_type."_damage` WHERE `cid` = '".$cti['id']."'");

                                                                if ($cell_target_type == 'bots') {
                                                                    //atakeri_drop_money_and_exp
                                                                    while ($a_drop_m_and_e = $atakeri_celi_sql->fetch_array(MYSQLI_ASSOC)) {
                                                                        //подключаемся к игроку
                                                                        $ataker_dme = $sql->query("SELECT * FROM `users` WHERE `id` = '".$a_drop_m_and_e['aid']."'")->fetch_array(MYSQLI_ASSOC);
                                                                        //смотрим какой % от хп цели отнял игрок
                                                                        $proc_ataki = $a_drop_m_and_e['damage']  / $cti['hp_all'];
                                                                        //смотрим сколько в целом должен получить игрок
                                                                        $drop_money = round(($cti['money'] * $proc_ataki));
                                                                        $drop_exp = round(($cti['exp'] * $proc_ataki));
                                                                        //добавляем
                                                                        $sql->query("UPDATE `users` SET `exp` = '".($ataker_dme['exp'] + $drop_exp)."', `money` = '".($ataker_dme['money'] + $drop_money)."' WHERE `id` = '".$ataker_dme['id']."'");
                                                                        //создаём логи если это надо
                                                                        if ($drop_money != 0) $sql->query("INSERT INTO `users_logi` SET `user` = '".$ataker_dme['id']."', `text` = '<font color=\"#0000FF\">Кредиты + ".$drop_money."</font>', `dtime` = '".date("H:i")."'");
                                                                        if ($drop_exp != 0) $sql->query("INSERT INTO `users_logi` SET `user` = '".$ataker_dme['id']."', `text` = '<font color=\"#0000FF\">Опыт + ".$drop_exp."</font>', `dtime` = '".date("H:i")."'");

                                                                    }

                                                                }
                                                                //вывод дропа в виде монет и опыта конец

                                                                //смотрим игрока который нанёс больше всех урона этой цели
                                                                //ssa - sam_sil_ataker
                                                                $ssa = $sql->query("SELECT * FROM `".$cell_target_type."_damage` WHERE `cid` = '".$cti['id']."' ORDER BY `damage` DESC LIMIT 1")->fetch_array(MYSQLI_ASSOC);
                                                                //выдаём ему дроп
                                                                while ($ssa_d = $cell_drop_sql->fetch_array(MYSQLI_ASSOC)) {
                                                                    //смотрим шанс выпадения вещи
                                                                    if ($cell_target_type == 'bots') { //если цель - бот
                                                                        $shans_drop = $ssa_d['shans'] * 100;
                                                                    } else { //если цель - игрок
                                                                        $shans_drop = 10000;
                                                                    }

                                                                    //если удача - т.е. шмот выпал
                                                                    if (rand(0, 10000) <= $shans_drop) {
                                                                        //добавляем в рюкзак самому сильному игроку
                                                                        //если шмот не суммируется
                                                                        if ($ssa_d['kol_vo_all'] == 0) { //если предмет не суммируется
                                                                            //добавляем в рюкзак
                                                                            $sql->query("INSERT INTO `users_bag` SET `lvl` = '".$ssa_d['lvl']."', `title` = '".$ssa_d['title']."', `type` = '".$ssa_d['type']."', `t_naw` = '".$ssa_d['t_naw']."', `t_naw_lvl` = '".$ssa_d['lvl']."', `speed_att` = '".$ssa_d['speed_att']."', `speed_att_all` = '".$ssa_d['speed_att_all']."', `kalibr` = '".$ssa_d['kalibr']."', `patron` = '".$ssa_d['patron']."', `patron_all` = '".$ssa_d['patron_all']."', `att` = '".$ssa_d['att']."', `att_all` = '".$ssa_d['att_all']."', `rej_str` = '".$ssa_d['rej_str']."', `rej_str_all` = '".$ssa_d['rej_str_all']."', `radius_att` = '".$ssa_d['radius_att']."', `pr` = '".$ssa_d['pr']."', `pr_all` = '".$ssa_d['pr_all']."', `ves` = '".$ssa_d['ves']."', `def` = '".$ssa_d['def']."', `str` = '".$ssa_d['str']."', `agi` = '".$ssa_d['agi']."', `dex` = '".$ssa_d['dex']."', `hp` = '".$ssa_d['hp']."', `hp_all` = '".$ssa_d['hp_all']."', `en` = '".$ssa_d['en']."', `en_all` = '".$ssa_d['en_all']."', `speed_hod_all` = '".$ssa_d['speed_hod_all']."', `ruki` = '".$ssa_d['ruki']."', `kol_vo` = '".$ssa_d['kol_vo']."', `kol_vo_all` = '".$ssa_d['kol_vo_all']."', `vmest` = '".$ssa_d['vmest']."', `cost` = '".$ssa_d['cost']."', `k_stat` = '".$ssa_d['k_stat']."', `user` = '".$ssa['aid']."';");
                                                                            //создаём лог
                                                                            $sql->query("INSERT INTO `users_logi` SET `user` = '".$ssa['aid']."', `text` = 'Вы получили [".$ssa_d['title']."]', `dtime` = '".date("H:i")."'");
                                                                            //если цель - игрок, удаляем шмот из рюкзака и создаём лог
                                                                            if ($cell_target_type == 'users') {
                                                                                $sql->query("DELETE FROM `users_bag` WHERE `id` = '".$ssa_d['id']."' LIMIT 1"); //удаляем
                                                                                $sql->query("INSERT INTO `users_logi` SET `user` = '".$cti['id']."', `text` = 'Вы потеряли [".$ssa_d['title']."]', `dtime` = '".date("H:i")."'");
                                                                            }

                                                                        } else { //если суммируется
                                                                            //проверяем, есть ли такой предмет в рюкзаке
                                                                            $bag_shmot_cop_sql = $sql->query("SELECT * FROM `users_bag` WHERE `user` = '".$ssa['aid']."' AND `title` = '".$ssa_d['title']."' AND `type` = '".$ssa_d['type']."'");
                                                                            $bag_shmot_cop_all = $bag_shmot_cop_sql->num_rows;

                                                                            if ($bag_shmot_cop_all == 0) { //если такого предмета в рюкзаке нет
                                                                                //добавляем в рюкзак
                                                                                $sql->query("INSERT INTO `users_bag` SET `lvl` = '".$ssa_d['lvl']."', `title` = '".$ssa_d['title']."', `type` = '".$ssa_d['type']."', `t_naw` = '".$ssa_d['t_naw']."', `t_naw_lvl` = '".$ssa_d['lvl']."', `speed_att` = '".$ssa_d['speed_att']."', `speed_att_all` = '".$ssa_d['speed_att_all']."', `kalibr` = '".$ssa_d['kalibr']."', `patron` = '".$ssa_d['patron']."', `patron_all` = '".$ssa_d['patron_all']."', `att` = '".$ssa_d['att']."', `att_all` = '".$ssa_d['att_all']."', `rej_str` = '".$ssa_d['rej_str']."', `rej_str_all` = '".$ssa_d['rej_str_all']."', `radius_att` = '".$ssa_d['radius_att']."', `pr` = '".$ssa_d['pr']."', `pr_all` = '".$ssa_d['pr_all']."', `ves` = '".$ssa_d['ves']."', `def` = '".$ssa_d['def']."', `str` = '".$ssa_d['str']."', `agi` = '".$ssa_d['agi']."', `dex` = '".$ssa_d['dex']."', `hp` = '".$ssa_d['hp']."', `hp_all` = '".$ssa_d['hp_all']."', `en` = '".$ssa_d['en']."', `en_all` = '".$ssa_d['en_all']."', `speed_hod_all` = '".$ssa_d['speed_hod_all']."', `ruki` = '".$ssa_d['ruki']."', `kol_vo` = '".$ssa_d['kol_vo']."', `kol_vo_all` = '".$ssa_d['kol_vo_all']."', `vmest` = '".$ssa_d['vmest']."', `cost` = '".$ssa_d['cost']."', `k_stat` = '".$ssa_d['k_stat']."', `user` = '".$ssa['aid']."';");
                                                                            } else { //если есть
                                                                                $bag_shmot_cop = $bag_shmot_cop_sql->fetch_array(MYSQLI_ASSOC);
                                                                                //добавляем в рюкзак
                                                                                $sql->query("UPDATE `users_bag` SET `kol_vo` = '".($bag_shmot_cop['kol_vo'] + $ssa_d['kol_vo'])."' WHERE `id` = '".$bag_shmot_cop['id']."'");
                                                                            }

                                                                            //создаём лог
                                                                            $sql->query("INSERT INTO `users_logi` SET `user` = '".$ssa['aid']."', `text` = 'Вы получили [".$ssa_d['title']."] [x".$ssa_d['kol_vo']."]', `dtime` = '".date("H:i")."'");
                                                                            //если цель - игрок, удаляем шмот из рюкзака и создаём лог
                                                                            if ($cell_target_type == 'users') {
                                                                                $sql->query("DELETE FROM `users_bag` WHERE `id` = '".$ssa_d['id']."' LIMIT 1"); //удаляем
                                                                                $sql->query("INSERT INTO `users_logi` SET `user` = '".$cti['id']."', `text` = 'Вы потеряли [".$ssa_d['title']."] [x".$ssa_d['kol_vo']."]', `dtime` = '".date("H:i")."'");
                                                                            }

                                                                        }
                                                                        //добавление.конец
                                                                    }

                                                                }
                                                                //выдача дропа самому сильному.конец

                                                                //скидываем игрокам таргет у которых выбрана эта цель
                                                                $sql->query("UPDATE `users` SET `cell_target_type` = '', `cell_target_id` = '0' WHERE `cell_target_type` = '".$cell_target_type."' AND `cell_target_id` = '".$cti['id']."'");
                                                                //очищаем инфу о бое
                                                                $sql->query("DELETE FROM `".$cell_target_type."_damage` WHERE `cid` = '".$cti['id']."'");
                                                                //удаляем таргет у цели
                                                                $sql->query("UPDATE `".cell_target_type." SET `cell_target_type` = '', `cell_target_id` = '0' WHERE `id` = '".$cti['id']."'");
                                                            }

                                                        }

                                                        //запускаем таймер на атаку
                                                        //оружию тоже если надето
                                                        if ($ewYes == 1) $sql->query("UPDATE `users_bag` SET `speed_att` = '".time()."' WHERE `id` = '".$ew['id']."'");
                                                        $sql->query("UPDATE `users` SET `speed_att` = '".time()."' WHERE `id` = '".$u['id']."'");

                                                    } else { //если патрон в магазине нет|не хватает
                                                        //создаём лог
                                                        $sql->query("INSERT INTO `users_logi` SET `user` = '".$u['id']."', `text` = 'Перезарядите оружие!', `dtime` = '".date("H:i")."'");
                                                    }


                                                } else { //если откат ещё не произошёл
                                                    //создаём лог
                                                    $sql->query("INSERT INTO `users_logi` SET `user` = '".$u['id']."', `text` = 'Слишком быстро', `dtime` = '".date("H:i")."'");
                                                    //ломаем оружие с шансом 5% если оно ещё не сломано
                                                    if ($ewYes == 1 && $ew['type'] != 4 && $ew['type'] != 3 && $ew['pr'] > 0 && rand(0, 100) <= 5) { //если оно не метательное
                                                        //ломаем
                                                        $sql->query("UPDATE `users_bag` SET `pr` = '".($ew['pr'] - 1)."' WHERE `id` = '".$ew['id']."'");
                                                    }

                                                }

                                            } else { //если цель не в радиусе атаки
                                                //создаём лог
                                                $sql->query("INSERT INTO `users_logi` SET `user` = '".$u['id']."', `text` = 'Оружие заклинило', `dtime` = '".date("H:i")."'");
                                            }

                                        } else { //если оружие заклинило
                                            //создаём лог
                                            $sql->query("INSERT INTO `users_logi` SET `user` = '".$u['id']."', `text` = 'Цель далеко!', `dtime` = '".date("H:i")."'");
                                        }

                                    } else { //если навыка нет
                                        //создаём лог
                                        $sql->query("INSERT INTO `users_logi` SET `user` = '".$u['id']."', `text` = 'Вы не можете использовать это оружие', `dtime` = '".date("H:i")."'");
                                    }

                                }
                                //функция.конец

                                //запускаем функцию
                                cell_target_damage($sql, $u, $cti, $ew, $ewYes, $ew_rad_att, $ew_rej_att, $ew_speed_att, $cell_target_type);
                                header('Location: ./'); //обновляем страницу
                            } 
                            //атака.конец

                            //если откат не произошёл указываем это
                            if ($ew_speed_att > 0) $cell_target_deistv .= ' '.$ew_speed_att.' сек';
                            //закрываем ссылку
                            $cell_target_deistv .= '</a>';
                        }

                    }
                    //можно стрелять.закрываем

                } else if ($cell_target_type == 'locations_shmots') { //если предмет

                    if ($cti['pr_all'] == 0) {
                        $cti['pr'] = 1;
                        $cti['pr_all'] = 1;
                    }

                    $cell_target_hp = '<font color="#666">['.round((($cti['pr'] / $cti['pr_all']) * 100)).'%]</font> ';
                    $cell_target_title = $cti['title'];
                    $cell_target_death_on = 0; //целое
                    //если шмот суммируется указываем его кол-во
                    if ($cti['kol_vo_all'] != 0) $cell_target_kol_vo = ' <font color="#666">[x'.$cti['kol_vo'].']</font>';

                } else if ($cell_target_type == 'locations_predmet') { //если предмет локации
                    $cell_target_hp = '';
                    $cell_target_title = $cti['title'];
                    $cell_target_death_on = 0; //целое
                }

                //если цель рядом(в радиусе 1 клетки)
                if ($cti['x'] >= ($u['x'] - 1) && $cti['x'] <= ($u['x'] + 1) && $cti['y'] >= ($u['y'] - 1) && $cti['y'] <= ($u['y'] + 1)) {
                    //поднятие предмета - если выбран предмет
                    if ($cell_target_type == 'locations_shmots') {
                        $cell_target_deistv .= '<a class="cell" href="?cell_target_in_bag">поднять</a>';

                        //если игрок захотел поднять этот предмет
                        if (isset($_GET['cell_target_in_bag'])) {
                            //функция поднятия шмота
                            function cell_target_in_bag($sql, $u, $cti) {
                                //если шмот не суммируется
                                if ($cti['kol_vo_all'] == 0) { //если предмет не суммируется
                                    //добавляем в рюкзак
                                    $sql->query("INSERT INTO `users_bag` SET `lvl` = '".$cti['lvl']."', `title` = '".$cti['title']."', `type` = '".$cti['type']."', `t_naw` = '".$cti['t_naw']."', `t_naw_lvl` = '".$cti['lvl']."', `speed_att` = '".$cti['speed_att']."', `speed_att_all` = '".$cti['speed_att_all']."', `kalibr` = '".$cti['kalibr']."', `patron` = '".$cti['patron']."', `patron_all` = '".$cti['patron_all']."', `att` = '".$cti['att']."', `att_all` = '".$cti['att_all']."', `rej_str` = '".$cti['rej_str']."', `rej_str_all` = '".$cti['rej_str_all']."', `radius_att` = '".$cti['radius_att']."', `pr` = '".$cti['pr']."', `pr_all` = '".$cti['pr_all']."', `ves` = '".$cti['ves']."', `def` = '".$cti['def']."', `str` = '".$cti['str']."', `agi` = '".$cti['agi']."', `dex` = '".$cti['dex']."', `hp` = '".$cti['hp']."', `hp_all` = '".$cti['hp_all']."', `en` = '".$cti['en']."', `en_all` = '".$cti['en_all']."', `speed_hod_all` = '".$cti['speed_hod_all']."', `ruki` = '".$cti['ruki']."', `kol_vo` = '".$cti['kol_vo']."', `kol_vo_all` = '".$cti['kol_vo_all']."', `vmest` = '".$cti['vmest']."', `cost` = '".$cti['cost']."', `k_stat` = '".$cti['k_stat']."', `user` = '".$u['id']."';");
                                    //удаляем с локации
                                    $sql->query("DELETE FROM `locations_shmots` WHERE `id` = '".$cti['id']."' LIMIT 1");
                                    //создаём лог
                                    $sql->query("INSERT INTO `users_logi` SET `user` = '".$u['id']."', `text` = 'Предмет [".$cti['title']."] был поднят', `dtime` = '".date("H:i")."'");
                                    //скидывам таргет
                                    $sql->query("UPDATE `users` SET `cell_target_id` = '0', `cell_target_type` = '' WHERE `id` = '".$u['id']."'");

                                } else { //если суммируется
                                    //проверяем, есть ли такой предмет в рюкзаке
                                    $bag_shmot_cop_sql = $sql->query("SELECT * FROM `users_bag` WHERE `user` = '".$u['id']."' AND `title` = '".$cti['title']."' AND `type` = '".$cti['type']."'");
                                    $bag_shmot_cop_all = $bag_shmot_cop_sql->num_rows;

                                    if ($bag_shmot_cop_all == 0) { //если такого предмета в рюкзаке нет
                                        //добавляем в рюкзак
                                        $sql->query("INSERT INTO `users_bag` SET `lvl` = '".$cti['lvl']."', `title` = '".$cti['title']."', `type` = '".$cti['type']."', `t_naw` = '".$cti['t_naw']."', `t_naw_lvl` = '".$cti['lvl']."', `speed_att` = '".$cti['speed_att']."', `speed_att_all` = '".$cti['speed_att_all']."', `kalibr` = '".$cti['kalibr']."', `patron` = '".$cti['patron']."', `patron_all` = '".$cti['patron_all']."', `att` = '".$cti['att']."', `att_all` = '".$cti['att_all']."', `rej_str` = '".$cti['rej_str']."', `rej_str_all` = '".$cti['rej_str_all']."', `radius_att` = '".$cti['radius_att']."', `pr` = '".$cti['pr']."', `pr_all` = '".$cti['pr_all']."', `ves` = '".$cti['ves']."', `def` = '".$cti['def']."', `str` = '".$cti['str']."', `agi` = '".$cti['agi']."', `dex` = '".$cti['dex']."', `hp` = '".$cti['hp']."', `hp_all` = '".$cti['hp_all']."', `en` = '".$cti['en']."', `en_all` = '".$cti['en_all']."', `speed_hod_all` = '".$cti['speed_hod_all']."', `ruki` = '".$cti['ruki']."', `kol_vo` = '".$cti['kol_vo']."', `kol_vo_all` = '".$cti['kol_vo_all']."', `vmest` = '".$cti['vmest']."', `cost` = '".$cti['cost']."', `k_stat` = '".$cti['k_stat']."', `user` = '".$u['id']."';");
                                    } else { //если есть
                                        $bag_shmot_cop = $bag_shmot_cop_sql->fetch_array(MYSQLI_ASSOC);
                                        //добавляем в рюкзак
                                        $sql->query("UPDATE `users_bag` SET `kol_vo` = '".($bag_shmot_cop['kol_vo'] + $cti['kol_vo'])."' WHERE `id` = '".$bag_shmot_cop['id']."'");
                                    }

                                    //удаляем с локации
                                    $sql->query("DELETE FROM `locations_shmots` WHERE `id` = '".$cti['id']."' LIMIT 1");
                                    //создаём лог
                                    $sql->query("INSERT INTO `users_logi` SET `user` = '".$u['id']."', `text` = 'Предмет [".$cti['title']."] [x".$cti['kol_vo']."] был поднят', `dtime` = '".date("H:i")."'");
                                    //скидывам таргет
                                    $sql->query("UPDATE `users` SET `cell_target_id` = '0', `cell_target_type` = '' WHERE `id` = '".$u['id']."'");

                                }

                            }
                            //функция.конец

                            //запускаем функцию
                            cell_target_in_bag($sql, $u, $cti);
                            header('Location: ./'); //обновляем страницу
                        }
                        //поднятие.конец

                    }

                    //обыск, если выбран предмет локации
                    if ($cell_target_type == 'locations_predmet') {
                        $cell_target_deistv .= '<a class="cell" href="?cell_target_obsk">обыскать</a>';

                        //функция обыска
                        if (isset($_GET['cell_target_obsk'])) { //если игрок нажал "обыскать"
                            //функция
                            function cell_target_obsk($sql, $cti, $u) {
                                //если предмет сегодня не обыскивали
                                if ($cti['obsk_date'] != date("d.m.Y")) {
                                    //смотрим список предметов спрятанных в предмете
                                    $locations_in_predmet_lut_sql = $sql->query("SELECT * FROM `locations_in_predmet_lut` WHERE `predmet` = '".$cti['id']."'");
                                    $locations_in_predmet_lut_all = $locations_in_predmet_lut_sql->num_rows;
                                    $kol_vo_luta = 0; //тмечаем сколько выпало предметов

                                    if ($locations_in_predmet_lut_all != 0) { //если есть чему выпадать
                                        //запускаем дроп
                                        //lipl - locations in predmet lut
                                        while ($lipl = $locations_in_predmet_lut_sql->fetch_array(MYSQLI_ASSOC)) {
                                            //если предмет удачно выпал
                                            if (rand(0, 10000) <= ($lipl['shans'] * 100)) {
                                                $kol_vo_luta++; //увеличиваем счётчик
                                                //добавляем
                                                //если шмот не суммируется
                                                if ($lipl['kol_vo_all'] == 0) { //если предмет не суммируется
                                                    //добавляем в рюкзак
                                                    $sql->query("INSERT INTO `users_bag` SET `lvl` = '".$lipl['lvl']."', `title` = '".$lipl['title']."', `type` = '".$lipl['type']."', `t_naw` = '".$lipl['t_naw']."', `t_naw_lvl` = '".$lipl['lvl']."', `speed_att` = '".$lipl['speed_att']."', `speed_att_all` = '".$lipl['speed_att_all']."', `kalibr` = '".$lipl['kalibr']."', `patron` = '".$lipl['patron']."', `patron_all` = '".$lipl['patron_all']."', `att` = '".$lipl['att']."', `att_all` = '".$lipl['att_all']."', `rej_str` = '".$lipl['rej_str']."', `rej_str_all` = '".$lipl['rej_str_all']."', `radius_att` = '".$lipl['radius_att']."', `pr` = '".$lipl['pr']."', `pr_all` = '".$lipl['pr_all']."', `ves` = '".$lipl['ves']."', `def` = '".$lipl['def']."', `str` = '".$lipl['str']."', `agi` = '".$lipl['agi']."', `dex` = '".$lipl['dex']."', `hp` = '".$lipl['hp']."', `hp_all` = '".$lipl['hp_all']."', `en` = '".$lipl['en']."', `en_all` = '".$lipl['en_all']."', `speed_hod_all` = '".$lipl['speed_hod_all']."', `ruki` = '".$lipl['ruki']."', `kol_vo` = '".$lipl['kol_vo']."', `kol_vo_all` = '".$lipl['kol_vo_all']."', `vmest` = '".$lipl['vmest']."', `cost` = '".$lipl['cost']."', `k_stat` = '".$lipl['k_stat']."', `user` = '".$u['id']."';");
                                                    //создаём лог
                                                    $sql->query("INSERT INTO `users_logi` SET `user` = '".$u['id']."', `text` = 'Вы нашли [".$lipl['title']."]', `dtime` = '".date("H:i")."'");

                                                } else { //если суммируется
                                                    //проверяем, есть ли такой предмет в рюкзаке
                                                    $bag_shmot_cop_sql = $sql->query("SELECT * FROM `users_bag` WHERE `user` = '".$u['id']."' AND `title` = '".$lipl['title']."' AND `type` = '".$lipl['type']."'");
                                                    $bag_shmot_cop_all = $bag_shmot_cop_sql->num_rows;

                                                    if ($bag_shmot_cop_all == 0) { //если такого предмета в рюкзаке нет
                                                        //добавляем в рюкзак
                                                        $sql->query("INSERT INTO `users_bag` SET `lvl` = '".$lipl['lvl']."', `title` = '".$lipl['title']."', `type` = '".$lipl['type']."', `t_naw` = '".$lipl['t_naw']."', `t_naw_lvl` = '".$lipl['lvl']."', `speed_att` = '".$lipl['speed_att']."', `speed_att_all` = '".$lipl['speed_att_all']."', `kalibr` = '".$lipl['kalibr']."', `patron` = '".$lipl['patron']."', `patron_all` = '".$lipl['patron_all']."', `att` = '".$lipl['att']."', `att_all` = '".$lipl['att_all']."', `rej_str` = '".$lipl['rej_str']."', `rej_str_all` = '".$lipl['rej_str_all']."', `radius_att` = '".$lipl['radius_att']."', `pr` = '".$lipl['pr']."', `pr_all` = '".$lipl['pr_all']."', `ves` = '".$lipl['ves']."', `def` = '".$lipl['def']."', `str` = '".$lipl['str']."', `agi` = '".$lipl['agi']."', `dex` = '".$lipl['dex']."', `hp` = '".$lipl['hp']."', `hp_all` = '".$lipl['hp_all']."', `en` = '".$lipl['en']."', `en_all` = '".$lipl['en_all']."', `speed_hod_all` = '".$lipl['speed_hod_all']."', `ruki` = '".$lipl['ruki']."', `kol_vo` = '".$lipl['kol_vo']."', `kol_vo_all` = '".$lipl['kol_vo_all']."', `vmest` = '".$lipl['vmest']."', `cost` = '".$lipl['cost']."', `k_stat` = '".$lipl['k_stat']."', `user` = '".$u['id']."';");
                                                    } else { //если есть
                                                        $bag_shmot_cop = $bag_shmot_cop_sql->fetch_array(MYSQLI_ASSOC);
                                                        //добавляем в рюкзак
                                                        $sql->query("UPDATE `users_bag` SET `kol_vo` = '".($bag_shmot_cop['kol_vo'] + $lipl['kol_vo'])."' WHERE `id` = '".$bag_shmot_cop['id']."'");
                                                    }

                                                    //создаём лог
                                                    $sql->query("INSERT INTO `users_logi` SET `user` = '".$u['id']."', `text` = 'Вы нашли [".$lipl['title']."] [x".$lipl['kol_vo']."]', `dtime` = '".date("H:i")."'");

                                                }
                                                //добавление.конец

                                            }

                                        }

                                    }

                                    //отмечаем что сегодня обыскивали
                                    $sql->query("UPDATE `locations_predmet` SET `obsk_date` = '".date("d.m.Y")."' WHERE `id` = '".$cti['id']."'");
                                    //если ничего не выпало создаём лог
                                    if ($kol_vo_luta == 0) $sql->query("INSERT INTO `users_logi` SET `user` = '".$u['id']."', `text` = 'Вы ничего не нашли', `dtime` = '".date("H:i")."'");

                                } else { //если предмет сегодня обыскивали
                                    //создаём лог
                                    $sql->query("INSERT INTO `users_logi` SET `user` = '".$u['id']."', `text` = 'Тут кто-то побывал до вас', `dtime` = '".date("H:i")."'");
                                }

                            }
                            //функция.конец

                            //запускаем функцию
                            cell_target_obsk($sql, $cti, $u);
                            header('Location: ./'); //обновляем страницу

                        }
                        //функция обыска.конец

                    }
                    //обыск.конец
                    //переход на 1 клетку, если игрок с предметом не на 1 клетке
                    if ($cti['x'] != $u['x'] && $cti['y'] != $u['y'] || $cti['x'] != $u['x'] && $cti['y'] == $u['y'] || $cti['x'] == $u['x'] && $cti['y'] != $u['y']) {
                        if ($cell_target_type != 'locations_predmet') {
                            $cell_target_deistv .= '<a class="cell" href="?a=map_go&x='.$cti['x'].'&y='.$cti['y'].'">перейти</a>';
                        } else if ($cti['hod_off'] == 0) {
                            $cell_target_deistv .= '<a class="cell" href="?a=map_go&x='.$cti['x'].'&y='.$cti['y'].'">перейти</a>';
                        }
                    }

                }

                $cell_target_deistv .= '<a class="cell" href="?cell_target_del">сбросить</a>'; //сброс цели

                if ($cell_target_death_on == 0) { //если цель цела/жива
                    //выводим
                    echo $cell_target_hp.''.$cell_target_title.''.$cell_target_kol_vo;

                    //действия с целью
                    echo '</div>';
                    echo '<div class="text">';
                    //выводим сисок возможных действий
                    echo $cell_target_deistv;

                    //сброс цели, если игрок решил сбросить таргет
                    if (isset($_GET['cell_target_del'])) {
                        //сбрасываем тарегт
                        cell_target_del($sql, $u); //запускаем функцию
                        header('Location: ./'); //обновляем страницу
                    }

                } else { //если цель мертва
                    //создаём лог
                    $sql->query("INSERT INTO `users_logi` SET `user` = '".$u['id']."', `text` = 'Цель не найдена', `dtime` = '".date("H:i")."'");
                    //сбрасываем тарегт
                    cell_target_del($sql, $u); //запускаем функцию
                    header('Location: ./'); //обновляем страницу
                }

            } else { //если цель не сушествует
                //создаём лог
                $sql->query("INSERT INTO `users_logi` SET `user` = '".$u['id']."', `text` = 'Цель не найдена', `dtime` = '".date("H:i")."'");
                //сбрасываем тарегт
                cell_target_del($sql, $u); //запускаем функцию
                header('Location: ./'); //обновляем страницу
            }

        } //если цель выбрана.закрываем

        echo '</div>';
        /*меню выбора целей.конец*/

        echo '<div style="margin-top: 2px;" class="line"></div>';

        /*-----КАРТА-----*/
        //функция вывода карты
        function map($sql, $u, $u_conf, $cti) {
            //map - переменная в которой будет находиться html скрипт с картой
            $map = '<table style="border: 0; padding: 0; margin: 2px auto;" cellpadding="0" cellspacing="0">';
            //смотрим минимальный и максимыльный x и y
            $min_x = ($u['x'] - $u_conf['map_size']); //min x
            $max_x = ($u['x'] + $u_conf['map_size']); //max x
            $min_y = ($u['y'] - $u_conf['map_size']); //min y
            $max_y = ($u['y'] + $u_conf['map_size']); //max y

            //смотрим клетки
            $map_kl_sql = $sql->query("SELECT * FROM `locations` WHERE `loc` = '".$u['loc']."' AND `x` >= '".$min_x."' AND `x` <= '".$max_x."' AND `y` >= '".$min_y."' AND `y` <= '".$max_y."'");
            $map_kl_all = $map_kl_sql->num_rows; //смотри сколько
            $map_kl = 0; //переменная для цикла

            if ($map_kl_all != 0) {

                while ($mkli = $map_kl_sql->fetch_array(MYSQLI_ASSOC)) {
                    //увеличиваем счётчик
                    $map_kl++;
                    //указываем координаты
                    $map_kl_x[$map_kl] = $mkli['x'];
                    $map_kl_y[$map_kl] = $mkli['y'];
                    //даём изображение
                    $map_kl_img[$map_kl] = '/img/location/kl/'.$mkli['img'].'.png';
                }

            }

            //смотрим цели на карте
            //смотрим в доме ли стоит игрок
            $map_us_in_home = $sql->query("SELECT * FROM `locations_predmet` WHERE `type` = 'Крыша' AND `loc` = '".$u['loc']."' AND `x` = '".$u['x']."' AND `y` = '".$u['y']."'")->num_rows;

            //смотрим предметы локации
            $map_locations_predmet_sql = $sql->query("SELECT * FROM `locations_predmet` WHERE `loc` = '".$u['loc']."' AND `x` >= '".$min_x."' AND `x` <= '".$max_x."' AND `y` >= '".$min_y."' AND `y` <= '".$max_y."'");
            $map_locations_predmet_all = $map_locations_predmet_sql->num_rows; //смотрим сколько
            $map_lp = 0; //переменная для цикла

            if ($map_locations_predmet_all != 0) { //если предметы есть

                while ($mlpi = $map_locations_predmet_sql->fetch_array(MYSQLI_ASSOC)) {
                    //если это не крыша и игрок не в доме или это крыша и игрок не в доме или это не крыша и игрок в доме
                    if ($mlpi['type'] != 'Крыша' && $map_us_in_home == 0 || $mlpi['type'] == 'Крыша' && $map_us_in_home == 0 || $mlpi['type'] != 'Крыша' && $map_us_in_home != 0) {
                        //прибавляем счётчик
                        $map_lp++;
                        //отмеечаем тип предмета
                        $map_lp_type[$map_lp] = $mlpi['type'];
                        //отмечаем, можноли наступать на этот предмет
                        $map_lp_hod[$map_lp] = $mlpi['hod_off'];
                        $map_lp_lut[$map_lp] = $mlpi['lut_on'];
                        //даём координаты
                        $map_lp_x[$map_lp] = $mlpi['x'];
                        $map_lp_y[$map_lp] = $mlpi['y'];
                        //даём изображение
                        $map_lp_img[$map_lp] = '/img/location/predmet/'.$mlpi['type'].'/'.$mlpi['img'].'.png';
                        //отмечаем слой
                        $map_lp_sloi[$map_lp] = $mlpi['sloi'];
                        //даём ID для таргета(если можно)
                        if ($mlpi['lut_on'] == 1) { //если можно обыскать
                            $map_lp_id[$map_lp] = $mlpi['id'];
                        } else { //если обыскать нельзя
                            $map_lp_id[$map_lp] = 0; //дейсвия к предмету нет
                        }

                    }

                }

            }
            //предметы.конец

            //смотрим юзеров на карте
            $map_users_sql = $sql->query("SELECT * FROM `users` WHERE `online` = '1' AND `loc` = '".$u['loc']."' AND `x` >= '".$min_x."' AND `x` <= '".$max_x."' AND `y` >= '".$min_y."' AND `y` <= '".$max_y."'");
            $map_users_all = $map_users_sql->num_rows; //смотрим сколько
            $map_users = 0; //переменная для цикла

            if ($map_users_all != 0) { //если игроки есть

                //mui - map users inf
                while ($mui = $map_users_sql->fetch_array(MYSQLI_ASSOC)) {
                    //смотрим скин
                    if ($mui['death_on'] == 0) { //если игрок жив
                        //увеличивам счётчик
                        $map_users++;
                        //создаём коардинаты
                        $mui_x[$map_users] = $mui['x'];
                        $mui_y[$map_users] = $mui['y'];
                        //скин
                        $mui_skin[$map_users] = '/img/skin/'.$mui['paul'].'/'.$mui['skin'].'_'.$mui['skin_p'].'.png';
                        //сморим ID для таргета
                        $mui_id[$map_users] = $mui['id'];
                    } else { //если игрок мёртв

                        if ($mui['death_time'] > (time() - 180)) { //если игрок умер недавно
                            //увеличивам счётчик
                            $map_users++;
                            //создаём коардинаты
                            $mui_x[$map_users] = $mui['x'];
                            $mui_y[$map_users] = $mui['y'];
                            //кровь
                            $mui_skin[$map_users] = '/img/death.png';
                            //сморим ID для таргета
                            $mui_id[$map_users] = 0; //0 т.к. цель мертва
                        }

                    }

                }

            }
            //юзеры. конец

            //смотрим ботов на карте
            $map_bots_sql = $sql->query("SELECT * FROM `bots` WHERE `loc` = '".$u['loc']."' AND `x` >= '".$min_x."' AND `x` <= '".$max_x."' AND `y` >= '".$min_y."' AND `y` <= '".$max_y."'");
            $map_bots_all = $map_bots_sql->num_rows; //сморим сколько
            $map_bots = 0; //переменная для цикла

            if ($map_bots_all != 0) { //если боты есть

                //mui - map users inf
                while ($mbi = $map_bots_sql->fetch_array(MYSQLI_ASSOC)) {
                    //смотрим скин
                    if ($mbi['death_on'] == 0) { //если бот жив
                        //увеличивам счётчик
                        $map_bots++;
                        //создаём коардинаты
                        $mbi_x[$map_bots] = $mbi['x']; //x
                        $mbi_y[$map_bots] = $mbi['y']; //y
                        //создаём скин
                        $mbi_skin[$map_bots] = '/img/bots/'.$mbi['skin'].'.png';
                        //сморим ID для таргета
                        $mbi_id[$map_bots] = $mbi['id'];
                    } else { //если бот мёртв

                        if ($mbi['death_time'] > (time() - 180)) { //если бот умер недавно
                            //увеличивам счётчик
                            $map_bots++;
                            //создаём коардинаты
                            $mbi_x[$map_bots] = $mbi['x']; //x
                            $mbi_y[$map_bots] = $mbi['y']; //y
                            //кровь
                            $mbi_skin[$map_bots] = '/img/death.png';
                            //сморим ID для таргета
                            $mbi_id[$map_bots] = 0; //т.к. бот мёртв
                        }

                    }

                }

            }
            //боты. конец


            //смотрим лут на карте
            $map_locations_shmot_sql = $sql->query("SELECT * FROM `locations_shmots` WHERE `loc` = '".$u['loc']."' AND `x` >= '".$min_x."' AND `x` <= '".$max_x."' AND `y` >= '".$min_y."' AND `y` <= '".$max_y."'");
            $map_locations_shmot_all = $map_locations_shmot_sql->num_rows; //кол-во лута
            $map_lut = 0; //переменная для цикла

            if ($map_locations_shmot_all != 0) { //если лут есть

                while ($mshi = $map_locations_shmot_sql->fetch_array(MYSQLI_ASSOC)) {
                    $map_lut++; //увеличиваем счётчик
                    //указываем координаты
                    $mshi_x[$map_lut] = $mshi['x'];
                    $mshi_y[$map_lut] = $mshi['y'];
                    //ид для таргета
                    $mshi_id[$map_lut] = $mshi['id'];
                }

            }
            //лут. конец

            //запускам автоматический расчёт координат и авто-постоение таблицы с кодом
            //координата Y
            for ($y = $max_y; $y >= $min_y; $y--) {
                //по ходу заполняем скрипт
                $map .= '<tr>';

                //координата X
                for ($x = $min_x; $x <= $max_x; $x++) {
                    //добавляем переменную для ссылок
                    $map_a = ''; //пустую
                    //добавляем переменную для передвижения
                    $map_go = 0; //идти можно
                    $map_lut_on = 0;
                    //отмеечам есть ли крыши
                    $map_home_k = 0; //нет

                    //стилизируем клетку
                    $map .= '<style>
                    .map_'.$x.'x'.$y.' {padding: 0; margin: 0; height: '.$u_conf['map_kl_size'].'px; width: '.$u_conf['map_kl_size'].'px; background: '; 

                    //стрелки
                    $map_str = '';

                    if ($x == ($u['x'] - 1) && $y == ($u['y'] + 1)) {
                        $map_str = 'url(/img/s1.png), ';
                    } else if ($x == $u['x'] && $y == ($u['y'] + 1)) {
                        $map_str = 'url(/img/s2.png), ';
                    } else if ($x == ($u['x'] + 1) && $y == ($u['y'] + 1)) {
                        $map_str = 'url(/img/s3.png), ';
                    } else if ($x == ($u['x'] - 1) && $y == $u['y']) {
                        $map_str = 'url(/img/s4.png), ';
                    } else if ($x == ($u['x'] + 1) && $y == $u['y']) {
                        $map_str = 'url(/img/s5.png), ';
                    } else if ($x == ($u['x'] - 1) && $y == ($u['y'] - 1)) {
                        $map_str = 'url(/img/s6.png), ';
                    } else if ($x == $u['x']  && $y == ($u['y'] - 1)) {
                        $map_str = 'url(/img/s7.png), ';
                    } else if ($x == ($u['x'] + 1) && $y == ($u['y'] - 1)) {
                        $map_str = 'url(/img/s8.png), ';
                    }

                    //выводим предметы верхнего слоя
                    if ($map_lp != 0) { //если предметы есть

                        for ($tlp = 1; $tlp <= $map_lp; $tlp++) {
                            //если предмет стоит на этой клетке
                            if ($map_lp_x[$tlp] == $x && $map_lp_y[$tlp] == $y) {
                                //если на этот предмет нельзя вставать
                                if ($map_lp_hod[$tlp] != 0) $map_go++;
                                if ($map_lp_lut[$tlp] != 0) $map_lut_on++;
                                //если предмет верхнего слоя
                                if ($map_lp_sloi[$tlp] == 0) {
                                    //если на этот предмет можно вставать
                                    if ($map_go == 0 && !empty($map_str) && empty($map_a)) {
                                        $map .= $map_str;
                                        $map_a .= '?a=map_go&x='.$x.'&y='.$y;
                                    }
                                    //если предмет - крыша, отмечам что крыша на этой клетке есть
                                    if ($map_lp_type[$tlp] == 'Крыша') $map_home_k++;
                                    //выводим
                                    $map .= 'url('.$map_lp_img[$tlp].'), ';

                                    if (empty($map_a) && $map_lp_id[$tlp] != 0) $map_a .= '?loc_predmet_target='.$map_lp_id[$tlp];

                                }

                            }

                        }

                    }

                    //выводим, если не закрыто крышей
                    if ($map_home_k == 0) {

                        //выводим игроков
                        if ($map_users != 0) { //если игроки есть

                            for ($mu = 1; $mu <= $map_users; $mu++) {

                                if ($mui_x[$mu] == $x && $mui_y[$mu] == $y) { //если на этой клетке
                                    $map .= 'url('.$mui_skin[$mu].'), ';

                                    if (empty($map_a) && $mui_id[$mu] != 0 && $mui_id[$mu] != $u['id']) $map_a .= '?loc_users_target='.$mui_id[$mu];
                                }

                            }

                        }

                        //выводим ботов
                        if ($map_bots != 0) { //если боты есть

                            for ($mb = 1; $mb <= $map_bots; $mb++) {

                                if ($mbi_x[$mb] == $x && $mbi_y[$mb] == $y) {
                                    $map .= 'url('.$mbi_skin[$mb].'), ';

                                    if (empty($map_a) && $mbi_id[$mb] != 0) $map_a .= '?loc_bots_target='.$mbi_id[$mb];
                                }

                            }

                        }

                        //вывод лута
                        if ($map_lut != 0) { //если шмот есть

                            for ($ml = 1; $ml <= $map_lut; $ml++) {

                                if ($mshi_x[$ml] == $x && $mshi_y[$ml] == $y) {
                                    $map .= 'url(/img/location/lut.png), ';

                                    if (empty($map_a)) $map_a .= '?loc_shmots_target='.$mshi_id[$ml];
                                }

                            }

                        }

                        //вывод стрелок
                        if (!empty($map_str) && empty($map_a) && $map_go == 0 && $map_lut_on == 0) {

                            $map .= $map_str;
                            $map_a .= '?a=map_go&x='.$x.'&y='.$y;

                        }

                   }
                   //если нет крыши. конец

                    //выводим предметы нижнего слоя
                    if ($map_lp != 0) { //если предметы есть

                        for ($tlp = 1; $tlp <= $map_lp; $tlp++) {

                            if ($map_lp_sloi[$tlp] == 1) { //если предмет нижнего слоя
                                //если предмет стоит на этой клетке
                                if ($map_lp_x[$tlp] == $x && $map_lp_y[$tlp] == $y) {
                                    //выводим
                                    $map .= 'url('.$map_lp_img[$tlp].'), ';

                                    if (empty($map_a) && $map_lp_id[$tlp] != 0) $map_a .= '?loc_predmet_target='.$map_lp_id[$tlp];
                                }

                            }

                        }

                    }

                    //если крыши нет выводим стрелки для перехода
                    if ($map_home_k == 0) {
                        //вывод стрелок
                        if (!empty($map_str) && empty($map_a) && $map_go == 0) {

                            $map .= $map_str;
                            $map_a .= '?a=map_go&x='.$x.'&y='.$y;

                        }

                    }

                    //кольцо таргета
                    if ($cti != 0) { //ели цель выбрана
                        //если цель стоит на этой клетке
                        if ($cti['x'] == $x && $cti['y'] == $y) {
                            //выводим
                            $map .= 'url(/img/target.png), ';
                        }
                    }
                    //таргет.конец

                    //вывод стиля клетки - земля, вода или др.
                    if ($map_kl != 0) { //если клетка указана в БД
                        
                        for ($mkl = 1; $mkl <= $map_kl; $mkl++) {
                            //если картинка для этой клетки
                            if ($map_kl_x[$mkl] == $x && $map_kl_y[$mkl] == $y) {
                                $map .= 'url('.$map_kl_img[$mkl].'), ';
                            }

                        }

                    }

                    $map .= 'url(/img/location/kl/0.png); background-size: 100% 100%;}
                    </style>';
                    //показ самой клетки
                    $map .= '<td class="map_'.$x.'x'.$y.'">';

                    //ели игрок не стоит на этой клетке
                    if ($x != $u['x'] && $y != $u['y'] || $x != $u['x'] && $y == $u['y'] || $x == $u['x'] && $y != $u['y']) {
                        //если есть переход или взятие в таргет
                        if (!empty($map_a)) {
                            $map .= '<a href="'.$map_a.'"><img width="100%" height="100%" src="./img/location/kl/empty.png"></a>';
                        }
                    }

                    $map .= '</td>';
                }

                $map .= '</tr>';
            }

            $map .= '</table>';

            return $map;
        }
        //функция вывода карты. конец

        //выводим карту
        echo map($sql, $u, $u_conf, $cti); //запускаем функцию
        echo '<div class="line"></div>';
        echo '<div class="text">';
        echo '&bull; Координаты: '.$u['x'].' | '.$u['y'];
        echo '<div style="float: right;">[<a href="?a=mini_map">карта</a>]</div>';
        echo '</div>';
        /*-----КАРТА.КОНЕЦ-----*/

        echo '<div class="line"></div>';

        /*логи*/
        if ($u_conf['logi_on'] == 0) {
            $logi_type = 'вкл';
        } else {
            $logi_type = 'выкл';
        }

        echo '<div class="mmenu">Логи<div style="float: right;">[<a href="?logi_izm">'.$logi_type.'</a>]</div></div>';
        echo '<div class="text">';

        if ($u_conf['logi_on'] == 1) {
            $logi_sql = $sql->query("SELECT * FROM `users_logi` WHERE `user` = '".$u['id']."' ORDER BY `id` DESC LIMIT 0, 10");
            $logi_all = $logi_sql->num_rows;

            if ($logi_all != 0) {

                while ($log = $logi_sql->fetch_array(MYSQLI_ASSOC)) {
                    echo '<font color="#666">['.$log['dtime'].']</font> '.$log['text'].'<br/>';
                }

            } else {
                echo 'Пусто';
            }

        }

        echo '</div>';

        /*логи.конец*/

        echo '<div class="line"></div>';

        /*чат*/
        if ($u_conf['chat_on'] == 0) {
            $chat_type = 'вкл';
        } else {
            $chat_type = 'выкл';
        }

        echo '<div class="mmenu">Чат<div style="float: right;">[<a href="?chat_izm">'.$chat_type.'</a>]</div></div>';
        echo '<div class="text">';

        if ($u_conf['chat_on'] == 1) { //если чат включен

            if (isset($_POST['mail_in_chat'])) { //если нажата кнопка "отправить"
                $chat_text = strip_tags($_POST['chat_text']); //удаляем HTML теги

                if (!empty($chat_text)) { //если смс не пустое, добавляем и обновляем страницу
                    $sql->query("INSERT INTO `chat` SET `name` = '".$u['name']."', `text` = '".$chat_text."', `dtime` = '".date("H:i")."', `timer` = '".time()."';");
                    header('Location: ./');
                }
            }

            echo '<form method="post" action="">';
            echo 'Текст: <input style="width: 50%;" type="text" name="chat_text" placeholder="Текст сообщения" value=""/> ';
            echo '<input type="submit" name="mail_in_chat" value="Отправить"/>';
            echo '</form>';
            echo '</div>';
            echo '<div class="line"></div>';
            echo '<div class="text">';

            $chat_mail_sql = $sql->query("SELECT * FROM `chat` ORDER BY `id` DESC LIMIT 0, 10");
            $chat_mail_all = $chat_mail_sql->num_rows;

            if ($chat_mail_all != 0) { //если сообщения есть

                while ($cm = $chat_mail_sql->fetch_array(MYSQLI_ASSOC)) { //выводим
                    echo '<font color="#666">['.$cm['dtime'].']</font> '.$cm['name'].': '.$cm['text'].'<br/>';
                }
            } else { //иначе
                echo 'Сообщений нет';
            }

        }

        echo '</div>';
        /*чат.конец*/

        //изменение типа лог/чата
        if (isset($_GET['logi_izm']) || isset($_GET['chat_izm'])) { //если игрок захотел сменить тип чего либо

            if (isset($_GET['logi_izm'])) { //если игрок захотел сменить логи
                $type = 'logi_on'; //отмечаем
                $type2 = $u_conf['logi_on']; //отмечаем
            } else { //если игрок захотел сменить чат
                $type = 'chat_on'; //отмечам
                $type2 = $u_conf['chat_on']; //отмечаем
            }

            //если включено
            if ($type2 == 1) {
                $type2 = 0; //выключаем
            } else { //если выключено
                $type2 = 1; //включаем
            }

            //меняем
            $sql->query("UPDATE `users_setting` SET `".$type."` = '".$type2."' WHERE `id` = '".$u['id']."'");

            //обновляем страницу
            header('Location: ./');

        }
        //функция изменения. конец

        /*чат.конец*/
        break;
        /*-----ЛОКАЦИЯ.КОНЕЦ-----*/

        /*-----МИНИ-КАРТА-----*/
        case 'mini_map':
        echo '<div class="menu">Мини-карта</div>';

        $mini_map = $sql->query("SELECT * FROM `locations` WHERE `loc` = '".$u['loc']."' AND `x` >= '".($u['x'] - 15)."' AND `x` <= '".($u['x'] + 15)."' AND `y` >= '".($u['y'] - 15)."' AND `y` <= '".($u['y'] + 15)."'");
        $map_arr = array();

        while ($mm = $mini_map->fetch_array(MYSQLI_ASSOC)) {
            $map_arr[$mm['x']][$mm['y']] = '#5F4C0B';
        }

        $mini_map_bot = $sql->query("SELECT * FROM `bots` WHERE `death_on` = '0' AND `loc` = '".$u['loc']."' AND `x` >= '".($u['x'] - 15)."' AND `x` <= '".($u['x'] + 15)."' AND `y` >= '".($u['y'] - 15)."' AND `y` <= '".($u['y'] + 15)."'");

        while ($mmb = $mini_map_bot->fetch_array(MYSQLI_ASSOC)) {

            if ($mmb['type'] == 0) {
                $type = '#DF7401';
            } else if ($mmb['type'] == 1) {
                $type = '#FF0000';
            } else {
                $type = '#00FF00';
            }

            $map_arr[$mmb['x']][$mmb['y']] = $type;

        }

        $mini_map_us = $sql->query("SELECT * FROM `users` WHERE `id` != '".$u['id']."' AND `online` = '1' AND `death_on` = '0' AND `loc` = '".$u['loc']."' AND `x` >= '".($u['x'] - 15)."' AND `x` <= '".($u['x'] + 15)."' AND `y` >= '".($u['y'] - 15)."' AND `y` <= '".($u['y'] + 15)."'");

        while ($mmu = $mini_map_us->fetch_array(MYSQLI_ASSOC)) {
            $map_arr[$mmu['x']][$mmu['y']] = '#01A9DB';
        }

        echo '<center>';
        echo '<table border="0" cellspacing="0" cellpadding="0" style="padding: 0; margin: 0;">';

        //координата Y
        for ($y = ($u['y'] + 15); $y >= ($u['y'] - 15); $y--) {
            echo '<tr>';

            //координата X
            for ($x = ($u['x'] - 15); $x <= ($u['x'] + 15); $x++) {

                if (empty($map_arr[$x][$y])) {
                    $img = '#61380B';
                } else {
                    $img = $map_arr[$x][$y];
                }

                if ($x == $u['x'] && $y == $u['y']) $img = '#0000FF';

                echo '<td style="height: 5px; width: 5px; background: '.$img.';"></td>';

            }

            echo '</tr>';

        }

        echo '</table>';
        echo '</center>';

        echo '<div class="line"></div>';
        echo '<div class="foot_a">';
        echo '<a href="./">Закрыть</a>';
        echo '</div>';
        break;
        /*-----МИНИ-КАРТА.КОНЕЦ-----*/

        /*-----ПЕРЕДВИЖЕНИЕ-----*/
        case 'map_go':
        //функция передвижения по карте
        //если игрок хочет перейти по карте и указаны координаты перехода
        if (isset($_GET['x']) && isset($_GET['y'])) {
            //функция
            function map_go($sql, $u, $x, $y) {
                if ($u['en'] > 0) { //если энергия есть

                    if (($u['speed_hod'] - (time() - $u['speed_hod_all'])) <= 0) { //скорость бега

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

                        //если нет перегруза
                        if ($perenos_ves <= $perenos_ves_all) {
                            //если дальность перехода нормальная
                            if ($x >= ($u['x'] - 1) && $x <= ($u['x'] + 1) && $y >= ($u['y'] - 1) && $y <= ($u['y'] + 1)) {
                                //проверка на препятсвия
                                $hod_off = $sql->query("SELECT * FROM `locations_predmet` WHERE `hod_off` = '1' AND `loc` = '".$u['loc']."' AND `x` = '".$x."' AND `y` = '".$y."'")->num_rows;

                                //если припепятсвий нет
                                if ($hod_off == 0) {
                                    //если игрок не стоит здесь
                                    if ($x != $u['x'] && $y != $u['y'] || $x != $u['x'] && $y == $u['y'] || $x == $u['x'] && $y != $u['y']) {
                                        //поворот в нужную сторону
                                        if ($x == ($u['x'] - 1) && $y == ($u['y'] + 1)) {
                                            $skin_p = 1;
                                        } else if ($x == $u['x'] && $y == ($u['y'] + 1)) {
                                            $skin_p = 2;
                                        } else if ($x == ($u['x'] + 1) && $y == ($u['y'] + 1)) {
                                            $skin_p = 3;
                                        } else if ($x == ($u['x'] - 1) && $y == $u['y']) {
                                            $skin_p = 4;
                                        } else if ($x == ($u['x'] + 1) && $y == $u['y']) {
                                            $skin_p = 5;
                                        } else if ($x == ($u['x'] - 1) && $y == ($u['y'] - 1)) {
                                            $skin_p = 6;
                                        } else if ($x == $u['x']  && $y == ($u['y'] - 1)) {
                                            $skin_p = 7;
                                        } else if ($x == ($u['x'] + 1) && $y == ($u['y'] - 1)) {
                                            $skin_p = 8;
                                        }

                                        //переходим
                                        $sql->query("UPDATE `users` SET `speed_hod` = '".time()."', `en` = '".($u['en'] - 1)."', `x` = '".$x."', `y` = '".$y."', `skin_p` = '".$skin_p."' WHERE `id` = '".$u['id']."'");

                                        //прокачиваем навык который качается при переходах по локе
                                        $naw_location_hod_sql = $sql->query("SELECT * FROM `users_naw` WHERE `kach_ot` = 'location_hod' AND `user` = '".$u['id']."'");
                                        $naw_location_hod_all = $naw_location_hod_sql->num_rows;

                                        if ($naw_location_hod_all != 0) {
                                            $naw = $naw_location_hod_sql->fetch_array(MYSQLI_ASSOC);
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


                                    } else { //если игрок стоит тут
                                        $sql->query("INSERT INTO `users_logi` SET `user` = '".$u['id']."', `text` = 'Вы стоите сдесь', `dtime` = '".date("H:i")."'");
                                    }

                                } else { //если есть препядствия
                                    //создаём лог
                                    $sql->query("INSERT INTO `users_logi` SET `user` = '".$u['id']."', `text` = 'Туда нельзя идти', `dtime` = '".date("H:i")."'");
                                }

                            } else { //если введёные координаты высоки
                                $sql->query("INSERT INTO `users_logi` SET `user` = '".$u['id']."', `text` = 'Далеко', `dtime` = '".date("H:i")."'");
                            }

                        } else { //если игрок перегружен
                            $sql->query("INSERT INTO `users_logi` SET `user` = '".$u['id']."', `text` = 'Вы перегружены', `dtime` = '".date("H:i")."'");
                        }

                    } else { //если игрок решил переходить слишком быстро
                        $sql->query("INSERT INTO `users_logi` SET `user` = '".$u['id']."', `text` = 'Слишком быстро', `dtime` = '".date("H:i")."'");
                    }

                } else { //если энергии нет
                    $sql->query("INSERT INTO `users_logi` SET `user` = '".$u['id']."', `text` = 'Отдохните', `dtime` = '".date("H:i")."'");
                }


            }

            //запускаем функцию
            map_go($sql, $u, $_GET['x'], $_GET['y']);
        }
        //передвижение.конец
        header('Location: ./'); //идём на главную
        break;
        /*-----ПЕРЕДВИЖЕНИЕ.КОНЕЦ-----*/




        

    }

} else { //если не авторизирован

    switch($_GET['a']) {

        //ГЛАВНАЯ
        default:
        echo '<div class="menu">Главная</div>';
        echo '<img width="100%" src="./img/logo_index.png">';
        echo '<div class="line"></div>';
        echo '<div class="text" style="text-align: center;">'.$conf['index_text'].'</div>';
        echo '<div class="line"></div>';

        //смотрим сколько игроков
        $playersAll = $sql->query("SELECT * FROM `users`")->num_rows; //всего
        $playersOn = $sql->query("SELECT * FROM `users` WHERE `online` = '1'")->num_rows; //онлайн

        echo '<div class="mmenu">Статистика</div>';
        echo '<div class="text">';
        echo '&bull; Всего игроков: '.$playersAll.'<br/>'; //выводим
        echo '&bull; Сейчас играют: '.$playersOn.'<br/>'; //выводим
        echo '&bull; Сервер: ';

        if ($conf['server'] != 0) { //если сервер включен
            echo '<font color="#00FF00">on</font>';
        } else { //если выключен
            echo '<font color="#FF0000">off</font>';
        }

        echo '</div>';
        echo '<div class="line"></div>';
        echo '<ul class="links">';
        echo '<li><a href="?a=login">&rArr; Войти в игру</a></li>';
        echo '<li><a href="?a=reg">&rArr; Зарегистрироваться</a></li>';
        echo '<li><a href="http://vk.com/club91891019" target="_blank">&rArr; Группа ВКонтакте</a></li>';
        echo '</ul>';
        break;
        //ГЛАНАЯ.КОНЕЦ

        //ВХОД В ИГРУ
        case 'login':
        echo '<div class="menu">Вход в игру</div>';

        if ($conf['server'] != 0) { //если сервер включен

            if (isset($_POST['enter'])) { //если обнаружена попытка входа
                $login = $_POST['login']; //смотрим введённый логин
                $pass = md5($_POST['pass']); //пароль

                //смотрим юзера
                $loginSQL = $sql->query("SELECT * FROM `users` WHERE `mail` = '".$login."' AND `pass` = '".$pass."'");
                $loginRow = $loginSQL->num_rows;

                if ($loginRow != 0) { //если такой акк есть
                    $uid = $loginSQL->fetch_array(MYSQLI_ASSOC); //сморим акк

                    if (!isset($_POST['session'])) { //если игрок на долго
                        setcookie("userID", $uid['id'], (time() + (86400 * 7))); //забиваем логин в куки
                        setcookie("userMAIL", $login, (time() + (86400 * 7))); //пароль
                    } else { //если на 1 сеанс
                        $_SESSION['userID'] = $uid['id']; //забиваем логи в сессии
                        $_SESSION['userMAIL'] = $login; //пароль
                    }

                    //отмечаем что игрок вошёл
                    $sql->query("UPDATE `users` SET `online` = '1', `online_timer` = '".time()."' WHERE `id` = '".$uid['id']."'");

                    header('Location: ./'); //обновляем страницу
                    exit;
                } else { //если сервер выключен
                    echo '<div class="text">';
                    echo '<font color="#FF0000">&times; Не верные данные!</font>';
                    echo '</div>';
                    echo '<div class="line"></div>';
                }

            }

            echo '<div class="mmenu">Форма входа</div>';
            echo '<div class="text">';
            echo '<form method="post" action="">';
            echo '&bull; E-mail:<br/>';
            echo '<input type="email" name="login" value="@"/><br/>';
            echo '&bull; Пароль:<br/>';
            echo '<input type="password" name="pass" value=""/><br/>';
            echo '<input type="checkbox" name="session" value=""/> Не запоминать<br/><font color="#666">* Если вашь браузер не поддерживает cookie поставьте галочку</font><br/>';
            echo '<input type="submit" name="enter" value="Войти"/>';
            echo '</form>';
            echo '</div>';
        } else {
            echo '<div class="text">Сервер выключен, авторизация не доступна</div>';
        }

        echo '<div class="line"></div>';
        echo '<ul class="links">';
        echo '<li><a href="./">&rArr; Главная</a></li>';
        echo '</ul>';
        break;
        //ВХОД.КОНЕЦ

        //РЕГИСТРАЦИЯ
        case 'reg':
        echo '<div class="menu">Регистрация</div>';

        $mail = '@';
        $name = '';

        if (isset($_POST['add_akk'])) {
            $error = '';
            $empty_name = $sql->query("SELECT * FROM `users` WHERE `name` = '".$_POST['name']."'")->num_rows;
            $empty_mail = $sql->query("SELECT * FROM `users` WHERE `mail` = '".$_POST['mail']."'")->num_rows;

            if ($empty_name != 0) $error .= '&times; Ник занят<br/>';
            if ($empty_mail != 0) $error .= '&times; E-mail занят<br/>';
            if (mb_strlen($_POST['name']) < 3 || mb_strlen($_POST['name']) > 16) $error .= '&times; Длина ника должна быть от 3 до 16 символов<br/>';
            if (mb_strlen($_POST['pass']) < 6 || mb_strlen($_POST['pass']) > 20) $error .= '&times; Длина пароля должна быть от 6 до 20 символов<br/>';
            if ($_POST['pass'] != $_POST['povt_pass']) $error .= '&times; Пароли не совпадают<br/>';
            if (!preg_match('/^[a-zA-Z0-9]{0,}$/iu', $_POST['name'])) $error .= '&times; Ник содержит запрещённые символы<br/>';

            if (empty($error)) {
                $sql->query("INSERT INTO `users` SET `name` = '".$_POST['name']."', `pass` = '".md5($_POST['pass'])."', `mail` = '".$_POST['mail']."', `paul` = '".$_POST['paul']."', `date_reg` = '".date("d.m.Y")."', `skin_p` = '7', `money` = '500', `slots_naw` = '3', `stats_points` = '3', `str` = '5', `str_k` = '5', `agi` = '5', `agi_k` = '5', `hp` = '50', `hp_all` = '50', `hp_k` = '50', `dex` = '5', `dex_k` = '5', `def` = '0', `speed_hod_all` = '3', `en` = '50', `en_all` = '50', `x` = '5', `y` = '5', `ves_all` = '30.000', `cell_target_id` = '3', `cell_target_type` = 'bots'");
                $us_id = $sql->query("SELECT * FROM `users` WHERE `name` = '".$_POST['name']."'")->fetch_array(MYSQLI_ASSOC);
                $sql->query("INSERT INTO `users_setting` SET `id` = '".$us_id['id']."'");
                $sql->query("INSERT INTO `users_mail` SET `timer` = '".time()."', `date` = '".date("d.m.Y")."', `ot_us` = 'Администратор', `title` = 'Добро пожаловать!', `text` = 'Добро пожаловать!<br/>Для начала распредели навыки, потом иди к мэру города и возьми у него задание \"Обход\". Затем иди к учителю и изучи 1 навык понравившийся тебе. Оружие и патроны к нему, а так же броню можеш купить у торговцев.<br/>Удачной игры!', `user` = '".$_POST['name']."';");

                echo '<font color="#00FF00">Вы успешно зарегистрировались!<br/>
                Данные для входа были отправлены на адрес <b>'.$_POST['mail'].'</b></font>';
                echo '<div class="line"></div>';

                mail($_POST['mail'], 'Данные для входа', 'Здравствуйте, '.$_POST['name'].'.<br/>Ваш пароль: '.$_POST['pass'].'<br/>Администрация wap-game.16mb.com<br/>*Не нужно отвечать на это письмо!', 'Content-type: text/html; charset=utf-8;');

            } else {
                $mail = $_POST['mail'];
                $name = $_POST['name'];

                echo '<font color="#FF0000">'.$error.'</font>';
                echo '<div class="line"></div>';

            }

        }
        
        echo '<div class="mmenu">Форма регистрации</div>';
        echo '<div class="text">';
        echo '<form method="post" action="">';
        echo '&bull; E-mail:<br/>';
        echo '<input type="email" name="mail" value="'.$mail.'"/><br/>';
        echo '&bull; Ник<font color="#666">(3-16 символов)</font>:<br/>';
        echo '<input type="text" name="name" value="'.$name.'"/><br/>';
        echo '&bull; Пол:<br/>';
        echo '<select name="paul">';
        echo '<option value="0">мужчина</option>';
        echo '<option value="1">женщина</option>';
        echo '</select><br/>';
        echo '&bull; Пароль<font color="#666">(6-20 символов)</font>:<br/>';
        echo '<input type="password" name="pass" value=""/><br/>';
        echo '&bull; Пароль<font color="#666">(повторите)</font>:<br/>';
        echo '<input type="password" name="povt_pass" value=""/><br/>';
        echo '<input type="submit" name="add_akk" value="Регистрация"/>';
        echo '</form>';
        echo '</div>';
        echo '<div class="line"></div>';
        echo '<ul class="links">';
        echo '<li><a href="./">&rArr; Главная</a></li>';
        echo '</ul>';
        break;
        //РЕГИСТРАЦИЯ.КОНЕЦ

    }
}

include './foot.php';
?>