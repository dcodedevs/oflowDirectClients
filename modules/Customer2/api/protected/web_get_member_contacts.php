<?php

$idr = $v_data['params']['idr'];
$sql_where = " AND displayInMemberpage = 1";
if($v_data['params']['all']) {
	$sql_where = "";
}
$getK = $o_main->db->query("SELECT * FROM contactperson WHERE customerId = '".$idr."' ORDER BY sortnr");

$cnt = 0;
$kk = array();
foreach($getK->result() AS $k){
	$kk[$cnt]["id"] = $k->id;
	$kk[$cnt]["name"] = $k->name;
	$kk[$cnt]["middlename"] = $k->middlename;
	$kk[$cnt]["lastname"] = $k->lastname;
	$kk[$cnt]["title"] = $k->title;
	$kk[$cnt]["directPhone"] = $k->mobile;
	$kk[$cnt]["email"] = $k->email;
	$kk[$cnt]["customerId"] = $k->customerId;
	$kk[$cnt]["displayInMemberpage"] = $k->displayInMemberpage;
	$kk[$cnt]["mainContact"] = $k->mainContact;


	$cnt++;
}

$v_return['data'] = $kk;

?>
