<?php
return array(
	#Для /addons/admin/modules/smiles.php
	'emotion'=>'Эмоция',
	'emotion_'=>'Эмоция в тексте. Например: :-) :-* ;-) . Можно указать несколько через запятую.',
	'path'=>'Путь к смайлу',
	'preview'=>'Предпросмотр',
	'pos'=>'Позиция',
	'pos_'=>'Оставьте пустым для добавления в конец',
	'status'=>'Активен',
	'show'=>'Отображать в списке',
	'gadd'=>'Групповое добавление',
	'fdne'=>'Введенный каталог не существует.',
	'emoexists'=>function($em){return count($em)>1 ? 'Эмоции '.join(', ',$em).' уже существуют.' : 'Эмоция '.join($em).' уже существует.';},
	'smnots'=>'Вы не выбрали ни одного смайла для добавления.',
	'smnf'=>'В данном каталоге новые смайлы не обнаружены.',
	'delc'=>'Подтверждение удаления',
	'list'=>'Список смайлов',
	'adding'=>'Добавление смайла',
	'editing'=>'Редактирование смайла',
);