<?php
$return = array();
define('BASEPATH', realpath(__DIR__.'/../../../../').DIRECTORY_SEPARATOR);
require_once(BASEPATH.'elementsGlobal/cMain.php');

if(isset($_POST['action']))
switch($_POST['action'])
{
	case 'manual_add':
		$source = "preload";
		$o_main->db->query("insert into sys_pdfsend_userlistexpire(session, created) values(?, NOW()) on duplicate key update created = NOW()", array($_POST['session']));
		if($_POST['name'] !="")
		{
			$o_main->db->query("insert into sys_pdfsend_userlist(id, session, source, name, text) values (NULL,?,?,?,?)", array($_POST['session'], $source, $_POST['name'], 'Other'));
		}
		
		$o_total = $o_selected = (object)array();
		$o_query = $o_main->db->query('SELECT count(id) cnt FROM sys_pdfsend_userlist WHERE session = ? and source = ? and disabled = ?', array($_POST['session'], $source, 0));
		if($o_query) $o_total = $o_query->row();
		$o_query = $o_main->db->query('SELECT count(id) cnt FROM sys_pdfsend_userlist WHERE session = ? and source = ? and selected = ?', array($_POST['session'], $source, 1));
		if($o_query) $o_selected = $o_query->row();
		$return = array('id' => $source, 'total' => (isset($o_total->cnt) ? $o_total->cnt : 0), 'selected' => (isset($o_selected->cnt) ? $o_selected->cnt : 0));
		break;
	
	
	
	
	case 'import':
		$o_main->db->query("insert into sys_pdfsend_userlistexpire(session, created) values(?, NOW()) on duplicate key update created = NOW()", array($_POST['session']));
		
		$o_main->db->query("delete from sys_pdfsend_userlist where session = ? and source = ?", array($_POST['session'], 'sys_reload'));
		
		$source = $_POST['source'];
		if($source == 'getynet')
		{
			if(!function_exists("APIconnectAccount")) include_once(__DIR__."/APIconnect.php");
			$users = json_decode(APIconnectUser("useremailgetlist", $_COOKIE['username'], $_COOKIE['sessionID'], array('USER_TYPE'=>1, 'COMPANY_ID'=>$_POST['companyID'])),true);
			
			$sqlExt = "";
			foreach ($users['data'] as $user)
			{
				if($user['name']=="") continue;
				$o_main->db->query("insert into sys_pdfsend_userlist(id, session, source, name, text) values (NULL,?,?,?,?)", array($_POST['session'], $_POST['source'], $user['name'], $_POST['source']));
				$counter++;
			}
			$userCount[] = array('id'=>$_POST['source'],'count'=>$counter);
		} else {
			if(!function_exists("sendEmail_get_module_options")) include("fn_sendEmail_get_module_options.php");
			
			list($vSource,$filters) = explode('(:)',$_POST['source'],2);
			list($source,$vSource) = explode(':',$vSource,2);
			$vSource = explode(':',$vSource);
			
			if($filters!="")
			{
				$enableCategory = false;
				$prefilter = $parentIds = $mainLevels = $subLevels = array();
				$filters = explode('(:)',$filters);
				foreach($filters as $filter)
				{
					$tmp = explode(':',$filter);
					if(strtolower($tmp[0]) == 'prefilter')
					{
						$prefilter[] = array($tmp[1], $tmp[2]);
					} else if(strtolower($tmp[0]) == 'sys')
					{
						$parentIds[] = $tmp[1];
						$enableCategory = true;
					} else if(strtolower($tmp[0]) == 'mod')
					{
						$options = sendEmail_get_module_options($tmp, $vSource[0], $_POST['choosenListInputLang']);
						if($tmp[1]==1) //add to all levels
						{
							$subLevels = array_merge($subLevels,$options);
						} else {
							$mainLevels = array_merge($mainLevels,$options);
						}
					}
				}
			}
			
			import_source_init($o_main, $vSource, $source, $prefilter, (sizeof($parentIds)>0 ? implode(',',$parentIds) : ($enableCategory ? 0 : '')), $subLevels, $mainLevels, $_POST['session'], 0, ($vSource[15]==1 ? true : false));
		}
		
		$o_total = $o_selected = (object)array();
		$o_query = $o_main->db->query('SELECT count(id) cnt FROM sys_pdfsend_userlist WHERE session = ? and source = ? and disabled = ?', array($_POST['session'], $source, 0));
		if($o_query) $o_total = $o_query->row();
		$o_query = $o_main->db->query('SELECT count(id) cnt FROM sys_pdfsend_userlist WHERE session = ? and source = ? and selected = ?', array($_POST['session'], $source, 1));
		if($o_query) $o_selected = $o_query->row();
		$return = array('id' => $source, 'total' => (isset($o_total->cnt) ? $o_total->cnt : 0), 'selected' => (isset($o_selected->cnt) ? $o_selected->cnt : 0), 'filter'=>import_build_filter($o_main, 0, $_POST['session'], $source, $_POST['field']));
		break;
	
	
	
	
	case 'change_selection':
		if(strlen($_POST['changeSource'])>0)
		{
			$s_sql = "update sys_pdfsend_userfilter set selected = ? where session = ? and source = ?";
			$o_main->db->query($s_sql, array($_POST['checked'], $_POST['session'], $_POST['changeSource']));
			$s_sql = "update sys_pdfsend_userfilter set selected = ? where session = ? and source = ? and disabled = ?";
			$o_main->db->query($s_sql, array($_POST['checked'], $_POST['session'], $_POST['changeSource'], 0));
		} else {
			change_selection_filter_child($o_main, $_POST['filterId'], $_POST['checked']);
			change_selection_filter_parent($o_main, $_POST['filterId'], $_POST['checked']);
			
			$s_sql = "update sys_pdfsend_userlist ul join sys_pdfsend_userfilter uf on uf.source = ul.source set ul.selected = ? where ul.session = ? and uf.id = ? and ul.disabled = ?";
			$o_main->db->query($s_sql, array(0, $_POST['session'], $_POST['filterId'], 0));
			$s_sql = "update sys_pdfsend_userlist ul join sys_pdfsend_userrelation ur on ur.userlistID = ul.id join sys_pdfsend_userfilter uf on uf.id = ur.userfilterID set ul.selected = ? where ul.session = ? and ul.selected = ? and uf.selected = ? and ul.disabled = ?";
			$o_main->db->query($s_sql, array(1, $_POST['session'], 0, 1, 0));
		}
		
		$o_total = $o_selected = (object)array();
		$o_query = $o_main->db->query('SELECT count(id) cnt FROM sys_emailsend_userlist WHERE session = ? and source = ? and disabled = ?', array($_POST['session'], $source, 0));
		if($o_query) $o_total = $o_query->row();
		$o_query = $o_main->db->query('SELECT count(id) cnt FROM sys_emailsend_userlist WHERE session = ? and source = ? and selected = ?', array($_POST['session'], $source, 1));
		if($o_query) $o_selected = $o_query->row();
		$return = array('total' => (isset($o_total->cnt) ? $o_total->cnt : 0), 'selected' => (isset($o_selected->cnt) ? $o_selected->cnt : 0));
		break;
	
	
	
	
	case 'list':
		$fields = array(1=>'name', 2=>'extra1', 3=>'extra2', 4=>'extra3', 5=>'extra4', 6=>'extra5', 7=>'extra6', 8=>'extra7', 9=>'extra8', 10=>'extra9', 11=>'extra10');
		list($order,$orderby) = explode(':',$_POST['sourceconfig']);
		$order = explode(',',$order);
		if($order[0]=="") $order = array();
		if(!in_array(1,$order)) $order[] = 1;
		$orderby = intval($orderby);
		if($orderby < 1 or $orderby > 10) $orderby = 1;
		
		$html = "";
		$page = 0;
		$perPage = 100;
		if(isset($_POST['page'])) $page = $_POST['page'];
		$v_param = array($_POST['session']);
		if($_POST['source'] != "") $v_param[] = $_POST['source'];
		$s_sql = "select * from sys_pdfsend_userlist where session = ?".($_POST['source']=="" ? "" : " and source = ?")." order by ".$fields[$orderby];
		$o_query = $o_main->db->query($s_sql, $v_param);
		if($o_query)
		{
			$total = $o_query->num_rows();
			$s_sql .= " LIMIT ".($page*$perPage).", $perPage";
			$o_query = $o_main->db->query($s_sql, $v_param);
			if($o_query && $o_query->num_rows()>0)
			{
				$html .= '<table width="100%" border="0" cellpadding="0" cellspacing="0" rules="none" frame="void">';
				foreach($o_query->result_array() as $user)
				{
					$html .= '<tr class="item"><td class="list_checkbox">'.($user['disabled']==0 ? '<input name="'.$_POST['field'].'_userID_[]" type="checkbox" value="'.$user['id'].'" onChange="javascript:'.$_POST['field'].'_user_change();"'.($user['selected']==1?' checked':'').'>' : '&nbsp;').'</td>';
					foreach($order as $key)
					{
						$html .= '<td class="'.$fields[$key].'" onClick="javascript:$(this).parent().find(\'input:checkbox\').trigger(\'click\');">'.$user[$fields[$key]].'</td>';
					}
					$html .= '<td class="text" onClick="javascript:$(this).parent().find(\'input:checkbox\').trigger(\'click\');">'.str_replace(':','<br>',$user['text']).'</td></tr>';
				}
				$html .= '</table>';
				$pages = ceil($total/$perPage);
				if($pages>1)
				{
					$html .= '<div class="paging">';
					for($i=0;$i<$pages;$i++)
					{
						$html .= '<a'.($i==$page ? ' class="active"' : '').' href="javascript:'.$_POST['field'].'_show_userlist_page('.$i.');">'.($i+1).'</a>';
					}
					$html .= '</div>';
				}
			}
		}
		
		$return['html'] = $html;
		break;
	
	
	
	
	case 'manual_update':
		$id = 0;
		if(sizeof($_POST['selected'])>0)
		{
			$id = $_POST['selected'][0];
			$o_main->db->update('sys_pdfsend_userlist', array('selected' => 1), array('id IN' => $_POST['selected'], 'disabled' => 0));
		}
		if(sizeof($_POST['unselected'])>0)
		{
			$id = $_POST['unselected'][0];
			$o_main->db->update('sys_pdfsend_userlist', array('selected' => 0), array('id IN' => $_POST['unselected'], 'disabled' => 0));
		}
		if($id>0)
		{
			$o_main->db->query('update sys_pdfsend_userfilter uf join sys_pdfsend_userlist ul on ul.session = uf.session and ul.source = uf.source set uf.selected = ? where ul.id = ?', array(0, $id));
			
			$o_total = $o_selected = (object)array();
			$o_query = $o_main->db->query('select count(ul.id) cnt from sys_pdfsend_userlist ul join sys_pdfsend_userlist c on c.session = ul.session and c.source = ul.source where c.id = ? and ul.disabled = ?', array($id, 0));
			if($o_query) $o_total = $o_query->row();
			$o_query = $o_main->db->query('select count(ul.id) cnt from sys_pdfsend_userlist ul join sys_pdfsend_userlist c on c.session = ul.session and c.source = ul.source where c.id = ? and ul.selected = ?', array($id, 1));
			if($o_query) $o_selected = $o_query->row();
			$return = array('total' => (isset($o_total->cnt) ? $o_total->cnt : 0), 'selected' => (isset($o_selected->cnt) ? $o_selected->cnt : 0));
		}
		break;
	
	
	
	
	case 'check_send':
		$o_selected = (object)array();
		$o_query = $o_main->db->query('SELECT count(id) cnt FROM sys_emailsend_userlist WHERE session = ? and selected = ?', array($_POST['session'], 1));
		if($o_query) $o_selected = $o_query->row();
		$return = array('total' => (isset($o_selected->cnt) ? $o_selected->cnt : 0));
		break;
	
	
	
	
	case 'cleanup':
		$o_query = $o_main->db->query('select session from sys_pdfsend_userlistexpire LIMIT 0,1');
		if($o_query && $o_query->num_rows()>0)
		{
			$o_query = $o_main->db->query('select session from sys_pdfsend_userlistexpire where created >= DATE_SUB(NOW(), INTERVAL 1 DAY) LIMIT 0,1');
			if($o_query && $o_query->num_rows()==0)
			{
				$o_main->db->simple_query('truncate sys_pdfsend_userlist');
				$o_main->db->simple_query('truncate sys_pdfsend_userrelation');
				$o_main->db->simple_query('truncate sys_pdfsend_userfilter');
				$o_main->db->simple_query('truncate sys_pdfsend_userlistexpire');
				$return['done'] = 'truncate';
			} else {
				$v_sessions = array();
				$o_query = $o_main->db->query('select session from sys_pdfsend_userlistexpire where created < DATE_SUB(NOW(), INTERVAL 1 DAY)');
				if($o_query && $o_query->num_rows()>0)
				{
					foreach($o_query->result() as $o_row)
					{
						$v_sessions[] = $o_row->session;
					}
				}
				$o_main->db->query('delete from sys_pdfsend_userlist where session in ?', array($v_sessions));
				$o_main->db->query('delete ur.* from sys_pdfsend_userrelation ur join sys_pdfsend_userfilter uf on uf.id = ur.userfilterID where uf.session in ?', array($v_sessions));
				$o_main->db->query('delete from sys_pdfsend_userfilter where session in ?', array($v_sessions));
				$o_main->db->query('delete from sys_pdfsend_userlistexpire where session in ?', array($v_sessions));
				$return['done'] = 'delete';
			}
		}
		break;
	
	
	
	
	case 'delete_report':
		$return['ok'] = 0;
		$o_query = $o_main->db->query('select * from sys_pdfsend where id = ?', array($_POST['id']));
		if($o_query && $o_query->num_rows()>0)
		{
			$o_main->db->query('delete from sys_pdfsend where id = ?', array($_POST['id']));
			if(is_file(__DIR__."/../../../../".$row['link']))
			{
				unlink(__DIR__."/../../../../".$row['link']);
			}
			$return['ok'] = 1;
		}
		break;
	
	
	
	
	default:
		break;
}

print json_encode($return);


function change_selection_filter_child($o_main, $id, $checked)
{
	$o_main->db->query('update sys_pdfsend_userfilter set selected = ? where parentID = ?', array($checked, $id));
	$o_query = $o_main->db->query('select id from sys_pdfsend_userfilter where parentID = ?', array($id));
	if($o_query && $o_query->num_rows()>0)
	{
		foreach($o_query->result() as $o_row)
		{
			change_selection_filter_child($o_main, $o_row->id, $checked);
		}
	}
}
function change_selection_filter_parent($o_main, $id, $checked)
{
	$o_main->db->query('update sys_pdfsend_userfilter set selected = ? where parentID = ?', array($checked, $id));
	$o_query = $o_main->db->query('select other.id from sys_pdfsend_userfilter other join sys_pdfsend_userfilter curent on curent.parentID = other.parentID and curent.id <> other.id where curent.id = ? and other.selected <> ?', array($id, $checked));
	if(
		($checked == 1 and $o_query && $o_query->num_rows() == 0)
		or
		($checked == 0)
	)
	{
		$o_query = $o_main->db->query('select parentID from sys_pdfsend_userfilter where id = ?', array($id));
		if($o_query && $o_row = $o_query->row())
		{
			if($o_row->parentID > 0) change_selection_filter_parent($o_main, $o_row->parentID, $checked);
		}
	}
}

function import_build_filter($o_main, $parentId, $session, $source, $field, $level = 1)
{
	$return = "";
	$o_query = $o_main->db->query('select uf.id, uf.hide_empty, uf.name, count(ul.id) cnt from sys_pdfsend_userfilter uf left outer join sys_pdfsend_userrelation ur on uf.id = ur.userfilterID left outer join sys_pdfsend_userlist ul on ul.id = ur.userlistID and ul.session = uf.session and ul.source = uf.source where uf.session = ? and uf.source = ? and uf.parentID = ? group by uf.id order by uf.hide_empty, uf.id', array($session, $source, $parentId));
	if($o_query && $o_query->num_rows()>0)
	{
		foreach($o_query->result() as $o_row)
		{
			if($o_row->hide_empty==1 and $o_row->cnt == 0) continue;
			$return .= '<div class="'.$field.'_filter filter"><input type="checkbox" value="'.$o_row->id.'" onChange="'.$field.'_change_selection(this,\''.$source.'\',\'\', '.$o_row->id.', this.checked);" checked><label onClick="javascript:$(this).prev(\'input:checkbox\').trigger(\'click\');">'.$o_row->name.' ('.$row['cnt'].')</label>'.import_build_filter($o_main, $o_row->id, $session, $source, $field, $level+1).'</div>';
		}
	}
	return $return;
}

function import_source_init($o_main, $vSource, $source, $prefilter, $syscategoryParentId, $subLevels, $mainLevels, $sessionID, $filterParentId, $importEmpty)
{
	$filterName = 'Other';
	$names = explode(',',$vSource[2]);
	$o_main->db->query("insert into sys_pdfsend_userfilter(id, session, source ,name, parentID, selected, hide_empty) values (NULL,?,?,?,?,?,?)", array($sessionID, $source, $filterName, $filterParentId,1 ,1));
	$filterID = $o_main->db->insert_id();
	$sqlWhere = "";
	foreach($prefilter as $item)
	{
		if($sqlWhere!="") $sqlWhere .= " AND ";
		$sqlWhere .= "s.".$o_main->db_escape_name($item[0])." = ".$o_main->db->escape($item[1]);
	}
	$o_query = $o_main->db->query('select s.* from '.$o_main->db_escape_name($vSource[0]).' as s'.($sqlWhere!='' ? ' where '.$sqlWhere : ''));
	if($o_query && $o_query->num_rows()>0)
	{
		foreach($o_query->result_array() as $row)
		{
			$emptyContact = true;
			foreach($names as $item)
			{
				$name[$item] = array_map('trim',explode('¤',$row[$item]));
			}
			$extra = array();
			for($i=3;$i<13;$i++)
			{
				if($vSource[$i]!="" and isset($row[$vSource[$i]]))
					$extra[]=array_map('trim',explode('¤',$row[$vSource[$i]]));
				else
					$extra[]=array('');
			}
			for($x=0; $x < sizeof($name[$names[0]]); $x++)
			{
				$tmp_name = "";
				foreach($names as $item) $tmp_name = trim($tmp_name).' '.$name[$item][$x];
				
				if($importEmpty or $tmp_name!="")
				{
					$emptyContact = false;
					$v_param = array($sessionID, $source, trim($tmp_name));
					foreach($extra as $item) $v_param[] = (sizeof($item)>1 ? $item[$x] : $item[0]);
					$v_param[] = $filterName;
					$v_param[] = $row['id'];
					$v_param[] = $x;
					$v_param[] = ($tmp_name!="" ? 1 : 0);
					$v_param[] = ($tmp_name!="" ? 0 : 1);
					$o_main->db->query('insert into sys_pdfsend_userlist(id, session, source, name, extra1, extra2, extra3, extra4, extra5, extra6, extra7, extra8, extra9, extra10, text, origID, origgroupID, selected, disabled) values (NULL,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)', $v_param);
					$userID = $o_main->db->insert_id();
					$o_main->db->query("insert into sys_pdfsend_userrelation(userfilterID, userlistID) values(?,?)", array($filterID, $userID));
				}
			}
			if($importEmpty and $emptyContact)
			{
				$v_param = array($sessionID, $source, '');
				foreach($extra as $item) $v_param[] = (sizeof($item)>1 ? $item[$x] : $item[0]);
				$v_param[] = '';
				$v_param[] = $row['id'];
				$v_param[] = 0;
				$v_param[] = 0;
				$v_param[] = 1;
				$o_main->db->query('insert into sys_pdfsend_userlist(id, session, source, name, extra1, extra2, extra3, extra4, extra5, extra6, extra7, extra8, extra9, extra10, text, origID, origgroupID, selected, disabled) values (NULL,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)', $v_param);
				$userID = $o_main->db->insert_id();
			}
		}
	}
	
	import_source($o_main, $vSource, $source, $syscategoryParentId, $subLevels, $mainLevels, $sessionID, 0, $filterID, $importEmpty);
}

function import_source($o_main, $vSource, $source, $syscategoryParentId, $subLevels, $mainLevels, $sessionID, $filterParentId, $otherFilterId, $importEmpty, $parentPath = '', $ids = array())
{
	$initCall = false;
	if($parentPath == '' && sizeof($ids)==0) $initCall = true;
	
	$o_filter = $o_main->db->query('select * from syscontactcategory where parentlevelID IN ?', array($syscategoryParentId));
	if($o_filter && $o_filter->num_rows()>0)
	{
		foreach($o_filter->result_array() as $filter)
		{
			$nextPath = ($parentPath!="" ? $parentPath.' - '.$filter['name'] : $filter['name']);
			$o_main->db->query("insert into sys_pdfsend_userfilter(id, session, source ,name, parentID, selected) values (NULL,?,?,?,?,?)", array($sessionID, $source, $filter['name'], $filterParentId, 1));
			$filterID = $o_main->db->insert_id();
			$ids[] = $filterID;
			$o_query = $o_main->db->query("select ul.* from sys_pdfsend_userlist ul join ".$o_main->db_escape_name($vSource[0])." as s on s.id = ul.origID join syscontactcategoryconnection c on c.contactID = s.id and c.contentTable = ? where ul.session = ? and ul.source = ? and c.categoryID = ?", array($vSource[0], $sessionID, $source, $filter['id']));
			if($o_query && $o_query->num_rows()>0)
			{
				foreach($o_query->result_array() as $row)
				{
					$userID = $row['id'];
					$paths = array();
					$update_path = false;
					$o_check = $o_main->db->query('select userlistID from sys_pdfsend_userrelation where userlistID = ? and userfilterID = ?', array($userID, $otherFilterId));
					if(!$o_check || ($o_check && $o_check->num_rows() == 0))
					{
						$paths = explode(":",$row['text']);
						foreach($paths as $i => $path)
						{
							if($path == $nextPath)
							{
								$paths[$i] = ($paths[$i] != "" ? $paths[$i]." - ".$filter['name'] : $filter['name']);
								$update_path = true;
								break;
							}
						}
					}
					if(!$update_path) $paths[] = $nextPath;
					$o_main->db->query('update sys_pdfsend_userlist set text = ? where id = ?', array(implode(':',$paths), $userID));
					$o_main->db->query('delete from sys_pdfsend_userrelation where userlistID = ? and userfilterID = ?', array($userID, $otherFilterId));
					$o_main->db->query('insert into sys_pdfsend_userrelation(userfilterID, userlistID) values(?, ?)', array($filterID, $userID));
				}
			}
			
			$nextIds = array();
			foreach($subLevels as $item)
			{
				$o_main->db->query("insert into sys_pdfsend_userfilter(id, session, source ,name, parentID, selected) values (NULL,?,?,?,?,?)", array($sessionID, $source, $item[0], $filterID, 1));
				$subfilterID = $o_main->db->insert_id();
				$nextIds[] = $subfilterID;
				
				$o_query = $o_main->db->query("select ul.*, s.".$o_main->db_escape_name($item[2])." tmp_checkfilter from sys_pdfsend_userlist ul join sys_pdfsend_userrelation ur on ur.userlistID = ul.id join ".$o_main->db_escape_name($vSource[0])." as s on s.id = ul.origID where ur.userfilterID = ".$o_main->db->escape($filterID)." and s.".$o_main->db_escape_name($item[2])." like '%".$o_main->db->escape_like_str($item[1])."%' ESCAPE '!'");
				if($o_query && $o_query->num_rows()>0)
				{
					foreach($o_query->result_array() as $row)
					{
						$filtercheck = array_map('trim',explode('¤',$row['tmp_checkfilter']));
						if((sizeof($filtercheck)>$row['origgroupID'] and $filtercheck[$row['origgroupID']] == $item[1]) or (sizeof($filtercheck)==1 and $filtercheck[0] == $item[1]))
						{
							$userID = $row['id'];
							$paths = array();
							$update_path = false;
							$o_check = $o_main->db->query('select userlistID from sys_pdfsend_userrelation where userlistID = ? and userfilterID = ?', array($userID, $otherFilterId));
							if(!$o_check || ($o_check && $o_check->num_rows() == 0))
							{
								$paths = explode(":",$row['text']);
								foreach($paths as $i => $path)
								{
									if($path == $nextPath)
									{
										$paths[$i] = ($paths[$i] != "" ? $paths[$i]." - ".$item[0] : $item[0]);
										$update_path = true;
										break;
									}
								}
							}
							if(!$update_path) $paths[] = $nextPath.' - '.$item[0];
							$o_main->db->query('update sys_pdfsend_userlist set text = ? where id = ?', array(implode(':',$paths), $userID));
							$o_main->db->query('delete from sys_pdfsend_userrelation where userlistID = ? and userfilterID = ?', array($userID, $otherFilterId));
							$o_main->db->query('insert into sys_pdfsend_userrelation(userfilterID, userlistID) values(?, ?)', array($subfilterID, $userID));
						}
					}
				}
			}
			
			import_source($o_main, $vSource, $source, $filter['id'], $subLevels, array(), $sessionID, $filterID, $otherFilterId, $importEmpty, $nextPath, $nextIds);
		}
	}
	
	foreach($mainLevels as $item)
	{
		$o_main->db->query("insert into sys_pdfsend_userfilter(id, session, source ,name, parentID, selected) values (NULL,?,?,?,?,?)", array($sessionID, $source, $item[0], 0, 1));
		$filterID = $o_main->db->insert_id();
		
		$o_query = $o_main->db->query("select ul.*, s.".$o_main->db_escape_name($item[2])." tmp_checkfilter from sys_pdfsend_userlist ul join ".$o_main->db_escape_name($vSource[0])." as s on s.id = ul.origID where ul.session = ".$o_main->db->escape($sessionID)." and ul.source = ".$o_main->db->escape($source)." and s.".$o_main->db_escape_name($item[2])." like '%".$o_main->db->escape_like_str($item[1])."%' ESCAPE '!'");
		if($o_query && $o_query->num_rows()>0)
		{
			foreach($o_query->result_array() as $row)
			{
				$filtercheck = array_map('trim',explode('¤',$row['tmp_checkfilter']));
				if((sizeof($filtercheck)>$row['origgroupID'] and $filtercheck[$row['origgroupID']] == $item[1]) or (sizeof($filtercheck)==1 and $filtercheck[0] == $item[1]))
				{
					$userID = $row['id'];
					$paths = array();
					$update_path = false;
					$o_check = $o_main->db->query('select userlistID from sys_pdfsend_userrelation where userlistID = ? and userfilterID = ?', array($userID, $otherFilterId));
					if(!$o_check || ($o_check && $o_check->num_rows() == 0))
					{
						$paths = explode(":",$row['text']);
						foreach($paths as $i => $path)
						{
							if($path == $nextPath)
							{
								$paths[$i] = ($paths[$i] != "" ? $paths[$i]." - ".$item[0] : $item[0]);
								$update_path = true;
								break;
							}
						}
					}
					if(!$update_path) $paths[] = $item[0];
					$o_main->db->query('update sys_pdfsend_userlist set text = ? where id = ?', array(implode(':',$paths), $userID));
					$o_main->db->query('delete from sys_pdfsend_userrelation where userlistID = ? and userfilterID = ?', array($userID, $otherFilterId));
					$o_main->db->query('insert into sys_pdfsend_userrelation(userfilterID, userlistID) values(?, ?)', array($filterID, $userID));
				}
			}
		}
	}
	
	if(sizeof($ids)>0)
	{
		$s_sql = "select ul.* from sys_pdfsend_userlist ul
			join sys_pdfsend_userrelation ur on ur.userlistID = ul.id
			left outer join sys_pdfsend_userrelation ur2 on ur2.userfilterID in ? and ur2.userlistID = ur.userlistID
			where ur.userfilterID = ? and ur2.userlistID IS NULL";
		$o_query = $o_main->db->query($s_sql, array($ids, $filterParentId));
		if($o_query && $o_query->num_rows()>0)
		{
			$filterName = 'Other';
			$o_main->db->query('insert into sys_pdfsend_userfilter(id, session, source ,name, parentID, selected) values (NULL,?,?,?,?,?)', array($sessionID, $source, $filterName, $filterParentId,1));
			$subfilterID = $o_main->db->insert_id();
			foreach($o_query->result_array() as $row)
			{
				$userID = $row['id'];
				$paths = array();
				$update_path = false;
				$o_check = $o_main->db->query('select userlistID from sys_pdfsend_userrelation where userlistID = ? and userfilterID = ?', array($userID, $otherFilterId));
				if(!$o_check || ($o_check && $o_check->num_rows() == 0))
				{
					$paths = explode(":",$row['text']);
					foreach($paths as $i => $path)
					{
						if($path == $parentPath)
						{
							$paths[$i] = ($paths[$i] != "" ? $paths[$i]." - ".$filterName : $filterName);
							$update_path = true;
							break;
						}
					}
				}
				if(!$update_path) $paths[] = $filterName;
				$o_main->db->query('update sys_pdfsend_userlist set text = ? where id = ?', array(implode(':',$paths), $userID));
				$o_main->db->query('delete from sys_pdfsend_userrelation where userlistID = ? and userfilterID = ?', array($userID, $otherFilterId));
				$o_main->db->query('insert into sys_pdfsend_userrelation(userfilterID, userlistID) values(?, ?)', array($subfilterID, $userID));
			}
		}
	}
}
?>