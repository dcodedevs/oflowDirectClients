<?php
class Field
{
	var $sqlname;
	var $formname;
	var $htmlname;
	var $database;
	var $extra;
	var $default;
	var $multilanguage;
	var $type;
	var $hidden;
	var $readonly;
	var $extravalue;
	var $update;
	var $insert;
	var $mandatory;
	var $duplicate;
	var $fieldwidth;
	
	
	function init($tsql, $tforn, $thtml, $tdatab, $ttype, $tex, $tdef, $hiddent, $readonlyt,$extravaluet,$upd,$ins,$mand,$dup,$tfil)
	{
		$this->sqlname = $tsql;
		$this->formname = $tforn;
		$this->htmlname = $thtml;
		$this->database = $tdatab;
		$this->type = $ttype;
		$this->extra = $tex;
		$this->default = $tdef;
		$this->hidden = $hiddent;
		$this->readonly = $readonlyt;
		$this->extravalue = $extravaluet;
		$this->update = $upd;
		$this->insert = $ins;
		$this->mandatory = $mand;
		$this->duplicate = $dup;	
		$this->fieldwidth = $tfil;
	}
}
?>