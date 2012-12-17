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
class CacheMachineMemCache implements CacheMachineInterface
{	private
		$u,#������������ ��� ������
		$n=array(''=>true),#������ ���� ����, ��� � ��� ���� � ����.
		$M=false;#������ MemCache-a

	/**
	 * ����������� ��� ������
	 *
	 * @param string $u ������ ������������ ���� (�� ����� ��� ������ ����� ���� �������� ��������� ����� Eleanor CMS)
	 */
	public function __construct($u='')
	{
		$this->u=$u;
		Eleanor::$nolog=true;
		$this->M=memcache_connect('localhost');
		Eleanor::$nolog=false;
		if(!$this->M)
			throw new Exception('MemCache failure '.__file__);

		#memcache_add_server($this->M, 'server', 'port');

		memcache_set_compress_threshold($this->M,20000,0.2);
		$this->n=$this->Get('');
		if(!$this->n or !is_array($this->n))
			$this->n=array();
	}

	public function __destruct()
	{		$this->Put('',$this->n);
		if($this->M)
			memcache_close($this->M);
	}

	/**
	 * ������ ��������
	 *
	 * @param string $k ����. �������� ��������, ��� ����� ������������� �������� � ���� ���1_���2 ...
	 * @param mixed $value ��������
	 * @param int $t ����� ����� ���� ������ ���� � ��������
	 */
	public function Put($k,$v,$t=0)
	{		$r=$this->M ? memcache_set($this->M,$this->u.$k,$v,is_bool($v) || is_int($v) || is_float($v) ? 0 : MEMCACHE_COMPRESSED,$t) : false;
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
		$r=memcache_get($this->M,$this->u.$k);
		if($r===false)
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
		return memcache_delete($this->M,$this->u.$k);
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