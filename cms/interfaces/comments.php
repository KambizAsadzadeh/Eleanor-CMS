<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
 */
namespace CMS\Interfaces;

/** АПИ для взаимодействия системы с модулем в вопросе комментарием */
interface Comments
{
	/** Получение ссылки на комментарий
	 * @param array $ids Идентификатор контента
	 * @return array of [URL,title] */
	public function Link2Comment($ids);

	/** Обновление счетчика комментариев
	 * @param array $changes идентификатор контента=>изменение числа комментариев, например +2 или -4 */
	public function UpdateCommentsCounter($changes);
}