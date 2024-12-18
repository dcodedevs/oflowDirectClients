<?php
function get_seo_data($contentID = 0, $menuID = 0, $menulevelID = 0, $languageID = 'no')
{
	$s_sql='SELECT * FROM seodata WHERE';
	if($contentID!=0)
	{
		$s_where = ' contentID = ? AND languageID = ?';
		$o_query = $o_main->db->query($s_sql.$s_where, array($contentID, $languageID));
		if($o_query && $o_query->num_rows()==0)
		{
			if($menuID!=0 and $menuID!=0)
			{
				$s_where = ' menuID = ? AND menulevelID = ? AND languageID = ?';
				$o_query = $o_main->db->query($s_sql.$s_where, array($menuID, $menulevelID, $languageID));
				if($o_query && $o_query->num_rows()==0)
				{
					$s_where = ' menuID = ? AND menulevelID = ? AND contentID = ? AND languageID = ?';
					$o_query = $o_main->db->query($s_sql.$s_where, array(0, 0, 0, $languageID));
					if(!$o_query) die($o_main->db->error()."kan ikke oppdatere siden. 3");
				} else {
					die($o_main->db->error()."kan ikke oppdatere siden. 2");
				}
			} else {
				if($menuID==0 and $menulevelID!=0)
				{
					$s_where = ' menulevelID = ? AND languageID = ?';
					$o_query = $o_main->db->query($s_sql.$s_where, array($menulevelID, $languageID));
					if($o_query && $o_query->num_rows()==0)
					{
						$s_where = ' menuID = ? AND menulevelID = ? AND contentID = ? AND languageID = ?';
						$o_query = $o_main->db->query($s_sql.$s_where, array(0, 0, 0, $languageID));
						if(!$o_query) die($o_main->db->error()."kan ikke oppdatere siden. 5");
					} else {
						die($o_main->db->error()."kan ikke oppdatere siden. 4");
					}
				} else {
					if($menuID!=0 and $menulevelID==0)
					{
						$s_where = ' menuID = ? AND languageID = ?';
						$o_query = $o_main->db->query($s_sql.$s_where, array($menuID, $languageID));
						if($o_query && $o_query->num_rows()==0)
						{
							$s_where = ' menuID = ? AND menulevelID = ? AND contentID = ? AND languageID = ?';
							$o_query = $o_main->db->query($s_sql.$s_where, array(0, 0, 0, $languageID));
							if(!$o_query) die($o_main->db->error()."kan ikke oppdatere siden. 7");
						} else {
							die($o_main->db->error()."kan ikke oppdatere siden. 6");
						}
					}
				}
			}
		} else {
			die($o_main->db->error()."kan ikke oppdatere siden. 1");
		}
	} else {
		if($menuID!=0 and $menuID!=0)
		{
			$s_where = ' menuID = ? AND menulevelID = ? AND languageID = ?';
			$o_query = $o_main->db->query($s_sql.$s_where, array($menuID, $menulevelID, $languageID));
			if($o_query && $o_query->num_rows()==0)
			{
				$s_where = ' menuID = ? AND menulevelID = ? AND contentID = ? AND languageID = ?';
				$o_query = $o_main->db->query($s_sql.$s_where, array(0, 0, 0, $languageID));
				if(!$o_query) die($o_main->db->error()."kan ikke oppdatere siden. 3");
			} else {
				die($o_main->db->error()."kan ikke oppdatere siden. 2");
			}
		} else {
			if($menuID==0 and $menulevelID!=0)
			{
				$s_where = ' menulevelID = ? AND languageID = ?';
				$o_query = $o_main->db->query($s_sql.$s_where, array($menulevelID, $languageID));
				if($o_query && $o_query->num_rows()==0)
				{
					$s_where = ' menuID = ? AND menulevelID = ? AND contentID = ? AND languageID = ?';
					$o_query = $o_main->db->query($s_sql.$s_where, array(0, 0, 0, $languageID));
					if(!$o_query) die($o_main->db->error()."kan ikke oppdatere siden. 5");
				} else {
					die($o_main->db->error()."kan ikke oppdatere siden. 4");
				}    
			} else {
				if($menuID!=0 and $menulevelID==0)
				{
					$s_where = ' menuID = ? AND languageID = ?';
					$o_query = $o_main->db->query($s_sql.$s_where, array($menuID, $languageID));
					if($o_query && $o_query->num_rows()==0)
					{
						$s_where = ' menuID = ? AND menulevelID = ? AND contentID = ? AND languageID = ?';
						$o_query = $o_main->db->query($s_sql.$s_where, array(0, 0, 0, $languageID));
						if(!$o_query) die($o_main->db->error()."kan ikke oppdatere siden. 7");
					} else {
						die($o_main->db->error()."kan ikke oppdatere siden. 6");
					}
				} else {
					$s_where = ' menuID = ? AND menulevelID = ? AND contentID = ? AND languageID = ?';
					$o_query = $o_main->db->query($s_sql.$s_where, array(0, 0, 0, $languageID));
					if(!$o_query) die($o_main->db->error()."kan ikke oppdatere siden. 7");
				}
			}
		}
	}
	
	$title = $description = $keywords = '';
	if($o_query)
	{
		foreach($o_query->result() as $o_row)
		{
			if(strlen($title)>0)
			{
				$title=$title.', '.$o_row->seoTitle;
				$description=$description.', '.$o_row->seoDescription;
				$keywords=$keywords.', '.$o_row->seoDescription;
			} else {
				$title=$title.$o_row->seoTitle;
				$description=$description.$o_row->seoDescription;
				$keywords=$keywords.$o_row->seoDescription;
			}
		}
	}
	$tmp_array = array();
	$tmp_array['title']=$title;
	$tmp_array['description']=$description;
	$tmp_array['keywords']=$keywords;
	
	// returns array off title, description, kywords
	return $tmp_array;
}
?>