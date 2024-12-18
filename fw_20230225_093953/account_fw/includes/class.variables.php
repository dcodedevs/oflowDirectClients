<?php
class Variables
{
	var $languageDir;
	var $languageDir2;
	var $languageID;
	var $levels;
	var $contentTable;
	var $pageID;
	var $outputFolder;
	var $contentID;
	var $choosenLevel;
	var $logget;
	var $loggID;
	var $mainlayout;
	var $errormessage;
	var $loginerror;
	var $userID;
	var $errormessageTop;
	var $errorID;
	var $ipinformation;
	var $defaultLanguageID;
	var $userCountry;
	var $micro;
	var $micpos;
	var $useraccessarray;
	var $useradminaccess;
	var $developeraccess;
	var $userConnectedToId;
	var $useradmin;
	var $companyaccessID;
	var $sessionID;
	var $userprofileimage;
	var $webBase;
	
	function __construct(array $properties=array()){
      foreach($properties as $key => $value){
        $this->{$key} = $value;
      }
    }
	
	function start($lang, $langID, $lev, $cont, $pag, $opfolder, $contID, $choosenLev ,$lg, $main, $errorm, $loginme, $uID, $langDir2, $errormsgtop, $error, $ipinfo,$defaultlang, $userCountry, $micr, $micrpos, $uaccessarray, $uadminaccess, $sessID)
	{
		$this->languageDir = $lang;
		$this->languageID = $langID;
		$this->levels = $lev;
		$this->contentTable = $cont;
		$this->pageID = $pag;
		$this->outputFolder = $opfolder;
		$this->contentID = $contID;
		$this->choosenLevel = $choosenLev;
		$this->logget = 0;
		$this->loggID = $lg;
		$this->mainlayout = $main;
		$this->errormessage = $errorm;
		$this->loginerror = $loginme;
		$this->userID = $uID;
		$this->languageDir2 = $langDir2;
		$this->errormessageTop = $errormsgtop;
		$this->errorID = $error;
		$this->ipinformation = $ipinfo;
		$this->defaultLanguageID = $defaultlang;
		$this->userCountry = $userCountry;
		$this->micro = $micr;
		$this->micpos = $micrpos;
		$this->useraccessarray = $uaccessarray;
		$this->useradminaccess = $uadminaccess;
		$this->sessionID = $sessID;
	}
}
?>