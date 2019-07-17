<?php
require_once 'header.php';

$gettime = gettime() - $gettime;

echo '</div>';
echo '<div class="foot">';
echo 'Generation: '.substr($gettime, 0, 6).'<br/>';
echo date("d.m.Y").'<br/>';
echo date("H:i");
echo '<a href="http://vk.com/id212888361">Автор</a>';
echo '</div>';
echo '</body>';
?>