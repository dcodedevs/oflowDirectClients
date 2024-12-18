<?php
if(!function_exists("create_adjustment_letter")){
    function create_adjustment_letter($subscription, $sl_rows, $v_cpi_indexes){
        global $o_main;
        global $moduleID;
        //create pdf
        if(!class_exists("TCPDF"))
        {
            require_once(__DIR__."/includes/tcpdf/config/lang/eng.php");
            require_once(__DIR__."/includes/tcpdf/tcpdf.php");
        }

        include(__DIR__."/languagesOutput/no.php");

        $s_sql = "SELECT * FROM ownercompany WHERE id = ?";
        $o_query = $o_main->db->query($s_sql, array($subscription['ownercompany_id']));
        $v_settings = $o_query ? $o_query->row_array() : array();
        if($v_settings){
            $invoicelogo = json_decode($v_settings['invoicelogo'],true);
            if(!class_exists("MYPDF_LETTER")){
                class MYPDF_LETTER extends TCPDF {
                    protected $custom_filename;
                    protected $pageLabelCustom;
                    protected $invoicelogo;
                    protected $additional_logos;
                    protected $v_settings;

                    public function setFileNameCustom($custom_filename) {
                        $this->custom_filename = $custom_filename;
                    }
                    public function setPageLabelCustom($pageLabelCustom) {
                        $this->pageLabelCustom = $pageLabelCustom;
                    }
                    public function setCustomInfo($invoicelogo, $v_settings) {
                        $this->invoicelogo = $invoicelogo;
                        $this->v_settings = $v_settings;
                    }
                    public function Header() {
                        $invoicelogo = $this->invoicelogo;
                        $v_settings = $this->v_settings;
                        // $additional_logos = $v_settings['additional_logos'];
                        // if(count($additional_logos) > 0){
                        //     foreach($additional_logos as $additional_logo){
                        //         if(intval($additional_logo['logo_width']) > 0){
                        //             $additional_logo_image = json_decode($additional_logo['logo'], true);
                        //             $logoWidth = intval($additional_logo['logo_width']);
                        //             $yPos = $additional_logo['logo_pos_y'];
                        //             $xPos = $additional_logo['logo_pos_x']+105-$logoWidth/2;
                        //             list($width, $height, $type, $attr) = getimagesize(__DIR__."/../../../".$additional_logo_image[0][1][0]);
                        //             $ratio = $width/$height;
                        //             $logoHeight = $logoWidth/$ratio;
                        //             $this->Image(__DIR__."/../../../".$additional_logo_image[0][1][0], $xPos, $yPos, $logoWidth, $logoHeight, '', '', '', true, 300);
                        //
                        //         }
                        //     }
                        // }
                        if ($invoicelogo[0][1][0]) {
                            $divider = 3;
                            $logoWidth = (is_numeric($v_settings['invoicelogoWidth']) ? $v_settings['invoicelogoWidth'] : 100) / $divider;
                            $logoPosX = (is_numeric($v_settings['invoicelogoPositionX']) ? $v_settings['invoicelogoPositionX'] : 0) / $divider + 17;
                            $logoPosY = (is_numeric($v_settings['invoicelogoPositionY']) ? $v_settings['invoicelogoPositionY'] : 0) / $divider + 6;
                            $logoPosX = 200/2 - $logoWidth/2;
                            $this->Image(__DIR__."/../../../".$invoicelogo[0][1][0], $logoPosX, $logoPosY, $logoWidth, 0, '', '', '', true, 300);
                            $this->SetMargins(20, 35, 20);
                        }
                    }
                    // Page footer
                    public function Footer() {
                        // Position at 15 mm from bottom
                        $this->SetY(-10);
                        // Set font
                        $this->SetFont('verdana', '', 8);
                        // Page number
                        $this->Cell(180, 10, $this->pageLabelCustom.' '.$this->getAliasNumPage().'/'.$this->getAliasNbPages()." ".$this->custom_filename, 0, false, 'R', 0, '', 0, false, 'T', 'M');
                    }
                }
            }
            // create new PDF document
            $pdf = new MYPDF_LETTER(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'iso-8859-1', false);

            $s_sql = "SELECT * FROM ownercompany_logos WHERE ownercompanyId = ? ORDER BY sortnr";
            $o_query = $o_main->db->query($s_sql, array($subscription['ownercompany_id']));
            $additionalLogos = $o_query ? $o_query->result_array() : array();
            $v_settings['additional_logos'] = $additionalLogos;
            $pdf->setCustomInfo($invoicelogo, $v_settings);

            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('ERP');
            $pdf->SetTitle('Letter: '.$subscription['id']);
            $pdf->SetSubject('');
            $pdf->SetKeywords('');
            $pdf->setPrintHeader(true);
            $pdf->setPrintFooter(true);
            $pdf->SetFooterMargin(20);
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf->SetMargins(20, 15, 20);
            $pdf->SetAutoPageBreak(TRUE, 20);
            $pdf->setLanguageArray($l);
            $pdf->SetFont('verdana', '', 8);

            $pdf->AddPage();
            //$pdf->Image($extraimagedir.''.$v_invoice_log[0][1][0] , 20, 5, 40, 15, '', '', '', true, 300);
            //

            $cpiPercentage = 0;
            $adjustmentError = false;

            $s_key = date("Y-m-01", strtotime($subscription['nextCpiAdjustmentFoundationDate']));
            $indexItem = (isset($v_cpi_indexes[$s_key]) ? $v_cpi_indexes[$s_key] : array());

            $s_key = date("Y-m-01", strtotime($subscription['lastCpiAdjustmentFoundationDate']));
            $lastIndexItem = (isset($v_cpi_indexes[$s_key]) ? $v_cpi_indexes[$s_key] : array());
            if($indexItem && $indexItem['index_number'] > 0){
                $adjustmentIndex = str_replace(",",".",$indexItem['index_number']);
            } else {
                $adjustmentError = true;
            }

            if($lastIndexItem && $lastIndexItem['index_number'] > 0){
                $lastAdjustmentIndex = str_replace(",",".",$lastIndexItem['index_number']);
            } else {
                $adjustmentError = true;
            }
            if(!$adjustmentError){
                $cpiPercentage = ($adjustmentIndex - $lastAdjustmentIndex)*100/$lastAdjustmentIndex;
                $cpiPercentage = number_format($cpiPercentage, 2, ".", "");
            } else {
                $noError = false;
            }
            $adjustmentErrorMsg = "";
            if($adjustmentError){
                $adjustmentErrorMsg = $formText_CpiAdjustmentMissingIndexes_output;
            }

            $s_sql = "SELECT contactperson.* FROM contactperson_role_conn
            LEFT OUTER JOIN contactperson ON contactperson.id = contactperson_role_conn.contactperson_id
            WHERE contactperson_role_conn.subscriptionmulti_id = ? AND (contactperson_role_conn.role = 0 OR contactperson_role_conn.role is null OR contactperson_role_conn.role = 1)
            ORDER BY contactperson_role_conn.role DESC";
            $o_query = $o_main->db->query($s_sql, array($subscription['id']));
            $contactPerson = $o_query ? $o_query->row_array() : array();

            $s_sql = "SELECT * FROM customer WHERE id = ?";
            $o_query = $o_main->db->query($s_sql, array($subscription['customerId']));
            $v_customer = ($o_query ? $o_query->row_array() : array());

            if($v_customer['useOwnInvoiceAdress']) {
                $s_cust_addr_prefix = 'ia';
                $customerAddress = 'own address';
                $customerAddress = $v_customer['iaStreet1']."<br />".(!empty($v_customer['iaStreet2']) ? $v_customer['iaStreet2'] . '<br />' : '').$v_customer['iaPostalNumber']." ".$v_customer['iaCity'] . "<br>" . $v_customer['iaCountry'];
            } else {
                $s_cust_addr_prefix = 'pa';
                $customerAddress = $v_customer['paStreet']."<br />".(!empty($v_customer['paStreet2']) ? $v_customer['paStreet2'] . '<br />' : '').$v_customer['paPostalNumber']." ".$v_customer['paCity'] . "<br>" . $v_customer['paCountry'];
            }
            $s_customer = trim($v_customer['name']." ".$v_customer['middlename']." ".$v_customer['lastname'])."<br />".$customerAddress." <br /><br />";

            if($contactPerson) {
                $s_customer .= $formText_YourContactPerson_Output.': '.$contactPerson['name']." ".$contactPerson['middlename']." ".$contactPerson['lastname']." <br />";
            }

            $s_invoice_text = $v_settings['companyname']." <br />".$v_settings['companypostalbox']." <br />".$v_settings['companyzipcode']." ".$v_settings['companypostalplace'];
            if($v_settings['companyCountry'] != ""){
                $s_invoice_text .= ", ".$v_settings['companyCountry'];
            }
            $s_invoice_text .=" <br />".$formText_Phone.": ".$v_settings['companyphone'];
            $s_invoice_text .= " <br /><br />".$formText_date.': '.date("d.m.Y");

            if($subscription['nextRenewalDate'] == '0000-00-00')
				$nextrenewaldatevalue = $subscription['startDate'];
			else
				$nextrenewaldatevalue = $subscription['nextRenewalDate'];
			$lastdate = $nextdate = $nextrenewaldatevalue;
			$nextdate2 = strtotime($nextdate);
			//
			$nextrenewaldatevalue = date('d.m.Y', strtotime($nextrenewaldatevalue));
			if(intval($subscription['periodUnit']) == 0){
				$nextrenewaldate = date('Y-m-d',mktime(0, 0, 0, date('m',$nextdate2)+$subscription['periodNumberOfMonths'], date('j',$nextdate2),  date('y',$nextdate2)));
				$nextrenewaldate2 = date('d.m.Y',mktime(0, 0, 0, date('m',$nextdate2)+$subscription['periodNumberOfMonths'], date('j',$nextdate2),  date('y',$nextdate2))-24*60*60);
			} else {
				$nextrenewaldate = date('Y-m-d',mktime(0, 0, 0, date('m',$nextdate2), date('j',$nextdate2),  date('Y',$nextdate2)+$subscription['periodNumberOfMonths']));
				$nextrenewaldate2 = date('d.m.Y',mktime(0, 0, 0, date('m',$nextdate2), date('j',$nextdate2),  date('Y',$nextdate2)+$subscription['periodNumberOfMonths'])-24*60*60);
			}
			$lastdate2 = date('d.m.Y',mktime(0, 0, 0, date('m',$nextdate2), date('j',$nextdate2),  date('y',$nextdate2)));


            $html1 = '<br/><br/><table style="margin: 0" border="0" cellpadding="0" cellspacing="0" width="100%">';
            $html1_1 ='
                <tr>
                    <td width="340">'.($s_customer).'</td>
                    <td width="150"><span style="font-size:44px;"><b>'.$formText_CPIPriceAdjustment_output.'</b></span><br />'.($s_invoice_text).'</td>
                </tr>
                <tr><td colspan="2"></td></tr>';
            $html1_1 .= '<tr>
                <td colspan="2" style="font-weight: bold;">'.$formText_AdjustmentOfRent. ' '.date("d.m.Y", strtotime($subscription['nextCpiAdjustmentDate'])).'</td>
            </tr>';

            $html2_before='
                <tr>
                    <td colspan="2">';

            $html2_before.='<div style="margin-bottom: 10px;">'.$formText_ThisIsBasisForAdjustingRentalPrices_output.'</div>
                    </td>
                </tr>';

            $html_orderlines = '<tr>
                <td colspan="2">
                    <table class="table table-condensed" cellpadding="5">
                        <thead>
                            <tr>
                                <th></th>
                                <th>'.$formText_IndexMonth_output.'</th>
                                <th>'.$formText_IndexNr_output.'</th>
                                <th>'.$formText_DateAdjustment_output.'</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>'.$formText_LastAdjustment_output.'</td>
                                <td>'.date("d.m.Y", strtotime($subscription['lastCpiAdjustmentFoundationDate'])).'</td>
                                <td>'.$lastAdjustmentIndex.'</td>
                                <td>'.date("d.m.Y", strtotime($subscription['lastCpiAdjustmentDate'])).'</td>
                            </tr>
                            <tr>
                                <td>'.$formText_ThisAdjustment_output.'</td>
                                <td>'.date("d.m.Y", strtotime($subscription['nextCpiAdjustmentFoundationDate'])).'</td>
                                <td>'.$adjustmentIndex.'</td>
                                <td>'.date("d.m.Y", strtotime($subscription['nextCpiAdjustmentDate'])).'</td>
                            </tr>
                            <tr>
                                <td colspan="4">'.$formText_AdjustmentPercent_output.": ".number_format($cpiPercentage, 2, ",", "")."%".'</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="item-order">
						<table class="table table-condensed" cellpadding="5" style="border: 2px solid #cecece;">
                            <thead>
                                <tr>
			                        <td colspan="6"><b>'.$formText_PreviousPriceFrom_output.' '.date("d.m.Y", strtotime($subscription['lastCpiAdjustmentDate'])).'</b></td>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td width="30%"></td>
                                    <td width="10%">'.$formText_Amount_Output.'</td>
                                    <td width="20%">'.$formText_PricePerPiece_Output.'</td>
                                    <td width="10%">'.$formText_Discount_Output.'</td>
                                    <td width="20%">'.$formText_TotalPrice_Output.'</td>
                                    <td width="10%">'.$formText_CpiAdjustmentPercentage_Output.'</td>
                                </tr>';
                                foreach($sl_rows as $sl_row){
    								if($subscription['subscription_category'] == 1){
    									$totalAmount = 1 * $sl_row['amount'];
    								} else if($subscription['override_periods'] > 0) {
    									$totalAmount = $subscription['override_periods'] * $sl_row['amount'];
    								} else {
    									$totalAmount = $subscription['periodNumberOfMonths'] * $sl_row['amount'];
    								}

    								$totalRowPrice = $totalAmount * $sl_row['pricePerPiece'] * ((100-$sl_row['discountPercent'])/100);
    								//calculating new price
    								if($subscription['priceAdjustmentType'] == 1) {
    									$newPricePerPiece = round($sl_row['pricePerPiece'] * ((100+$subscription['annualPercentageAdjustment'])/100), 2);

    									$totalNewRowPrice = $totalAmount * $newPricePerPiece * ((100-$sl_row['discountPercent'])/100);
    								} else if($subscription['priceAdjustmentType'] == 2) {
    									$adjustmentIndexDiff = ($adjustmentIndex - $lastAdjustmentIndex) * (intval($sl_row['cpiAdjustmentFactor'])/100);
    									$newPricePerPiece = round($sl_row['pricePerPiece']/$lastAdjustmentIndex*($lastAdjustmentIndex+$adjustmentIndexDiff), 2);

    									$totalNewRowPrice = $totalAmount * $newPricePerPiece * ((100-$sl_row['discountPercent'])/100);
    								}

    								$totalTotal += $totalNewRowPrice;

                                    $html_orderlines .= '<tr>
                                        <td width="30%">'.$subscription['subscriptionName'] . " - ". $sl_row['articleName'].'</td>
                                        <td width="10%">'.$totalAmount.'</td>
                                        <td width="20%">'.number_format($sl_row['pricePerPiece'], 2, ",", " ").'</td>
                                        <td width="10%">'.number_format($sl_row['discountPercent'], 2, ",", " ").'%</td>
                                        <td width="20%">'.number_format($totalRowPrice, 2, ",", " ").'</td>
                                        <td width="10%">'.number_format($sl_row['cpiAdjustmentFactor'], 2, ",", "").'</td>
                                    </tr>';
                                }
                            $html_orderlines .= '</tbody>

                        </table><br/><br/>
                        <table class="table table-condensed" cellpadding="5" style="border: 2px solid #cecece;">
                            <thead>
                                <tr>
			                        <td colspan="6"><b>'.$formText_NewPriceFrom_output." ".date("d.m.Y", strtotime($subscription['nextCpiAdjustmentDate'])).'</b></td>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td width="30%"></td>
                                    <td width="10%">'.$formText_Amount_Output.'</td>
                                    <td width="20%">'.$formText_PricePerPiece_Output.'</td>
                                    <td width="10%">'.$formText_Discount_Output.'</td>
                                    <td width="20%">'.$formText_TotalPrice_Output.'</td>
                                    <td width="10%">'.$formText_CpiAdjustmentPercentage_Output.'</td>
                                </tr>';
                                foreach($sl_rows as $sl_row){
    								if($subscription['subscription_category'] == 1){
    									$totalAmount = 1 * $sl_row['amount'];
    								} else if($subscription['override_periods'] > 0) {
    									$totalAmount = $subscription['override_periods'] * $sl_row['amount'];
    								} else {
    									$totalAmount = $subscription['periodNumberOfMonths'] * $sl_row['amount'];
    								}

    								$totalRowPrice = $totalAmount * $sl_row['pricePerPiece'] * ((100-$sl_row['discountPercent'])/100);
    								//calculating new price
    								if($subscription['priceAdjustmentType'] == 1) {
    									$newPricePerPiece = round($sl_row['pricePerPiece'] * ((100+$subscription['annualPercentageAdjustment'])/100), 2);

    									$totalNewRowPrice = $totalAmount * $newPricePerPiece * ((100-$sl_row['discountPercent'])/100);
    								} else if($subscription['priceAdjustmentType'] == 2) {
    									$adjustmentIndexDiff = ($adjustmentIndex - $lastAdjustmentIndex) * (intval($sl_row['cpiAdjustmentFactor'])/100);
    									$newPricePerPiece = round($sl_row['pricePerPiece']/$lastAdjustmentIndex*($lastAdjustmentIndex+$adjustmentIndexDiff), 2);

    									$totalNewRowPrice = $totalAmount * $newPricePerPiece * ((100-$sl_row['discountPercent'])/100);
    								}

    								$totalTotal += $totalNewRowPrice;

                                    $html_orderlines .= '<tr>
                                        <td width="30%">'.$subscription['subscriptionName'] . " - ". $sl_row['articleName'].'</td>
                                        <td width="10%">'.$totalAmount.'</td>
                                        <td width="20%">'.number_format($newPricePerPiece, 2, ",", " ").'</td>
                                        <td width="10%">'.number_format($sl_row['discountPercent'], 2, ",", " ").'%</td>
                                        <td width="20%">'.number_format($totalNewRowPrice, 2, ",", " ").'</td>
                                        <td width="10%">'.number_format($sl_row['cpiAdjustmentFactor'], 2, ",", "").'</td>
                                    </tr>';
                                }
                            $html_orderlines .= '</tbody>

                        </table>
					</div>';

                $html_orderlines.= '</td>
                   </tr>';
            $html2_after ='
                <tr>
                    <td colspan="2">
                        <div style="margin-bottom: 20px;">'.$formText_BestRegards_output.'<br/>'.$v_settings['companyname'].'</div>
                    </td>
                </tr>';
                $html3_1.='
            </table>';

            // tcpd not outputting if there is space at the end of the span tag
            $checkTags = array("</strong>", "</em>", "</ul>", "</li>", "</ol>");

            foreach($checkTags as $checkTag) {
                $html2_before = str_replace(" ".$checkTag, $checkTag, $html2_before);
                $html2_after = str_replace(" ".$checkTag, $checkTag, $html2_after);
                $html_orderlines = str_replace(" ".$checkTag, $checkTag, $html_orderlines);
                $html4 = str_replace(" ".$checkTag, $checkTag, $html4);
            }
            $html2_before = str_replace(": ", ":", $html2_before);
            $html2_after = str_replace(": ", ":", $html2_after);
            $html_orderlines = str_replace(": ", ":", $html_orderlines);
            $html4 = str_replace(": ", ":", $html4);

            $html2_before = str_replace("&nbsp;", " ", $html2_before);
            $html2_after = str_replace("&nbsp;", " ", $html2_after);
            $html_orderlines = str_replace("&nbsp;", " ", $html_orderlines);
            $html4 = str_replace("&nbsp;", " ", $html4);

            $html2_before = html_entity_decode(preg_replace('/\t+/', '', $html2_before));
            $html2_after = html_entity_decode(preg_replace('/\t+/', '', $html2_after));
            $html_orderlines = html_entity_decode(preg_replace('/\t+/', '', $html_orderlines));
            $html4 = html_entity_decode(preg_replace('/\t+/', '', $html4));

            $htmlFrontpage_prefix = $html1.$html1_1;
            $htmlFrontpage = $html4.$html3.$html3_1;

            $html_before = $html2_before;
            $html_after = $html2_after.$html3.$html3_1;
            $html_prefix = $html1.$html1_1;

            $nextCpiAdjustmentDate = $subscription['nextCpiAdjustmentDate'];

            $file = $formText_Letter_output." ".$subscription['id']."-".$nextCpiAdjustmentDate."_".time()." ".$formText_From_output." ".$v_settings['name'];

            $file .= ".pdf";
            $pdf->setFileNameCustom($file);
            $pdf->setPageLabelCustom($formText_Page_output);
            $filepath = __DIR__."/../../../uploads/protected/cpi_letters/";
            if(!file_exists($filepath))
            {
                mkdir($filepath, 0777,true);
            }
            chmod($filepath, 0777);

            $pdf->SetFont('verdana', '', 9);
            $pdf->writeHTML($html_prefix, true, false, true, false, '');

            $pdf->SetFont('verdana', '', 9);
            $pdf->writeHTML($html_before, true, false, true, false, '');

            $cp =  $pdf->getPage();
            $pdf->startTransaction();

            $pdf->SetFont('verdana', '', 7);
            $pdf->writeHTML($html_orderlines, true, false, true, false, '');
            if ($pdf->getPage() > $cp) {
                $pdf->rollbackTransaction(true);//true is very important
                $pdf->AddPage();
                $pdf->writeHTML($html_orderlines, true, false, true, false, '');
            } else {
                $pdf->commitTransaction();
            }
            $pdf->SetFont('verdana', '', 9);
            $pdf->writeHTML($html_after, true, false, true, false, '');

            $pdf->lastPage();

            $pdf->Output($filepath.$file, 'F');//'FD');
            $result = false;
            if(file_exists($filepath.$file)){
                $result = true;
                $s_sql = "INSERT INTO subscriptionmulti_cpi_letter SET
                created = NOW(),
                moduleID = ?,
                cpi_adjustment_letter = ?,
                subscriptionmulti_id = ?,
                date = ?";
                $o_main->db->query($s_sql, array($moduleID, "uploads/protected/cpi_letters/".$file, $subscription['id'], date("Y-m-d", strtotime($subscription['nextCpiAdjustmentDate']))));
            }
            return $result;
        }
    }
}
?>
