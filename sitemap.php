<?php
header('Content-type: application/xml; charset=utf-8');
define('BASEPATH', __DIR__.DIRECTORY_SEPARATOR);
require_once(BASEPATH.'elementsGlobal/cMain.php');

//configuration
$v_path = explode('sitemap.php',getSelfLinkFull());
$s_url_prefix = $v_path[0];
$s_timezone = 'UTC';
$s_timezone_offset = '+00:00';
$W3C_datetime_format_php = 'Y-m-d\Th:i:s'; // See http://www.w3.org/TR/NOTE-datetime

$output = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
$output .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

$o_query = $o_main->db->query('SELECT languageID FROM language WHERE outputlanguage = 1 ORDER BY defaultOutputlanguage DESC');
foreach($o_query->result() as $o_language)
{
	$languageID = $o_language->languageID;
	$s_sql = 'SELECT m.*, mc.levelname, p.id pageID, count(p.id) pageCount, pc.urlrewrite FROM menulevel m JOIN menulevelcontent mc ON m.id = mc.menulevelID AND mc.languageID = ? LEFT OUTER JOIN pageID p ON p.menulevelID = m.id AND p.deleted = 0 LEFT OUTER JOIN pageIDcontent pc ON pc.pageIDID = p.id AND pc.languageID = ? GROUP BY m.id ORDER BY m.moduleID, m.level, m.sortnr, p.id DESC';
	$o_query1 = $o_main->db->query($s_sql, array($languageID, $languageID));
	if($o_query1)
	{
		foreach($o_query1->result() as $o_row1)
		{
			if($o_row1->urlrewrite != '')
			{
				if($o_row1->pageCount>1) {
					$urlList = explode('/',$o_row1->urlrewrite);
					$urlList = $urlList[0];
					$link = $urlList.'/list';
				} else {
					$link = $o_row1->urlrewrite;
				}
			} else {
				$link = 'index.php?pageID='.$o_row1->pageID.'&amp;openLevel='.$o_row1->id.($o_row1->pageCount>1 ? '&amp;showList=1' : '');
			}
			
			
			if($o_row1->pageID>0)
			{
				$output .= "\t<url>\n\t\t<loc>" . $s_url_prefix . htmlspecialchars($link) . "</loc>\n\t</url>\n";
			}
			if($o_row1->pageCount>1)
			{
				$o_query2 = $o_main->db->query('SELECT p.*, pc.urlrewrite FROM pageID p LEFT OUTER JOIN pageIDcontent pc ON pc.pageIDID = p.id AND pc.languageID = ? WHERE p.menulevelID = ? AND p.deleted = 0 ORDER BY p.id', array($languageID, $o_row1->id));
				if($o_query2)
				{
					foreach($o_query1->result() as $o_row2)
					{
						if($o_row2->urlrewrite != '')
						{
							$link = $o_row2->urlrewrite;
						} else {
							$link = 'index.php?pageID='.$o_row2->pageID.'&amp;openLevel='.$o_row2->menulevelID.($o_row2->pageCount>1 ? '&amp;showList=1' : '');
						}
						
						if($o_row2->id>0)
						{
							$output .= "\t<url>\n\t\t<loc>" . $s_url_prefix . htmlspecialchars($link) . "</loc>\n\t</url>\n";
						}
					}
				}
			}
		}
	}
}
$output .= '</urlset>';

echo $output;


function getSelfLinkFull()
{
	$s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
	$protocol = strleft(strtolower($_SERVER["SERVER_PROTOCOL"]), "/").$s;
	$port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
	return $protocol."://".$_SERVER['SERVER_NAME'].$port.$_SERVER['REQUEST_URI'];
}

function strleft($s1, $s2)
{
	return substr($s1, 0, strpos($s1, $s2));
}
?>