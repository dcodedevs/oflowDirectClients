<?php
function get_customer_list_count2($o_main, $filter, $search_filter){
    return get_customer_list($o_main, $filter, $search_filter, 0, 0);
}
function get_customer_list_count($o_main, $filter, $search_filter){
    $search_filter = "";
    return get_customer_list($o_main, $filter, $search_filter, 0, 0);
}
function get_customer_list($o_main, $filter, $search_filter, $page=1, $perPage=100, $customer_id = null) {
    $search_by = '';
	if($search_filter != "" && $page > 0 && $perPage > 0)
    {
        $perPage = 50;
        $perPage = $perPage * ($page - 1);
        $offset = 0;
        if($page == 1)
        {
            $offset = 0;
            $perPage = 5;
        }
    } else {
        $offset = ($page-1)*$perPage;
        if($offset < 0){
            $offset = 0;
        }
    }

    $pager = " LIMIT ".$perPage ." OFFSET ".$offset;
    $list = array();
    $search_filter_sql_select = "";
    $search_filter_sql_join = "";
    $search_filter_sql_where = "";
    if($search_filter != "")
	{
        $search_filter_reg = str_replace(" ", "|",$search_filter);
        switch($search_by){
            /*case 1:
                $search_filter_sql_select = "";
                $search_filter_sql_join = "";
                $search_filter_sql_where = " AND ((c.name REGEXP '".$search_filter."' AND (c.customerType is null OR c.customerType = 0)) OR ((c.name REGEXP '".$search_filter_reg."' OR c.middlename REGEXP '".$search_filter_reg."' OR c.lastname REGEXP '".$search_filter_reg."') AND c.customerType = 1) OR c.publicRegisterId LIKE '%".$o_main->db->escape_like_str($search_filter)."%' OR c.publicRegisterId LIKE '%".$o_main->db->escape_like_str(str_replace(' ', '',$search_filter))."%' OR cexternl.external_sys_id LIKE '%".$o_main->db->escape_like_str($search_filter)."%' OR cexternl.external_id LIKE '%".$o_main->db->escape_like_str($search_filter)."%'".($customer_basisconfig['activate_shop_name'] ? " OR c.shop_name LIKE '%".$search_filter."%'" : "")."".$subunit_sql_where.")";

            break;*/
            default:
                $search_filter_sql_select = "";
                $search_filter_sql_join = "";
                $search_filter_sql_where = " AND (cr.companyname LIKE '%".$o_main->db->escape_like_str($search_filter)."%' OR cr.companypostalbox LIKE '%".$o_main->db->escape_like_str($search_filter)."%' OR cr.companyzipcode LIKE '%".$o_main->db->escape_like_str($search_filter)."%' OR cr.companypostalplace LIKE '%".$o_main->db->escape_like_str($search_filter)."%')";

        }
    }
    if($page == 0 && $perPage == 0){
        $pager = "";
    }
    $filter_content_where = " cr.content_status < 2";

    $showDefaultList = false;
    if($filter == "all") {
        $showDefaultList = true;
        $filter_content_where = " cr.content_status < 2";
    } else if ($filter == "deleted") {
        $filter_content_where = " cr.content_status = 2";
    }

    $sql = "SELECT cr.id, cr.created, cr.companyname, cr.companypostalbox, cr.companyzipcode, cr.companypostalplace, cr.customer_id".$search_filter_sql_select."
            FROM creditor AS cr
			".$search_filter_sql_join."
            WHERE ".$filter_content_where.$search_filter_sql_where;

    if($customer_id != null) {
        $list = array();
        $sql .= " GROUP BY cr.id ".(''!=$s_custom_order_by ? $s_custom_order_by : "ORDER BY cr.companyname ASC").$pager;
		
        $o_query = $o_main->db->query($sql);
        if($o_query && $o_query->num_rows()>0){
            $customerList = $o_query->result_array();
            foreach($customerList as $index=>$customer) {
                if($customer['id'] == $customer_id) {
                    $currentCustomerIndex = $index;
                    break;
                }
            }
            array_push($list, $customerList[$currentCustomerIndex-1]);
            array_push($list, $customerList[$currentCustomerIndex]);
            array_push($list, $customerList[$currentCustomerIndex+1]);
        }

        return $list;
    } else {
        $sql .= " GROUP BY cr.id";
        if($page == 0 && $perPage == 0){
            $rowCount = 0;
            $o_query = $o_main->db->query($sql);
            if($o_query){
                $rowCount = $o_query->num_rows();
            }
            return $rowCount;
        } else {
            $sql .= (''!=$s_custom_order_by ? $s_custom_order_by : " ORDER BY cr.companyname ASC").$pager;
            $f_check_sql = $sql;
			
			$o_query = $o_main->db->query($sql);
            if($o_query && $o_query->num_rows()>0){
                $list = $o_query->result_array();
            }
            return $list;
        }
    }
}
