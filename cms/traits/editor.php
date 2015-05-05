<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS\Traits;
use \CMS\Eleanor, \Eleanor\Classes\EE;

/** Повторное использование кода для классов \CMS\Editor и \CMS\Saver */
trait Editor
{
	public static
		/** @var array Перечень всех возможных редакторов */
		$editors=[
			'no'=>'Textarea',
			'bb'=>'Eleanor BB Editor',
			'ckeditor'=>'CKEditor',
			'tinymce'=>'TinyMCE',
			'codemirror'=>'CodeMirror',
		],

		/** @var array Перечень визуальных редакторов */
		$wysiwyg=['ckeditor','tinymce'];

	public
		/** @var bool Включить использование смайлов */
		$smiles,

		/** @var bool Включить использование OwnBB */
		$ownbb;

	private
		/** @var bool Флаг визуального редактора (синоним wysiwyg) */
		$visual,

		/** @var string Тип редактора */
		$type;

	/** Конструктор
	 * @param string|null $type
	 * @param bool $smiles Включить использование смайлов
	 * @param bool $ownbb Включить использование OwnBB
	 * @throws EE */
	public function __construct($type=null,$smiles=true,$ownbb=true)
	{
		if(!isset(Eleanor::$vars['editor_type']))
			\CMS\LoadOptions('editor');

		if(!$type and !$type=Eleanor::$Login->Get('editor'))
			$type=Eleanor::$vars['editor_type'];

		if(!isset(static::$editors[ $type ]))
			throw new EE('Unknown type of editor: '.$type,EE::DEV);

		$this->type=$type;
		$this->smiles=$smiles;
		$this->ownbb=$ownbb;
		$this->visual=in_array($type,static::$wysiwyg);
	}

	/** Получение дампа смайлов */
	public static function GetSmiles()
	{
		$smiles=Eleanor::$Cache->Get('smiles',false);

		if($smiles===false)
		{
			$smiles=[];
			$R=Eleanor::$Db->Query('SELECT `path`,`emotion`,`show` FROM `'.\CMS\P
				.'smiles` WHERE `status`=1 ORDER BY `pos` ASC');
			while($smile=$R->fetch_assoc())
			{
				$smile['emotion']=explode(',,',trim($smile['emotion'],','));
				$smiles[]=$smile;
			}

			Eleanor::$Cache->Put('smiles',$smiles,0,false);
		}

		return$smiles;
	}
}