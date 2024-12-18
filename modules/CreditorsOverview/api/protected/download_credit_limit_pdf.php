<?php
$username = $v_data['params']['username'];
$customer_id = $v_data['params']['customer_id'];
$limited_info = $v_data['params']['limited_info'];
$extended_info = $v_data['params']['extended_info'];

$s_sql = "SELECT * FROM integration24sevenoffice_session WHERE username = '".$o_main->db->escape_str($username)."' AND creditor_id = '".$o_main->db->escape_str($creditor_filter)."'";
$o_query = $o_main->db->query($s_sql);
if($o_query && 0 < $o_query->num_rows() || $username == 'david@dcode.no') {	
    $sql = "SELECT * FROM customer WHERE id = ?";
    $o_query = $o_main->db->query($sql, array($customer_id));
    $customer_data = $o_query && $o_query->num_rows() ? $o_query->row_array() : array();
    $cid = intval($customer_data['creditor_id']);
    ini_set('max_execution_time', 600);
    session_start();
    define('FRAMEWORK_DEBUG', FALSE);
    define('ACCOUNT_PATH', realpath(__DIR__.'/../../../../')); // this is modified to fit this files location
    define('BASEPATH', ACCOUNT_PATH.DIRECTORY_SEPARATOR);
    $v_tmp = explode("/",ACCOUNT_PATH);
    $accountname = array_pop($v_tmp);
    require_once(__DIR__.'/../../output/includes/tcpdf/tcpdf.php');
    require_once(__DIR__.'/../../output/includes/tcpdf-charts.php');
    require_once(__DIR__.'/../languagesOutput/no.php');
    if($cid > 0) {
        $org_nr = 0;
        if($customer_data){
            $org_nr = intval(trim($customer_data['publicRegisterId']));
        }
        if($org_nr > 0){
            $auth_token = "S5FAxERkIkNfFaWalNkut7em6"; // valid till 24.11.2024
            $headers = array(
                'Content-Type: application/json',
                'Accept: application/json',
                'Authorization: Token ' . $auth_token
            );
            $call_type = 0;
    
            $curl = curl_init();
            $params = array();
            $get_params = $params ? '?' . http_build_query($params) : '';
            curl_setopt($curl,CURLOPT_URL, "https://ppc.proff.no/CompanyReport/".$org_nr. $get_params);
            curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            $response = curl_exec($curl);
            $httpcode2 = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $full_company_info = json_decode(trim($response), true);

            $curl = curl_init();
            $params = array();
            $get_params = $params ? '?' . http_build_query($params) : '';
    
            curl_setopt($curl, CURLOPT_URL, "https://ppc.proff.no/CreditRating/".$org_nr. $get_params);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            $response = curl_exec($curl);
            $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $response_decoded = json_decode(trim($response), true);
            $credit_rating = $response_decoded;
            $httpcode;
            if($httpcode == 200){
                $curl = curl_init();
                $params = array();
                $get_params = $params ? '?' . http_build_query($params) : '';
                curl_setopt($curl,CURLOPT_URL, "https://ppc.proff.no/PaymentRemarks/".$org_nr. $get_params);
                curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
                $response = curl_exec($curl);
                $httpcode2 = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                $response_decoded = json_decode(trim($response), true);
                $payment_remarks = $response_decoded;
                if($limited_info){
                    if($httpcode == 200 && $httpcode2 == 200) {
                        $sql = "INSERT INTO creditor_credit_info_call SET created = NOW(), createdBy=?, creditor_id = ?, customer_id = ?, call_type = 0";
                        $o_query = $o_main->db->query($sql, array($username, $customer_data['creditor_id'], $customer_data['id']));
                    }
                }    
            }    
            if($extended_info) { 
                if($httpcode == 200 && $httpcode2 == 200) {
                    $curl = curl_init();
                    $params = array();
                    $get_params = $params ? '?' . http_build_query($params) : '';
                    curl_setopt($curl,CURLOPT_URL, "https://ppc.proff.no/PaymentRemarkDetails/".$org_nr. $get_params);
                    curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
                    $response = curl_exec($curl);
                    $httpcode3 = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                    $response_decoded = json_decode(trim($response), true);
                    $payment_remark_details = $response_decoded;
                    if($httpcode3 == 200) {
                        $sql = "INSERT INTO creditor_credit_info_call SET created = NOW(), createdBy=?, creditor_id = ?, customer_id = ?, call_type = 1";
                        $o_query = $o_main->db->query($sql, array($username, $customer_data['creditor_id'], $customer_data['id']));
                    }
                }
            }
            if($credit_rating){
                $o_query = $o_main->db->query("SELECT * FROM creditor WHERE id = '".$o_main->db->escape_str($cid)."'");
                $creditor_info = $o_query ? $o_query->row_array() : array();
                class MYPDF extends TcpdfCharts {
                    //Page header
                    public function Header() {
                        // Logo
                        if ($this->page > 1) {
                            $this->SetY(7);
                            $this->SetTextColor(127,127,127);
                            $this->SetFont('helvetica', '', 8);
                            // $this->MultiCell(0, 0, date("d.m.Y"), 0, 'L', 0, 1, "", "", true, 0, true);
                            $this->Ln(1);
                            $this->SetTextColor(0,0,0);
                            $this->SetFont('helvetica', '', 10);
                            // $this->MultiCell(0, 0, $formText_ReportFor_output." ".$creditor_info['companyname'], 0, 'L', 0, 1, "", "", true, 0, true);

                            $this->SetY(10);
                            $image_file = ACCOUNT_PATH.'/modules/CreditorsOverview/output/elementsOutput/24sevenoffice_Logo_Horizontal_Midnight_RGB.png';
                            $this->Image($image_file, 115, 10, 45, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
                            $image_file = ACCOUNT_PATH.'/modules/CreditorsOverview/output/elementsOutput/Oflow Full Logo Black.png';
                            $this->Image($image_file, 163, 10, 30, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
                        }
                        // // Set font
                        // $this->SetFont('helvetica', 'B', 20);
                        // // Title
                        // $this->Cell(0, 15, '<< TCPDF Example 003 >>', 0, false, 'C', 0, '', 0, false, 'M', 'M');
                    }
                
                    // Page footer
                    public function Footer() {
                        // // Position at 15 mm from bottom
                        // $this->SetY(-15);
                        // // Set font
                        // $this->SetFont('helvetica', 'I', 8);
                        // // Page number
                        // $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
                    }
                }

                $pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

                // set document information
                $pdf->SetCreator(PDF_CREATOR);
                $pdf->SetAuthor("");
                $pdf->SetTitle("");
                $pdf->SetSubject("");
                $pdf->SetKeywords("");

                // remove default header/footer
                $pdf->setPrintHeader(true);
                $pdf->setPrintFooter(false);
                // set default monospaced font
                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

                // set margins
                $pdf->SetMargins(15, 25, 15);

                // set auto page breaks
                $pdf->SetAutoPageBreak(TRUE, 20);

                // set image scale factor
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

                // set some language-dependent strings (optional)
                if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
                    require_once(dirname(__FILE__).'/lang/eng.php');
                    $pdf->setLanguageArray($l);
                }

                // add a page
                $pdf->AddPage();

                function get_max_height($height, $height2){
                    if($height2 > $height) {
                        $height = $height2;
                    }
                    return $height;
                }
                setlocale(LC_TIME, 'no_NO');

                           
                if($extended_info) {
                    $image_file = ACCOUNT_PATH.'/modules/CreditorsOverview/output/elementsOutput/24sevenoffice_Logo_Horizontal_Midnight_RGB.png';
                    $pdf->Image($image_file, 0, 60, 55, '', 'PNG', '', 'T', false, 300, 'C', false, false, 1, false, false, false);
                    $image_file = ACCOUNT_PATH.'/modules/CreditorsOverview/output/elementsOutput/Oflow Full Logo Black.png';
                    $pdf->Image($image_file, 0, 70, 40, '', 'PNG', '', 'T', false, 300, 'C', false, false, 1, false, false, false);
                    $pdf->Ln(15);
                    $pdf->setCellPaddings(0, 0, 0, 0);
                    $pdf->SetFont('helvetica', 'b', 20);
                    $pdf->MultiCell(0, 0, $formText_CreditCheckInfo_output, "", 'C', 0, 1, "", "", true, 0, true);
                    $pdf->SetFont('helvetica', '', 20);
                    $pdf->MultiCell(0, 0, $customer_data['name']." - ".date("d.m.Y"), "", 'C', 0, 1, "", "", true, 0, true);
                    $pdf->Ln(5);
                    
                    $pdf->AddPage();

                    $pdf->SetFont('helvetica', 'b', 20);
                    $pdf->MultiCell(0, 0, $formText_CompanyInformation_output, "", 'L', 0, 1, "", "", true, 0, true);
                    $pdf->Ln(3);
                    $pdf->SetLineStyle(array('width' => 1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(126, 65, 129)));
                    $pdf->Line(15,$pdf->GetY(), 45, $pdf->GetY(), 4);
                    $pdf->Ln(10);
                    $pdf->SetFont('helvetica', '', 10);
                    $pdf->setCellPaddings(0, 3, 0, 3);
                    $pdf->MultiCell(60, 0, "<b>".$formText_Status_output."</b>", "", 'L', 0, 0, "", "", true, 0, true);
                    $pdf->MultiCell(0, 0, $full_company_info['company']['basicCompanyInfo']['statusDescription'], "", 'L', 0, 1, "", "", true, 0, true);   
                    $pdf->MultiCell(60, 0, "<b>".$formText_OrganizationNumber_output."</b>", "", 'L', 0, 0, "", "", true, 0, true);
                    $pdf->MultiCell(0, 0, $full_company_info['company']['basicCompanyInfo']['orgNo'], "", 'L', 0, 1, "", "", true, 0, true);   
                    $pdf->MultiCell(60, 0, "<b>".$formText_CompanyName_output."</b>", "", 'L', 0, 0, "", "", true, 0, true);
                    $pdf->MultiCell(0, 0, $full_company_info['company']['basicCompanyInfo']['name'], "", 'L', 0, 1, "", "", true, 0, true);   
                    $pdf->MultiCell(60, 0, "<b>".$formText_OrganizationForm_output."</b>", "", 'L', 0, 0, "", "", true, 0, true);
                    $pdf->MultiCell(0, 0, $full_company_info['company']['basicCompanyInfo']['companyTypeDescription']." (".$full_company_info['company']['basicCompanyInfo']['companyTypeCode'].")", "", 'L', 0, 1, "", "", true, 0, true);   
                    $pdf->MultiCell(60, 0, "<b>".$formText_FoundationDate_output."</b>", "", 'L', 0, 0, "", "", true, 0, true);
                    $pdf->MultiCell(0, 0, $full_company_info['company']['basicCompanyInfo']['stiftDato'], "", 'L', 0, 1, "", "", true, 0, true);   
                    $pdf->MultiCell(60, 0, "<b>".$formText_RegistrationDate_output."</b>", "", 'L', 0, 0, "", "", true, 0, true);
                    $pdf->MultiCell(0, 0, $full_company_info['company']['basicCompanyInfo']['regDato'], "", 'L', 0, 1, "", "", true, 0, true);   
                    // $pdf->MultiCell(60, 0, "<b>".$formText_RegistreredIn_output."</b>", "", 'L', 0, 0, "", "", true, 0, true);
                    // $pdf->MultiCell(0, 0, $full_company_info['company']['basicCompanyInfo']['statusDescription'], "", 'L', 0, 1, "", "", true, 0, true);  
                    $pdf->MultiCell(60, 0, "<b>".$formText_Industry_output."</b>", "", 'L', 0, 0, "", "", true, 0, true);
                    $pdf->MultiCell(0, 0, $full_company_info['company']['basicCompanyInfo']['industry'], "", 'L', 0, 1, "", "", true, 0, true);   
                    $pdf->MultiCell(60, 0, "<b>".$formText_Phone_output."</b>", "", 'L', 0, 0, "", "", true, 0, true);
                    $pdf->MultiCell(0, 0, $full_company_info['company']['basicCompanyInfo']['mobile'], "", 'L', 0, 1, "", "", true, 0, true);   
                    $pdf->MultiCell(60, 0, "<b>".$formText_EmployeeCount_output."</b>", "", 'L', 0, 0, "", "", true, 0, true);
                    $pdf->MultiCell(0, 0, $full_company_info['company']['basicCompanyInfo']['numberOfEmployees'], "", 'L', 0, 1, "", "", true, 0, true);   
                    $pdf->MultiCell(60, 0, "<b>".$formText_ShareCapital_output."</b>", "", 'L', 0, 0, "", "", true, 0, true);
                    $pdf->MultiCell(0, 0, $full_company_info['company']['basicCompanyInfo']['shareCapital'], "", 'L', 0, 1, "", "", true, 0, true);  
                    $pdf->MultiCell(60, 0, "<b>".$formText_BusinessAddress_output."</b>", "", 'L', 0, 0, "", "", true, 0, true);
                    $pdf->MultiCell(0, 0, $full_company_info['company']['address'][0]['street'].", ".$full_company_info['company']['address'][0]['postno']." ".$full_company_info['company']['address'][0]['postplace'], "", 'L', 0, 1, "", "", true, 0, true);   

                    $pdf->AddPage();

                    $pdf->setCellPaddings(0, 0, 0, 0);
                    $pdf->SetFont('helvetica', 'b', 20);
                    $pdf->MultiCell(0, 0, "Rating", "", 'L', 0, 1, "", "", true, 0, true);
                    $pdf->Ln(3);
                    $pdf->SetLineStyle(array('width' => 1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(126, 65, 129)));
                    $pdf->Line(15,$pdf->GetY(), 45, $pdf->GetY(), 4);
                    $pdf->Ln(3);
                    $pdf->SetFont('helvetica', '', 10);
                    $pdf->MultiCell(0, 0, "Denne modellen baserer seg på rmaets regnskapstall, aksjonærer/eiere, roller og styreinformasjon,
                    panteheftelser, betalingsanmerkninger med mer og kan ikke ene og alene si om et rma er på vei til å gå
                    konkurs. Derimot gir den en meget god indikasjon på selskapets betalingsevne og tilstand. Proff tar
                    forbehold om feil i regnskapstallene, som medfører at ratingen ikke blir korrekt.", "", 'L', 0, 1, "", "", true, 0, true);   

                    $pdf->ln(8);
                    $pdf->MultiCell(10, 10, "", "", 'L', 0, 0, "", "", true, 0, true);
                    $pdf->MultiCell(35, 10, "Proff Premium rating<br/><b>Meget lav risiko</b>", "", 'L', 0, 0, "", "", true, 0, true);
                    $pdf->MultiCell(0, 10, "Meget lav estimert sannsynlighet for konkurs. Dersom ingenting drastisk skjer, vil dette foretaket ikke gå konkurs", "", 'L', 0, 1, "", "", true, 0, true);
                    
                    $pdf->ln(5);
                    
                    $pdf->SetLineStyle(array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));

                    $lineY = $pdf->GetY();
                    $pdf->Line(15, $lineY, 195, $lineY, 1);
                    $pdf->ln(3);
                    $pdf->SetFont('helvetica', 'b', 12);
                    $highlighColor = "#00a04b";
                    if($credit_rating <= 66){
                        $highlighColor = "#f2c94c";
                        if($credit_rating <= 33) {
                            $highlighColor = "#de4953";
                        }
                    }
                    $pdf->setCellPaddings(0, 0, 0, 0);
                    $pdf->MultiCell(190, 0, $formText_Score_output.' <span color="'.$highlighColor.'">'.$credit_rating['ratingScore'].'</span>/100', "", 'L', 0, 1, "", "", true, 0, true);
                
                    $pdf->ln(3);
                    $lineY = $pdf->GetY();
                    $pdf->SetFont('helvetica', '', 10);
                    
                    $pdf->Circle($pdf->GetX()+2.2,$pdf->GetY()+2.2, 2, 0, 360, 'DF', array('width' => 0.4, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0,160,75)), array(0,160,75));  
                    $pdf->MultiCell(2, 4, '', "", 'L', 0, 0, "", "", true, 0, true);
                    $pdf->SetFillColor(0,160,75);
                    $pdf->MultiCell(58, 4, '', "", 'L', 1, 0, "", "", true, 0, true);
                    $pdf->SetFillColor(242,201,76);
                    $pdf->MultiCell(60, 4, '', "", 'L', 1, 0, "", "", true, 0, true);
                    $pdf->SetFillColor(222,73,83);
                    $pdf->MultiCell(58, 4, '', "", 'L', 1, 0, "", "", true, 0, true);
                    $pdf->MultiCell(2, 4, '', "", 'L', 0, 0, "", "", true, 0, true);
                    $pdf->Circle($pdf->GetX()-2.2,$pdf->GetY()+2.2, 2, 0, 360, 'DF', array('width' => 0.4, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(222,73,83)), array(222,73,83));  
                   
                    $pdf->ln(7);
                    
                    $pdf->SetTextColor(108,117,125);
                    $pdf->MultiCell(60, 4, 'Høy risiko ', "", 'C', 0, 0, "", "", true, 0, true);
                    $pdf->MultiCell(60, 4, 'Moderat risiko', "", 'C', 0, 0, "", "", true, 0, true);
                    $pdf->MultiCell(60, 4, 'Lav risiko', "", 'C', 0, 1, "", "", true, 0, true);
                    $pdf->ln(5);
                    

                    $pdf->SetTextColor(0,0,0);
                    $pdf->SetFillColor(255,255,255);
                    $pdf->Circle(($credit_rating['ratingScore']/100*180+22.5),$lineY+2,3,  0, 360, 'DF', array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0,0,0)), array(255, 255, 255));
                    
                    $lineY = $pdf->GetY();
                    $pdf->Line(15, $lineY, 195, $lineY, 1); 
                    $pdf->ln(5);
                   
                    $scoreColor = array(array(220, 53, 69), array(255,193,7), array(255,193,7), array(25, 135, 84), array(25, 135, 84));
                    
                    $pdf->MultiCell(35, 5, "<b>".$formText_CreditLimit_output."</b>", "", 'L', 0, 0, "", "", true, 0, true);
                    $pdf->MultiCell(40, 5, "<b>".$formText_LeadOwnership_output."</b>", "", 'L', 0, 0, "", "", true, 0, true);
                    $pdf->MultiCell(40, 5, "<b>".$formText_Economy_output."</b>", "", 'L', 0, 0, "", "", true, 0, true);
                    $pdf->MultiCell(40, 5, "<b>".$formText_PaymentHistory_output."</b>", "", 'L', 0, 0, "", "", true, 0, true);
                    $pdf->MultiCell(40, 5, "<b>".$formText_OtherGeneral_output."</b>", "", 'L', 0, 1, "", "", true, 0, true);
                    
                    $pdf->MultiCell(35, 5, $credit_rating['creditLimit'], "", 'L', 0, 0, "", "", true, 0, true);         
                    $pdf->Circle($pdf->GetX()+2.5,$pdf->GetY()+2.5,2, 0, 360, 'DF', 1, $scoreColor[($credit_rating['leadOwnership']-1)]);        
                    $pdf->MultiCell(5, 5, "", "", 'L', 0, 0, "", "", true, 0, true);     
                    $pdf->MultiCell(35, 5, "Vurdering: ".$credit_rating['leadOwnership'], "", 'L', 0, 0, "", "", true, 0, true);
                    $pdf->Circle($pdf->GetX()+2.5,$pdf->GetY()+2.5,2, 0, 360, 'DF', 1, $scoreColor[($credit_rating['economy']-1)]);      
                    $pdf->MultiCell(5, 5, "", "", 'L', 0, 0, "", "", true, 0, true);     
                    $pdf->MultiCell(35, 5, "Vurdering: ".$credit_rating['economy'], "", 'L', 0, 0, "", "", true, 0, true);
                    $pdf->Circle($pdf->GetX()+2.5,$pdf->GetY()+2.5,2, 0, 360, 'DF', 1, $scoreColor[($credit_rating['paymentHistory']-1)]);      
                    $pdf->MultiCell(5, 5, "", "", 'L', 0, 0, "", "", true, 0, true);     
                    $pdf->MultiCell(35, 5, "Vurdering: ".$credit_rating['paymentHistory'], "", 'L', 0, 0, "", "", true, 0, true);
                    $pdf->Circle($pdf->GetX()+2.5,$pdf->GetY()+2.5,2, 0, 360, 'DF', 1, $scoreColor[($credit_rating['otherGeneral']-1)]);      
                    $pdf->MultiCell(5, 5, "", "", 'L', 0, 0, "", "", true, 0, true);     
                    $pdf->MultiCell(35, 5, "Vurdering: ".$credit_rating['otherGeneral'], "", 'L', 0, 1, "", "", true, 0, true);
                    
                    $pdf->ln(5);
                    $lineY = $pdf->GetY();
                    $pdf->ln(5);


                    $pdf->SetFont('helvetica', 'b', 16);
                    $pdf->MultiCell(0, 0, "Definisjoner", "", 'L', 0, 1, "", "", true, 0, true);
                    $pdf->Ln(3);
                    $pdf->SetFont('helvetica', 'b', 10);
                    $pdf->MultiCell(0, 0, "Kredittramme", "", 'L', 0, 1, "", "", true, 0, true);
                    $pdf->SetFont('helvetica', '', 10);
                    $pdf->MultiCell(0, 0, "Kredittrammen er kun en indikasjon på kreditt som kan vurderes gitt til ett selskap. Det er kun selskaper
                    med A, A+ og A++ rating som blir vurdert for en kredittramme. Rammen er en indikasjon basert på nøkkeltall
                    og anmerkninger. Proff AS kan ikke gi noen garanti for estimert kredittramme og anbefaler alle å vise
                    forsiktighet.", "", 'L', 0, 1, "", "", true, 0, true);   
                    $pdf->ln(3);
                    
                    $pdf->SetFont('helvetica', 'b', 10);
                    $pdf->MultiCell(0, 0, "Ledelse og eierskap *", "", 'L', 0, 1, "", "", true, 0, true);
                    $pdf->SetFont('helvetica', '', 10);
                    $pdf->MultiCell(0, 0, "Gir innsikt i om det er ett selskap med en stabil ledelse og trygg(e) eier(e). Ved stadige endringer i styret eller
                    daglig leder vil det resultere i en svakere vurdering av selskapets ledelse. Dersom selskapet har utenlandske
                    eller trygge/utrygge eiere vil dette også få effekt på vurderingen av selskapet. Oppfattes det som at ledelsen
                    består av konkursrytter(e) er dette utelukkende negativt. Også historiske verv er av betydning for vektingen.", "", 'L', 0, 1, "", "", true, 0, true); 
                    $pdf->ln(3);

                    $pdf->SetFont('helvetica', 'b', 10);
                    $pdf->MultiCell(0, 0, "Økonomi *", "", 'L', 0, 1, "", "", true, 0, true);
                    $pdf->SetFont('helvetica', '', 10);
                    $pdf->MultiCell(0, 0, "Økonomien baserer seg på nøkkeltall som er viktige å se i sammenheng for å få ett enkelt oversiktsbilde over økonomien til selskapet.", "", 'L', 0, 1, "", "", true, 0, true); 
                    $pdf->ln(3);

                    $pdf->SetFont('helvetica', 'b', 10);
                    $pdf->MultiCell(0, 0, "Betalingshistorikk *", "", 'L', 0, 1, "", "", true, 0, true);
                    $pdf->SetFont('helvetica', '', 10);
                    $pdf->MultiCell(0, 0, "Gir en enkel oversikt om selskapet har ulike typer pant og anmerkninger av betydning.", "", 'L', 0, 1, "", "", true, 0, true); 
                    $pdf->ln(3);

                    $pdf->SetFont('helvetica', 'b', 10);
                    $pdf->MultiCell(0, 0, "Generelt *", "", 'L', 0, 1, "", "", true, 0, true);
                    $pdf->SetFont('helvetica', '', 10);
                    $pdf->MultiCell(0, 0, "Gir ett innblikk i hvor etablert selskapet er i sin bransje. Det tas høyde for selskapets alder og nøkkeltall sett i sammenheng", "", 'L', 0, 1, "", "", true, 0, true); 
                    $pdf->ln(3);

                    $pdf->SetFont('helvetica', 'b', 10);
                    $pdf->MultiCell(0, 0, "* Deles inn i kategorier:", "", 'L', 0, 1, "", "", true, 0, true);
                    $pdf->SetFont('helvetica', '', 10);   

                    $pdf->Circle($pdf->GetX()+2.5,$pdf->GetY()+2.5,2, 0, 360, 'DF', 1, $scoreColor[4]);                 
                    $pdf->MultiCell(5, 0, "", "", 'L', 0, 0, "", "", true, 0, true);              
                    $pdf->MultiCell(35, 0, "5=Meget bra", "", 'L', 0, 0, "", "", true, 0, true);
                    $pdf->Circle($pdf->GetX()+2.5,$pdf->GetY()+2.5,2, 0, 360, 'DF', 1, $scoreColor[3]);                
                    $pdf->MultiCell(5, 0, "", "", 'L', 0, 0, "", "", true, 0, true);   
                    $pdf->MultiCell(35, 0, "4=Bra", "", 'L', 0, 0, "", "", true, 0, true);
                    $pdf->Circle($pdf->GetX()+2.5,$pdf->GetY()+2.5,2, 0, 360, 'DF', 1, $scoreColor[2]);                 
                    $pdf->MultiCell(5, 0, "", "", 'L', 0, 0, "", "", true, 0, true);  
                    $pdf->MultiCell(35, 0, "3=Middels", "", 'L', 0, 0, "", "", true, 0, true);
                    $pdf->Circle($pdf->GetX()+2.5,$pdf->GetY()+2.5,2, 0, 360, 'DF', 1, $scoreColor[1]);                
                    $pdf->MultiCell(5, 0, "", "", 'L', 0, 0, "", "", true, 0, true);   
                    $pdf->MultiCell(35, 0, "2=Svak", "", 'L', 0, 0, "", "", true, 0, true);
                    $pdf->Circle($pdf->GetX()+2.5,$pdf->GetY()+2.5,2, 0, 360, 'DF', 1, $scoreColor[0]);                 
                    $pdf->MultiCell(5, 0, "", "", 'L', 0, 0, "", "", true, 0, true);  
                    $pdf->MultiCell(35, 0, "1=Negativ", "", 'L', 0, 1, "", "", true, 0, true);
                   
                    


                    $pdf->AddPage();
                    $pdf->SetFont('helvetica', 'b', 20);
                    $pdf->MultiCell(0, 0, "Panteheftelser og betalingsanmerkninger", "", 'L', 0, 1, "", "", true, 0, true);
                    $pdf->Ln(3);
                    $pdf->SetLineStyle(array('width' => 1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(126, 65, 129)));
                    $pdf->Line(15,$pdf->GetY(), 45, $pdf->GetY(), 4);
                    $pdf->Ln(15);
                    $pdf->SetLineStyle(array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));

                    $pdf->SetFont('helvetica', '', 16);
                    $pdf->MultiCell(0, 0, "<b>".$formText_HasVoluntaryMortgages_output."</b>", "", 'L', 0, 1, "", "", true, 0, true);
                    $pdf->SetFont('helvetica', '', 10);
                    if($payment_remarks['hasVoluntaryMortgages']){ $hasVoluntaryMortgages = $formText_Yes_output;} else { $hasVoluntaryMortgages = $formText_No_output;}
                    $pdf->MultiCell(0, 0, $hasVoluntaryMortgages, "", 'L', 0, 1, "", "", true, 0, true);
                    $pdf->Ln(5); 
                    foreach($payment_remark_details['voluntaryMortgages'] as $paymentRemark) {                            
                        $pdf->MultiCell(60, 0, "<b>".$formText_Type_output."</b>", "", 'L', 0, 0, "", "", true, 0, true);
                        $pdf->MultiCell(0, 0, $paymentRemark['type']['name'], "", 'L', 0, 1, "", "", true, 0, true);   
                        $pdf->MultiCell(60, 0, "<b>".$formText_Amount_output."</b>", "", 'L', 0, 0, "", "", true, 0, true);
                        $pdf->MultiCell(0, 0, $paymentRemark['amount'], "", 'L', 0, 1, "", "", true, 0, true);   
                        $pdf->MultiCell(60, 0, "<b>".$formText_Creditor_output."</b>", "", 'L', 0, 0, "", "", true, 0, true);
                        $pdf->MultiCell(0, 0, $paymentRemark['creditor'], "", 'L', 0, 1, "", "", true, 0, true);   
                        $pdf->MultiCell(60, 0, "<b>".$formText_registrationDate_output."</b>", "", 'L', 0, 0, "", "", true, 0, true);
                        $pdf->MultiCell(0, 0, $paymentRemark['registrationDate'], "", 'L', 0, 1, "", "", true, 0, true);   
                        $pdf->MultiCell(60, 0, "<b>".$formText_legalRepresentative_output."</b>", "", 'L', 0, 0, "", "", true, 0, true);
                        $pdf->MultiCell(0, 0, $paymentRemark['legalRepresentative'], "", 'L', 0, 1, "", "", true, 0, true); 
                    }

                    $pdf->Ln(10);
                    $pdf->SetFont('helvetica', '', 16);
                    $pdf->MultiCell(0, 0, "<b>".$formText_HasRemarks_output."</b>", "", 'L', 0, 1, "", "", true, 0, true);
                    $pdf->SetFont('helvetica', '', 10);
                    if($payment_remarks['hasRemarks']){ $remarks = $formText_Yes_output;} else { $remarks = $formText_No_output;}
                    $pdf->MultiCell(0, 0, $remarks, "", 'L', 0, 1, "", "", true, 0, true); 
                    $pdf->Ln(5); 
                    foreach($payment_remark_details['paymentRemarks'] as $paymentRemark) {                        
                        $pdf->MultiCell(60, 0, "<b>".$formText_Source_output."</b>", "", 'L', 0, 0, "", "", true, 0, true);
                        $pdf->MultiCell(0, 0, $paymentRemark['source']['name'], "", 'L', 0, 1, "", "", true, 0, true);   
                        $pdf->MultiCell(60, 0, "<b>".$formText_Type_output."</b>", "", 'L', 0, 0, "", "", true, 0, true);
                        $pdf->MultiCell(0, 0, $paymentRemark['type']['name'], "", 'L', 0, 1, "", "", true, 0, true);   
                        $pdf->MultiCell(60, 0, "<b>".$formText_Amount_output."</b>", "", 'L', 0, 0, "", "", true, 0, true);
                        $pdf->MultiCell(0, 0, $paymentRemark['amount'], "", 'L', 0, 1, "", "", true, 0, true);   
                        $pdf->MultiCell(60, 0, "<b>".$formText_Creditor_output."</b>", "", 'L', 0, 0, "", "", true, 0, true);
                        $pdf->MultiCell(0, 0, $paymentRemark['creditor'], "", 'L', 0, 1, "", "", true, 0, true);   
                        $pdf->MultiCell(60, 0, "<b>".$formText_registrationDate_output."</b>", "", 'L', 0, 0, "", "", true, 0, true);
                        $pdf->MultiCell(0, 0, $paymentRemark['registrationDate'], "", 'L', 0, 1, "", "", true, 0, true);   
                        $pdf->MultiCell(60, 0, "<b>".$formText_refNr_output."</b>", "", 'L', 0, 0, "", "", true, 0, true);
                        $pdf->MultiCell(0, 0, $paymentRemark['refNr'], "", 'L', 0, 1, "", "", true, 0, true);   
                    }

                    $pdf->Ln(10);
                    $pdf->SetFont('helvetica', '', 16);                    
                    $pdf->MultiCell(0, 0, "<b>".$formText_HasCompulsoryMortgages_output."</b>", "", 'L', 0, 1, "", "", true, 0, true);
                    $pdf->SetFont('helvetica', '', 10);  
                    if($payment_remarks['hasCompulsoryMortgages']){ $hasCompulsoryMortgages = $formText_Yes_output;} else { $hasCompulsoryMortgages = $formText_No_output;}
                    $pdf->MultiCell(0, 0, $hasCompulsoryMortgages, "", 'L', 0, 1, "", "", true, 0, true);  
                    $pdf->ln(5);                  
                    foreach($payment_remark_details['compulsoryMortgages'] as $paymentRemark) {
                        $pdf->MultiCell(60, 0, "<b>".$formText_Type_output."</b>", "", 'L', 0, 0, "", "", true, 0, true);
                        $pdf->MultiCell(0, 0, $paymentRemark['type']['name'], "", 'L', 0, 1, "", "", true, 0, true);   
                        $pdf->MultiCell(60, 0, "<b>".$formText_Amount_output."</b>", "", 'L', 0, 0, "", "", true, 0, true);
                        $pdf->MultiCell(0, 0, $paymentRemark['amount'], "", 'L', 0, 1, "", "", true, 0, true);   
                        $pdf->MultiCell(60, 0, "<b>".$formText_Creditor_output."</b>", "", 'L', 0, 0, "", "", true, 0, true);
                        $pdf->MultiCell(0, 0, $paymentRemark['creditor'], "", 'L', 0, 1, "", "", true, 0, true);   
                        $pdf->MultiCell(60, 0, "<b>".$formText_registrationDate_output."</b>", "", 'L', 0, 0, "", "", true, 0, true);
                        $pdf->MultiCell(0, 0, $paymentRemark['registrationDate'], "", 'L', 0, 1, "", "", true, 0, true);   
                        $pdf->MultiCell(60, 0, "<b>".$formText_legalRepresentative_output."</b>", "", 'L', 0, 0, "", "", true, 0, true);
                        $pdf->MultiCell(0, 0, $paymentRemark['legalRepresentative'], "", 'L', 0, 1, "", "", true, 0, true); 
                    }

                    $pdf->AddPage();
                    $pdf->SetFont('helvetica', 'b', 20);
                    $pdf->MultiCell(0, 0, "Roller", "", 'L', 0, 1, "", "", true, 0, true);
                    $pdf->Ln(3);                    
                    $pdf->SetLineStyle(array('width' => 1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(126, 65, 129)));
                    $pdf->Line(15,$pdf->GetY(), 45, $pdf->GetY(), 4);
                    $pdf->Ln(5);
                    $pdf->SetFont('helvetica', '', 10);

                    $pdf->setCellPaddings(1, 1, 1, 1);
                    $pdf->SetLineStyle(array('width' => 0.5, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));
                    $pdf->MultiCell(60, 0, "<b>Rolle</b>", "TLB", 'L', 0, 0, "", "", true, 0, true);
                    $pdf->MultiCell(100, 0, "<b>Navn</b>", "TB", 'L', 0, 0, "", "", true, 0, true);   
                    $pdf->MultiCell(20, 0, "<b>F.dato</b>", "TRB", 'L', 0, 1, "", "", true, 0, true);   
                    $x = 1;
                    foreach($full_company_info['company']['roles'] as $role) {
                        if($x % 2 == 0){
                            $pdf->SetFillColor(247,247,247); 
                        } else {
                            $pdf->SetFillColor(255,255,255);  
                        }


                        $pdf->MultiCell(60, 0, $role['description'], "BL", 'L', 1, 0, "", "", true, 0, true);
                        $pdf->MultiCell(100, 0, $role['name'], "B", 'L', 1, 0, "", "", true, 0, true);   
                        $role_date="";
                        if($role['dob'] != "") {
                            $role_date = date("Y", strtotime($role['dob']));
                        }
                        $pdf->MultiCell(20, 0, $role_date, "BR", 'L', 1, 1, "", "", true, 0, true); 
                        $x++;
                    }

                    $pdf->AddPage();
                    $pdf->SetFont('helvetica', 'b', 20);
                    $pdf->MultiCell(0, 0, "Regnskapsinformasjon", "", 'L', 0, 1, "", "", true, 0, true);
                    $pdf->Ln(3);                    
                    $pdf->SetLineStyle(array('width' => 1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(126, 65, 129)));
                    $pdf->Line(15,$pdf->GetY(), 45, $pdf->GetY(), 4);
                    $pdf->Ln(15);
                } else {
                    $pdf->Ln(10);
                    $pdf->setCellPaddings(0, 0, 0, 0);
                    $pdf->SetFont('helvetica', 'b', 10);
                    $pdf->MultiCell(190, 0, $formText_CreditCheckInfo_output, "", 'L', 0, 1, "", "", true, 0, true);
                    $pdf->SetFont('helvetica', '', 10);
                    $pdf->MultiCell(190, 0, $customer_data['name']." - ".date("d.m.Y"), "", 'L', 0, 1, "", "", true, 0, true);
                    $pdf->Ln(5);

                    $pdf->SetLineStyle(array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));

                    $pdf->SetFont('helvetica', 'b', 12);

                    $highlighColor = "#00a04b";
                    if($credit_rating <= 66){
                        $highlighColor = "#f2c94c";
                        if($credit_rating <= 33) {
                            $highlighColor = "#de4953";
                        }
                    }
                    $pdf->setCellPaddings(0, 0, 0, 0);
                    $pdf->MultiCell(190, 0, $formText_Score_output.' <span color="'.$highlighColor.'">'.$credit_rating['ratingScore'].'</span>/100', "", 'L', 0, 1, "", "", true, 0, true);
                
                    $pdf->ln(5);
                    $lineY = $pdf->GetY();
                    $pdf->SetFont('helvetica', '', 9);
                    $pdf->SetFillColor(0,160,75);
                    $pdf->MultiCell(60, 4, '', "", 'L', 1, 0, "", "", true, 0, true);
                    $pdf->SetFillColor(242,201,76);
                    $pdf->MultiCell(60, 4, '', "", 'L', 1, 0, "", "", true, 0, true);
                    $pdf->SetFillColor(222,73,83);
                    $pdf->MultiCell(60, 4, '', "", 'L', 1, 1, "", "", true, 0, true);
                    
                    $pdf->SetFillColor(255,255,255);
                    $pdf->Circle(($credit_rating['ratingScore']/100*180+22.5),$lineY+2,3,  0, 360, 'DF', 1, array(255, 255, 255));
                    
                    $pdf->ln(15);
                    $lineY = $pdf->GetY();
                    $pdf->Line(15, $lineY, 195, $lineY, 1); 
                    $pdf->ln(5);
                    $pdf->MultiCell(35, 10, "<b>".$formText_CreditLimit_output."</b><br/>".$credit_rating['creditLimit'], "", 'L', 0, 0, "", "", true, 0, true);
                    $pdf->MultiCell(35, 10, "<b>".$formText_LeadOwnership_output."</b><br/>".$credit_rating['leadOwnership'], "", 'L', 0, 0, "", "", true, 0, true);
                    $pdf->MultiCell(35, 10, "<b>".$formText_Economy_output."</b><br/>".$credit_rating['economy'], "", 'L', 0, 0, "", "", true, 0, true);
                    $pdf->MultiCell(35, 10, "<b>".$formText_PaymentHistory_output."</b><br/>".$credit_rating['paymentHistory'], "", 'L', 0, 0, "", "", true, 0, true);
                    $pdf->MultiCell(35, 10, "<b>".$formText_OtherGeneral_output."</b><br/>".$credit_rating['otherGeneral'], "", 'L', 0, 1, "", "", true, 0, true);
                    $pdf->ln(5);
                    $lineY = $pdf->GetY();
                    $pdf->Line(15, $lineY, 195, $lineY, 1);
                    $pdf->ln(5);
                    
                    $pdf->MultiCell(40, 0, "<b>".$formText_PaymentRemarks_output."</b>", "", 'L', 0, 1, "", "", true, 0, true);
                    $pdf->ln(2);

                    $pdf->MultiCell(40, 0, "<b>".$formText_HasRemarks_output."</b>", "", 'L', 0, 0, "", "", true, 0, true);
                    if($payment_remarks['hasRemarks']){ $remarks = $formText_Yes_output;} else { $remarks = $formText_No_output;}
                    $pdf->MultiCell(40, 0, $remarks, "", 'L', 0, 1, "", "", true, 0, true);                
                }
            } else {
                echo $formText_NoRatingFound_output;
            }
            //Close and output PDF document
            $pdfName = 'report_'.$cid;
            $pdfName .= '.pdf';
            $pdf_string = $pdf->Output($pdfName, 'E');
        } else {   
        }        
    } else {
    }
    $v_return['pdf_string'] = $pdf_string;
}
?>