<?php
class ListInfo
{
	public $mainTable = "";
	public $conditions = array();
	public $searchConditions = array();
	
	public function Start($sub)
	{
		$this->mainTable = $sub;
	}
}
?>