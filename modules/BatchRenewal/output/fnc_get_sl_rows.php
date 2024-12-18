<?php
if(!function_exists("get_sl_rows")){
    function get_sl_rows($v_row, $nextrenewaldatevalue, $nextrenewaldate2, $batch_renewal_accountconfig){
        global $o_main;
        if(!$v_row['activate_specified_invoicing']){
            $s_sql = "SELECT * FROM subscriptionline WHERE subscribtionId = ?";
            $sl_rows = array();
            $o_query = $o_main->db->query($s_sql, array($v_row['id']));
            if($o_query && $o_query->num_rows()>0){
                $sl_rows = $o_query->result_array();
            }
        } else {
            // Create order for each workline
            $s_sql = "SELECT rsi.*, ww.date, ww.estimatedTimeuse, rsi.id as specified_invoicing_id FROM workplanlineworker ww
            LEFT OUTER JOIN repeatingorder_specified_invoicing_group rsig ON rsig.id = ww.specified_invoicing_id
            LEFT OUTER JOIN repeatingorder_specified_invoicing rsi ON rsi.repeatingorder_specified_invoicing_group_id = rsig.id
            WHERE ww.repeatingOrderId = ? AND ww.specified_invoicing_id > 0 AND ww.date >= ? AND ww.date <= ? AND (ww.absenceDueToIllness is null OR ww.absenceDueToIllness = 0) ORDER BY ww.date ASC, specified_invoicing_id ASC";
            $o_query = $o_main->db->query($s_sql, array($v_row['id'], date("Y-m-d", strtotime($nextrenewaldatevalue)), date("Y-m-d", strtotime($nextrenewaldate2))));
            $workplanlines = $o_query ? $o_query->result_array() : array();
            $sl_rows = array();
            // $sl_rows_prep = array();
            $sl_rows_count = array();
            $datesAdded = array();
            foreach($workplanlines as $temp_line){
                $addLine = false;
                if($temp_line['invoicingType'] == 0){
                    $temp_line['amount'] = $temp_line['estimatedTimeuse'];
                } else if($temp_line['invoicingType'] == 1){
                    $temp_line['amount'] = 1;
                }
                $temp_line['specified_invoicing'] = true;
                $temp_line['workDate'] = date("d.m.Y", strtotime($temp_line['date']));
                if($temp_line['invoicingType'] == 1){
                    if($batch_renewal_accountconfig['specifiedInvoicing_makeMultiWorkersIntoOneTime']){
                        if(!isset($datesAdded[$temp_line['workDate']])) {
                            $datesAdded[$temp_line['workDate']] = $temp_line;
                            $addLine = true;
                        }
                    } else {
                        $addLine = true;
                    }
                } else {
                    $addLine = true;
                }
                if($addLine) {
                    // $sl_rows_prep[$temp_line['date']."-".$temp_line['id']] = $temp_line;
                    $sl_rows_count[$temp_line['specified_invoicing_id']."_".$temp_line['workDate']][] = $temp_line;
                }
            }
            foreach($sl_rows_count as $specified_invoicing_id => $sl_rows_array) {
                foreach($sl_rows_array as $sl_rows_single){
                    if($batch_renewal_accountconfig['specifiedInvoicing_linesToShow'] > 0 && count($sl_rows_count[$specified_invoicing_id]) > $batch_renewal_accountconfig['specifiedInvoicing_linesToShow']){
                        if(!isset($sl_rows[$specified_invoicing_id])){
                            $sl_rows_single['combined_specified'] = true;
                            $sl_rows[$specified_invoicing_id] = $sl_rows_single;
                            $sl_rows[$specified_invoicing_id]['start_date'] = $sl_rows_single['workDate'];
                            $sl_rows[$specified_invoicing_id]['end_date'] = $sl_rows_single['workDate'];
                        } else {
                            $sl_rows[$specified_invoicing_id]['amount'] += $sl_rows_single['amount'];
                            if(strtotime($sl_rows_single['workDate']) < strtotime($sl_rows[$specified_invoicing_id]['start_date'])) {
                                $sl_rows[$specified_invoicing_id]['start_date'] = $sl_rows_single['workDate'];
                            }
                            if(strtotime($sl_rows_single['workDate']) > strtotime($sl_rows[$specified_invoicing_id]['end_date'])) {
                                $sl_rows[$specified_invoicing_id]['end_date'] = $sl_rows_single['workDate'];
                            }
                        }
                    } else {
                        if(isset($sl_rows[$sl_rows_single['date']."-".$sl_rows_single['id']])){
                            $sl_rows_single_new = $sl_rows[$sl_rows_single['date']."-".$sl_rows_single['id']];
                        } else {
                            $sl_rows_single_new = $sl_rows_single;
                            $sl_rows_single_new['amount'] = 0;
                        }
                        $sl_rows_single_new['amount']+=$sl_rows_single['amount'];
                        $sl_rows[$sl_rows_single['date']."-".$sl_rows_single['id']] = $sl_rows_single_new;
                    }
                }
            }
        }

        return $sl_rows;
    }
}
?>
