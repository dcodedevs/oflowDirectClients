<?php
function duplicate_images($s_images)
{
	global $o_main;
	$s_acc_path = realpath(__DIR__."/../../../../");
	$v_images = $v_images_new = json_decode($s_images);

	foreach($v_images as $l_key => $v_image)
	{
		$s_sql = "select * from uploads where id = '".$v_image[4]."'";
    	$o_query = $o_main->db->query($s_sql);
    	$v_tmp = $o_query ? $o_query->row_array() : array();
		$v_tmp['id'] = "NULL";
		$o_main->db->query("insert into uploads (".implode(",",array_keys($v_tmp)).") values('".implode("','",$v_tmp)."')");
		$v_images_new[$l_key][4] = $o_main->db->insert_id();
		$v_tmp = array();
		foreach($v_image[1] as $l_i => $s_image)
		{
			$v_tmp[$l_i] = str_replace("/".$v_image[4]."/".$l_i, "/".$v_images_new[$l_key][4]."/".$l_i, $s_image);
			mkdir(dirname($s_acc_path."/".$v_tmp[$l_i]),octdec(777),true);
			copy($s_acc_path."/".$s_image, $s_acc_path."/".$v_tmp[$l_i]);
		}
		$v_images_new[$l_key][1] = $v_tmp;
	}

	return json_encode($v_images_new);
}
?>
