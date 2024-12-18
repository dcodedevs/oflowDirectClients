<?php

	// error_reporting(E_ALL);
	// ini_set("display_errors", 1);
	ini_set('max_execution_time', 120);
	if(isset($_POST['fixOrders'])) {

        $v_subrows = array();
        $s_sql = "select * from customer_collectingorder order by sortnr";
        $o_query = $o_main->db->query($s_sql);
        $collectingorderCount = $o_query ? $o_query->num_rows() : 0;

        if($collectingorderCount == 0){
	        $s_sql = "UPDATE orders SET collectingorderId = null";
	        $o_query = $o_main->db->query($s_sql);
        }

        $v_subrows = array();
        $s_sql = "select * from invoice order by id";;
        $o_query = $o_main->db->query($s_sql);
        $invoices = $o_query ? $o_query->result_array() : array();

        $s_sql = "SELECT * FROM moduledata WHERE name = 'InvoicedOrders'";
        $o_query = $o_main->db->query($s_sql);
        if($o_query && $o_query->num_rows()>0){
            $invoicedOrder = $o_query->row_array();
            $invoicedOrderModuleId = $invoicedOrder['uniqueID'];
        }

        $s_sql = "DELETE FROM orders WHERE content_status > 0";
        $o_query = $o_main->db->query($s_sql);

        foreach($invoices as $invoice) {
	        $s_sql = "select * from customer_collectingorder WHERE invoiceNumber = ?";
	        $o_query = $o_main->db->query($s_sql, array($invoice['id']));
	        $collectingorder = $o_query ? $o_query->row_array() : array();
	        if(!$collectingorder){
	        	$sql = "INSERT INTO customer_collectingorder SET
	            created = now(),
	            createdBy='".$variables->loggID."',
	            date = ?,
	            customerId = ?,
	            ownercompanyId = ?,
	            invoiceNumber = ?,
	            approvedForBatchinvoicing = 1
	            ";
	            $o_query = $o_main->db->query($sql, array($invoice['invoiceDate'], $invoice['customerId'], $invoice['ownercompany_id'], $invoice['id']));
				$collectingorderId = $o_main->db->insert_id();

			} else {
				$collectingorderId = $collectingorder['id'];
			}

			if($invoicedOrderModuleId > 0){
				$s_sql = "select * from orders WHERE invoiceNumber = ? AND orders.moduleID = ?";
		        $o_query = $o_main->db->query($s_sql, array($invoice['id'], $invoicedOrderModuleId));
			} else {
				$s_sql = "select * from orders WHERE invoiceNumber = ?";
		        $o_query = $o_main->db->query($s_sql, array($invoice['id']));
			}
	        $orders = $o_query ? $o_query->result_array() : array();

	        foreach($orders as $order) {
	        	$s_sql = "UPDATE orders SET collectingorderId = ?, moduleID = 0 WHERE id = ?";
   			 	$o_query = $o_main->db->query($s_sql, array($collectingorderId, $order['id']));
	        }
        }


        $s_sql = "SELECT * FROM moduledata WHERE name = 'Orders'";
        $o_query = $o_main->db->query($s_sql);
        if($o_query && $o_query->num_rows()>0){
            $uninvoicedOrder = $o_query->row_array();
            $uninvoicedOrderModuleId = $uninvoicedOrder['uniqueID'];
        }
		if($uninvoicedOrderModuleId > 0){
	        $s_sql = "select customer.* from customer left outer join orders ON orders.customerID = customer.id AND orders.moduleID = ? where orders.id is not null GROUP BY customer.ID ORDER BY customer.id";
	        $o_query = $o_main->db->query($s_sql, array($uninvoicedOrderModuleId));
		} else {
	        $s_sql = "select customer.* from customer left outer join orders ON orders.customerID = customer.id AND (orders.invoiceNumber = 0 OR orders.invoiceNumber is null) where orders.id is not null GROUP BY customer.ID ORDER BY customer.id";
	        $o_query = $o_main->db->query($s_sql);
		}
        $customers = $o_query ? $o_query->result_array() : array();


        foreach($customers as $customer){
            $ownercompanies_list = array();
            // List of ownercompanies
            $s_sql = "SELECT * FROM ownercompany";
            $o_query = $o_main->db->query($s_sql);
            if($o_query && $o_query->num_rows()>0){
                $ownercompanies_list = $o_query->result_array();
            }
            foreach($ownercompanies_list as $ownercompany){
				if($uninvoicedOrderModuleId > 0){
			        $s_sql = "select * from orders WHERE (invoiceNumber = 0 OR invoiceNumber is null) AND moduleID = ? AND (collectingorderId is null OR collectingorderId = 0) AND customerID = ? AND ownercompany_id = ?";
			        $o_query = $o_main->db->query($s_sql, array($uninvoicedOrderModuleId, $customer['id'], $ownercompany['id']));
				} else {
					$s_sql = "select * from orders WHERE (invoiceNumber = 0 OR invoiceNumber is null) AND (collectingorderId is null OR collectingorderId = 0) AND customerID = ? AND ownercompany_id = ?";
				   	$o_query = $o_main->db->query($s_sql, array($customer['id'], $ownercompany['id']));
				}
		        $orders = $o_query ? $o_query->result_array() : array();

		        if(count($orders) > 0){
		        	$sql = "INSERT INTO customer_collectingorder SET
			            created = now(),
			            createdBy='".$variables->loggID."',
			            date = now(),
			            customerId = ?,
			            ownercompanyId = ?
			            ";
		            $o_query = $o_main->db->query($sql, array($customer['id'], $ownercompany['id']));
					$collectingorderId = $o_main->db->insert_id();

			        foreach($orders as $order) {
			        	$s_sql = "UPDATE orders SET collectingorderId = ?, moduleID = 0 WHERE id = ?";
					 	$o_query = $o_main->db->query($s_sql, array($collectingorderId, $order['id']));
			        }
		        }
		    }
	    }

	}
?>
<div>
	<form name="importData" method="post" enctype="multipart/form-data"  action="" >

		<div class="formRow submitRow">

			<input type="submit" name="fixOrders" value="fix orders">

		</div>
	</form>
</div>
