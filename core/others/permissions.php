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

final class Permissions extends BaseClass
{
	private
		$lc,#Login class

		$moderate,#���� ��������� ����������
		$is_admin,#���� ������
		$max_upload,#������������ ������ ������������ �����
		$hcaptcha,#���� ������� �����
		$sh_cls,#����� ������ ��������� �����
		$banned,#���� ���� ������
		$fl,#Flood limit
		$sl;#Search limit

	/**
	 * ����������� ����������, ��� ������� ������ ������������ ���� ����������
	 *
	 * @param string $lc �������� ������ ������, �� ������� �������� ������� ������ ����������
	 */
	public function __construct($lo)
	{
		$this->lc=$lo;
	}

	/**
	 * �������� �������� �� ������������ ���� � ������ ��������������
	 */
	public function IsAdmin()
	{
		if(!isset($this->is_admin))
		{
			$v=Eleanor::GetPermission('access_cp',$this->lc);
			$this->is_admin=in_array(1,$v);
		}
		return$this->is_admin;
	}

	/**
	 * ����������� ������������� ������� ������������ �����
	 *
	 * @return bool|int true - ��� �����������, false - ������ ��������� �����, (int) - ������ � ������
	 */
	public function MaxUpload()
	{
		if(!isset($this->max_upload))
		{
			$v=Eleanor::GetPermission('max_upload',$this->lc);
			if(in_array(1,$v))
				return$this->max_upload=true;
			sort($v,SORT_NUMERIC);
			$bytes=(int)end($v);
			return$this->max_upload=$bytes<1 ? false : $bytes*1024;
		}
		return$this->max_upload;
	}

	/**
	 * �������� ������� �� ������������
	 */
	public function IsBanned()
	{
		if($this->IsAdmin())
			return false;
		if(!isset($this->banned))
		{
			$v=Eleanor::GetPermission('banned',$this->lc);
			$this->banned=in_array(1,$v);
		}
		return$this->banned;
	}

	/**
	 * �������� ����������� ���������� ����� ��� ������������
	 */
	public function HideCaptcha()
	{
		if(!isset($this->hcaptcha))
		{
			$v=Eleanor::GetPermission('captcha',$this->lc);
			$this->hcaptcha=in_array(0,$v);
		}
		return$this->hcaptcha;
	}

	/**
	 * �������� ������� ����������� ������������� �������� ����
	 */
	public function ShowClosedSite()
	{
		if(!isset($this->sh_cls))
		{
			$v=Eleanor::GetPermission('sh_cls',$this->lc);
			$this->sh_cls=in_array(1,$v);
		}
		return$this->sh_cls;
	}

	/**
	 * ����������� ������������ ���������� ������� � �������� ����� ����������� 2� ���������� (��������, ������������ � �.�.)
	 */
	public function FloodLimit()
	{
		if(!isset($this->fl))
		{
			$v=Eleanor::GetPermission('flood_limit',$this->lc);
			$this->fl=min($v);
		}
		return$this->fl;
	}


	/**
	 * ����������� ������������ ���������� ������� � �������� ����� 2�� ���������� ���������
	 */
	public function SearchLimit()
	{
		if(!isset($this->sl))
		{
			$v=Eleanor::GetPermission('search_limit',$this->lc);
			$this->sl=min($v);
		}
		return$this->sl;
	}

	/**
	 * �������� ������� ����������� ���������� ���������� ��� �� ������������
	 */
	public function Moderate()
	{
		if(!isset($this->moderate))
		{
			$v=Eleanor::GetPermission('moderate',$this->lc);
			$this->moderate=in_array(1,$v);
		}
		return$this->moderate;
	}
}