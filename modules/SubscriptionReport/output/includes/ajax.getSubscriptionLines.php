<?php
	if(isset($_POST['subscriptionId'])){ $subscriptionId = intval($_POST['subscriptionId']); }
	if($subscriptionId > 0) {

		$s_sql = "SELECT * FROM subscriptionmulti WHERE id = ?";
		$o_query = $o_main->db->query($s_sql, array($subscriptionId));
		$subscription = ($o_query ? $o_query->row_array():array());
		if($subscription['freeNoBilling']){
			?>
			<div class="subscriptionLine"><?php echo $formText_FreeNoBilling_Output;?></div>
			<?php
		} else {
			$o_query = $o_main->db->query("SELECT * FROM subscriptionline WHERE subscribtionId = ?", array($subscriptionId));
			if($o_query && $o_query->num_rows()>0)
			foreach($o_query->result_array() as $subscriptionline)
			{
				$pricePerPiece = $subscriptionline['pricePerPiece'];
				if($subscriptionline['articleOrIndividualPrice']) {
		            $sql = "SELECT * FROM article WHERE id = ?";
		            $o_query = $o_main->db->query($sql, array($subscriptionline['articleNumber']));
		            $article = $o_query ? $o_query->row_array() : array();
					$pricePerPiece = $article['price'];
				}
				?>
	 				<div class="subscriptionLine"><?php echo $subscriptionline['articleName']." <b>".$formText_Amount_output.":</b> ".$subscriptionline['amount']." <b>".$formText_PricePerPiece_output.":</b> ".$pricePerPiece." <b>".$formText_Discount_output.":</b> ".$subscriptionline['discount']." "." <b>".$formText_PriceTotal_output.":</b> ". $subscriptionline['pricePerPiece'] * $subscriptionline['amount'] * (1 - $subscriptionline['discount']/100);?></div>
				<?php
			}
		}
	}
?>
