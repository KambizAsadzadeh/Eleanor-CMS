<?php
/** Оформление содержимого блока "Вертикальное одноуровневое меню"
 * @var array $var_0 меню: каждый элемент - готовая ссылка <a href="...">...</a> */
defined('CMS\STARTED')||die;

echo'<nav><ul class="navs menu"><li>',join('</li><li>',$var_0),'</ul></nav>';