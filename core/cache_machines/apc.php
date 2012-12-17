<?php
/*
	Copyright � Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym
*/
class CacheMachineApc implements CacheMachineInterface
{	private
		$u,#������������ ��� ������
		$n=array(''=>true);#������ ���� ����, ��� � ��� ���� � ����.

	/**
	 * ����������� ��� ������
	 *
	 * @param string $u ������ ������������ ���� (�� ����� ��� ������ ����� ���� �������� ��������� ����� Eleanor CMS)
	 */
	public function __construct($u='')
	{
		$this->u=$u;
		$this->n=$this->Get('');
		if(!$this->n or !is_array($this->n))
			$this->n=array();
	}

	public function __destruct()
	{
		$this->Put('',$this->n);
	}

	/**
	 * ������ ��������
	 *
	 * @param string $k ����. �������� ��������, ��� ����� ������������� �������� � ���� ���1_���2 ...
	 * @param mixed $value ��������
	 * @param int $t ����� ����� ���� ������ ���� � ��������
	 */
	public function Put($k,$v,$t=0)
	{
		$r=apc_store($this->u.$k,$v,$t);
		if($r)
			$this->n[$k]=$t+time();
		return$r;
	}

	/**
	 * ��������� ������ �� ����
	 *
	 * @param string $k ����
	 */
	public function Get($k)
	{
		if(!isset($this->n[$k]))
			return false;
		$r=apc_fetch($this->u.$k,$s);
		if(!$s)
			unset($this->n[$k]);
		return$r;
	}

	/**
	 * �������� ������ �� ����
	 *
	 * @param string $k ����
	 */
	public function Delete($k)
	{
		unset($this->n[$k]);
		return apc_delete($this->u.$k);
	}

	/**
	 * �������� ������� �� ����. ���� ��� ���� ������ - ��������� ���� ���.
	 *
	 * @param string $t ���
	 */
	public function DeleteByTag($t)
	{
		foreach($this->n as $k=>&$v)
			if($t=='' or strpos($k,$t)!==false)
				$this->Delete($k);
	}
}