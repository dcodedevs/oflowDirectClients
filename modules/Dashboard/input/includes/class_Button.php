<?php
class Button
{
	var $buttonnamelist;
	var $buttonname;
	var $selectedbutton;
	var $tableconnect;
	var $hiddenfield;
	var $hiddendirectfield;
	var $tableconnectmodule;
	var $content_status;
	
	function init( $tforn, $thtml, $tdatab, $ttype, $tex, $tdef, $tconnmod, $tstatus)
	{
		if(trim($tstatus)!="") $tstatus = intval($tstatus);
		$this->buttonnamelist = $tforn;
		$this->buttonname = $thtml;
		$this->selectedbutton = $tdatab;
		$this->tableconnect = $ttype;
		$this->hiddenfield = $tex;
		$this->hiddendirectfield = $tdef;
		$this->tableconnectmodule = $tconnmod;
		$this->content_status = $tstatus;
	}
}
?>