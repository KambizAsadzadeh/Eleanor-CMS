<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
 */
namespace CMS\Interfaces;

/** Набор методов для создания файлов sitemap.xml */
interface SitemapGenerator
{
	/** Конфигуратор SiteMap-а
	 * @return array */
	public function SitemapConfigure();

	/** Получение потенциального количества генерируемых ссылок (для возможности публикации прогрессбара)
	 * @param mixed $data Данные, полученные от метода SitemapGenerate на предыдущем этапе
	 * @param array $conf Конфигурация от SitemapConfigure
	 * @return number */
	public function SitemapAmount($data,$conf);

	/** Генератор карты сайта
	 * @param mixed $data Данные, полученные от этого метода на предыдущем этапе
	 * @param array $conf Конфигурация полученная от метода SitemapConfigure
	 * @param callable $callback Функция, которую следует вызать для отправки результата
	 * @param array $opts Опции, ключи:
	 *  int limit Рекомендуемое количество ссылок для генерации за раз */
	public function SitemapGenerate($data,$conf,$callback,$opts);
}