<?php
$l_page = 0;
$l_offset = 0;
$l_per_page = 5;
$l_brreg_page = 0;
if(isset($_POST['search_brreg']))
{
	$l_per_page = 50;
	$l_page = intval($_POST['page']);
	$l_offset = $l_per_page * ($l_page - 1);
	if($l_offset < 0){
		$l_offset = 0;
	}
	if(intval($_POST['search_from_popup']) == 0){
		if($l_page == 1 )
		{
			$l_offset = 5;
			$l_per_page = 45;
		}
		$l_brreg_page = ceil(($l_per_page * $l_page) / 100);
	}
	if(intval($_POST['search_from_popup']) == 1){
		if($l_page == 1 )
		{
			$l_offset = 50;
			$l_per_page = 50;
		} else if($l_page > 1) {
			$l_offset = $l_per_page * ($l_page);
		}
		$l_brreg_page = ceil(($l_per_page * $l_page+1) / 100);
	}
	$search_filter = $_POST['search_brreg'];
	//echo $l_brreg_page;
}
// Show companies from Difi when searching
if($search_filter != '')
{
	if(intval(str_replace(" ", "", $search_filter)) > 0){
		$search_filter = str_replace(" ", "", $search_filter);
	}
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_VERBOSE, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
	curl_setopt($ch, CURLOPT_POST, TRUE);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
	curl_setopt($ch, CURLOPT_URL, 'http://ap_api.getynet.com/brreg.php');
	$v_post = array(
		'page' => ($l_brreg_page>0?$l_brreg_page:1),
		'search' => $search_filter,
		'token' => 'RySBvCv3i9u6tP4mEd177X2gspGP6Rw0E512874043zDfUyHDsNF70gZvJ2R9s6idmGxk29amaRRR1R4Hbvqy93LJzPCz9oy',
		'password' => '_9^JAj|7_atz~-Y.BndXBguO9_jr0^z~~09m-*:4SXlj|!KZ6Xd.hnJe3WM75H9Vy=;ZIcrab-84WLKN+4Kdz~+xM5U%ePIY'
	);

	curl_setopt($ch, CURLOPT_POSTFIELDS, $v_post);
	$s_response = curl_exec($ch);
	
	$v_items = array();
	$v_response = json_decode($s_response, TRUE);
	if(isset($v_response['status']) && $v_response['status'] == 1)
	{
		$l_brreg_count = $v_response['total_items'];
		$v_items = $v_response['items'];
	}
	if($l_page == 0) { ?>
	<h3 class="customer-list-table-title"><?php echo $formText_SearchIn_Output;?> brreg.no <span><?php echo $l_brreg_count.' '.$formText_Hits_Output;?></span></h3>
	<div class="gtable brreg">
		<div class="gtable_row">
			<div class="gtable_cell gtable_cell_head">#</div>
			<div class="gtable_cell gtable_cell_head c1"><?php echo $formText_CompanyName_output;?></div>
			<div class="gtable_cell gtable_cell_head"><?php echo $formText_Street_output;?></div>
			<div class="gtable_cell gtable_cell_head"><?php echo $formText_City_output;?></div>
			<div class="gtable_cell gtable_cell_head">&nbsp;</div>
		</div><?php
	}
		$l_counter = 0;
		if($l_brreg_page > 1)
		{
			$l_counter = (100 * ($l_brreg_page-1));
		}
		foreach($v_items as $v_row)
		{
			$v_customer = array();
			$l_counter++;
			if($l_counter <= $l_offset) continue;
			$b_found_connection = FALSE;
			$o_query = $o_main->db->query("SELECT id, content_status FROM customer WHERE publicRegisterId = '".$o_main->db->escape_str($v_row['orgnr'])."'");
			if($o_query && $o_query->num_rows()>0)
			{
				$b_found_connection = TRUE;
				$s_class = ' output-click-helper';
				$v_customer = $o_query->row_array();
				if($v_customer['content_status'] == 2){
					$s_class = ' reactivate_customer';
				}
				$s_edit_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$v_customer['id'];
			} else {
				$s_edit_link = '';
				$s_class = ' brreg-new-customer';
			}
			?><div class="gtable_row gtable_row_bregg <?php echo $s_class;?>"<?php echo ($s_edit_link != '' ? ' data-href="'.$s_edit_link.'"':' data-orgnr="'.$v_row['orgnr'].'"');?> data-customer-id="<?php echo $v_customer['id']?>">
				<div class="gtable_cell"><?php echo $l_counter;?></div>
				<div class="gtable_cell"><?php echo $v_row['navn'];?></div>
				<div class="gtable_cell"><?php echo $v_row['forretningsadr'];?></div>
				<div class="gtable_cell"><?php echo $v_row['forradrpoststed'];?></div>
				<div class="gtable_cell"><?php
				if($b_found_connection) { ?><span class="glyphicon glyphicon-link"></span><?php }
				?></div>
			</div><?php
			if($l_counter >= ($l_offset + $l_per_page)) break;
		}
	if($l_page == 0) { ?></div><?php
	if($l_brreg_count > $l_counter + 1)
	{
		?><div class="customer-paging"><?php echo $formText_Showing_output ." <i class='brreg-showing'>". $l_counter."</i> ".$formText_Of_output." ".$l_brreg_count;?>  <a href="#" class="brreg-page-link" data-page="<?php echo $l_page+1;?>"><?php echo $formText_ShowNext_Output;?> 50</a></div><?php
	}
	?>
	<script type="text/javascript">
	$(function(){
		$(document).off("click", '.brreg-new-customer').on('click', '.brreg-new-customer', function(e){
			e.preventDefault();
			var data = {
				customerId: 0,
				brreg_orgnr: $(this).data('orgnr')
			};
			ajaxCall('editCustomerDetail', data, function(json) {
				$('#popupeditboxcontent').html('');
				$('#popupeditboxcontent').html(json.html);
				out_popup = $('#popupeditbox').bPopup(out_popup_options);
				$("#popupeditbox:not(.opened)").remove();
			});
		});
	});
	</script>
	<div style="margin-bottom:100px;"></div><?php
	}
}
