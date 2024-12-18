<?php
function uploadingImportFile() {
    $randomnumber = time();
    $allowedExts = array("xlsx");
    $extension = strtolower(end(explode(".", $_FILES["fileImport"]["name"])));
    mkdir(dirname(__FILE__)."/../../../../uploads/importData");
    $importPath = realpath(dirname(__FILE__)."/../../../../uploads/importData");
    $myfiles = array();
    if ($handle = opendir($importPath)) {
        while (($file = readdir($handle)) !== false){
            if (!in_array($file, array('.', '..')) && !is_dir($importPath."/".$file)){
                if(is_file($importPath."/".$file)) {
                    $created = filemtime($importPath ."/". $file);
                    $myfiles[$created] = $file;
                }
            }
        }
        krsort($myfiles);
    }
    if(count($myfiles) >= 10){
        unlink($importPath ."/".end($myfiles));
    }
    if (in_array($extension, $allowedExts))	{
        if ($_FILES["fileImport"]["error"] > 0) {
            echo "Return Code: " . $_FILES["fileImport"]["error"] . "<br>";
        } else {
            if (file_exists($importPath."/".$randomnumber."". $_FILES["fileImport"]["name"])){
                echo $_FILES["fileImport"]["name"] . " already exists. ";
            }else {
                move_uploaded_file($_FILES["fileImport"]["tmp_name"],
                $importPath."/".$randomnumber. "". $_FILES["fileImport"]["name"]);
                umask(0);
                chmod($importPath."/".$randomnumber. "". $_FILES["fileImport"]["name"],0777);
                //echo "Stored in: " . "upload/" . $_FILES["file"]["name"];
            }
        }
        return($importPath."/".$randomnumber. "". $_FILES["fileImport"]["name"]);
    }else {
        // echo "Invalid file";
        return false;
    }
}
if($moduleAccesslevel > 0){
    if($_POST['output_form_submit']) {
        $supplier_id = $_POST['supplier_id'];
        $sql = "SELECT * FROM article_supplier WHERE id = ".$o_main->db->escape($supplier_id);
        $o_query = $o_main->db->query($sql);
        $supplier = $o_query ? $o_query->row_array() : array();
        if($supplier){
            if($_FILES["fileImport"]["error"] == 0){
                $file = uploadingImportFile();
                if($file){
                    require_once dirname(__FILE__) . '/PHPExcel/PHPExcel/IOFactory.php';
                    $inputFileType = PHPExcel_IOFactory::identify($file);
                    $objReader = PHPExcel_IOFactory::createReader($inputFileType);
                    $objReader->setReadDataOnly(FALSE);
                    $objPHPExcel = $objReader->load($file);
                    $total_sheets=$objPHPExcel->getSheetCount();
                    $allSheetName=$objPHPExcel->getSheetNames();
                    $objWorksheet = $objPHPExcel->setActiveSheetIndex(0);
                    $highestRow = $objWorksheet->getHighestDataRow();
                    $highestColumn = $objWorksheet->getHighestDataColumn();
                    $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
                    $arraydata = array();
                    for ($row = 2; $row <= $highestRow;++$row) {
                        for ($col = 0; $col < $highestColumnIndex;++$col) {
                            $value = $objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
                            if($value == NULL || is_object($value) || is_array($value) || ($value != "" && $value[0] == "=")){
                                try{
                                    $value = $objWorksheet->getCellByColumnAndRow($col, $row)->getCalculatedValue();
                                }catch(Exception $e){
                                    $value = $objWorksheet->getCellByColumnAndRow($col, $row)->getOldCalculatedValue();
                                }
                            }
                            $value = trim($value);
                            if(strtolower($value) != "#ref!" && strtolower($value) != "#n/a" && $value != "0"){
                                $arraydata[$row-1][$col] = $value;
                            }
                        }
                    }
                    $sql = "SELECT * FROM article WHERE content_status < 2 AND article_supplier_id = ".$o_main->db->escape($supplier['id']);
                    $o_query = $o_main->db->query($sql);
                    $activeArticles = $o_query ? $o_query->result_array() : array();

                    $importedArticleIds = array();
                    $errors = array();
    				foreach($arraydata as $arrayrow) {
    					$articleCode = trim($arrayrow[0]);
    					$name = trim($arrayrow[1]);
    					$sales_unit = trim($arrayrow[2]);
    					$supplier_product_group = trim($arrayrow[6]);
    					$supplier_product_category = trim($arrayrow[7]);
    					$costPrice = trim($arrayrow[8]);
    					$price = trim($arrayrow[11]);

                        if($articleCode != ""){
                            $sql = "SELECT * FROM article WHERE articleCode = ".$o_main->db->escape($articleCode)." AND article_supplier_id = ".$o_main->db->escape($supplier['id']);
                            $o_query = $o_main->db->query($sql);
                            $article = $o_query ? $o_query->row_array() : array();
                            if($article){
                                $sql = "UPDATE article SET updated = NOW(), updatedBy = ?, name = ?, sales_unit = ?, supplier_product_group = ?, supplier_product_category = ?, costPrice = ?, price = ?, content_status = 0, defaultVatCodeWithVat = ?, defaultSalesAccountWithVat = ? WHERE id = ?";
                                $o_query = $o_main->db->query($sql, array($variables->loggID, $name, $sales_unit, $supplier_product_group, $supplier_product_category, $costPrice, $price, $supplier['defaultVatCodeWithVat'], $supplier['defaultSalesAccountWithVat'], $article['id']));
                                if($o_query){
                                    $importedArticleIds[] = $article['id'];
                                } else {
                                    $errors[] = array("id"=>$articleCode, "msg"=>$formText_ErrorUpdatingDatabase_output);
                                }
                            } else {
                                $sql = "INSERT INTO article SET created = NOW(), createdBy = ?, name = ?, sales_unit = ?, supplier_product_group = ?, supplier_product_category = ?, costPrice = ?, price = ?, article_supplier_id = ?, articleCode = ?, defaultVatCodeWithVat = ?, defaultSalesAccountWithVat = ?";
                                $o_query = $o_main->db->query($sql, array($variables->loggID, $name, $sales_unit, $supplier_product_group, $supplier_product_category, $costPrice, $price, $supplier['id'], $articleCode, $supplier['defaultVatCodeWithVat'], $supplier['defaultSalesAccountWithVat']));
                                if($o_query){
                                    $importedArticleIds[] = $o_main->db->insert_id();
                                } else {
                                    $errors[] = array("id"=>$articleCode, "msg"=>$formText_ErrorUpdatingDatabase_output);
                                }
                            }
                        }
                    }
                    foreach($activeArticles as $activeArticle){
                        if(!in_array($activeArticle['id'], $importedArticleIds)){
                            $sql = "UPDATE article SET updated = NOW(), updatedBy = ?, content_status = 2 WHERE id = ?";
                            $o_query = $o_main->db->query($sql, array($variables->loggID, $activeArticle['id']));
                        }
                    }
                    if(count($errors) != 0) {
                        $fw_error_msg = $formText_TheseProductsHadIssueGettingSaved_output;
                        foreach($errors as $error){
                            $fw_error_msg.= "<br/>".$error['id']." ".$error['msg'];
                        }
                    }
                } else {
                    $fw_error_msg = $formText_WrongFile_output;
                }
            } else {
                $fw_error_msg = $formText_WrongFile_output;
            }
        } else {
            $fw_error_msg = $formText_SupplierMissing_output;
        }
        return;
    }
}
?>
<div class="popupform">
	<div id="popup-validate-message" style="display:none;"></div>
	<form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=uploadFile";?>" method="post" enctype="multipart/form-data">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="supplier_id" value="<?php echo $_POST['supplier_id']?>">
        <input type="hidden" name="redirect_url" value="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=list&priceMatrix=".$priceMatrixId."&discountMatrix=".$discountMatrixId."&list_filter=".$list_filter."&search_filter=".$search_filter; ?>">
		<div class="inner">

            <div class="line">
                <div class="lineTitle"><?php echo $formText_File_Output; ?></div>
                <div class="lineInput">
                    <input type="file" name="fileImport" id="fileImport"/>
                </div>
                <div class="clear"></div>
            </div>
		</div>

		<div class="popupformbtn">
			<button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
			<input type="submit" name="sbmbtn" value="<?php echo $formText_Save_Output; ?>">
		</div>
	</form>
</div>
<script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
<script type="text/javascript">

$(document).ready(function() {
    $("form.output-form").validate({
        submitHandler: function(form) {
            var data = new FormData(form);
            // $.each($('#fileImport')[0].files, function(i, file) {
            //     data.append('file-'+i, file);
            // });
            //
            // var formdata = $(form).serializeArray();
            // $(formdata ).each(function(index, obj){
            //     if(data[obj.name] != undefined) {
            //         if(Array.isArray(data[obj.name])){
            //             data[obj.name].push(obj.value);
            //         } else {
            //             data[obj.name] = [data[obj.name], obj.value];
            //         }
            //     } else {
            //         data[obj.name] = obj.value;
            //     }
            // });
            fw_loading_start();
            $.ajax({
                url: $(form).attr("action"),
                cache: false,
                type: "POST",
                dataType: "json",
                contentType: false,
                processData: false,
                data: data,
                success: function (data) {
                    fw_loading_end();
                    if(data.error !== undefined){
                        $("#popup-validate-message").html(data.error);
                        $("#popup-validate-message").show();
                    } else {
                        $("#popup-validate-message").html("");
                        out_popup.addClass("close-reload");
                        out_popup.close();
                    }
                }
            }).fail(function() {
                $("#popup-validate-message").html("<?php echo $formText_ErrorOccuredSavingContent_Output;?>", true);
                $("#popup-validate-message").show();
                $('#popupeditbox').css('height', $('#popupeditboxcontent').height());
                fw_loading_end();
            });
        },
        invalidHandler: function(event, validator) {
            var errors = validator.numberOfInvalids();
            if (errors) {
                var message = errors == 1
                ? '<?php echo $formText_YouMissed_validate; ?> 1 <?php echo $formText_field_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>'
                : '<?php echo $formText_YouMissed_validate; ?> ' + errors + ' <?php echo $formText_fields_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>';

                $("#popup-validate-message").html(message);
                $("#popup-validate-message").show();
                $('#popupeditbox').css('height', $('#popupeditboxcontent').height());
            } else {
                $("#popup-validate-message").hide();
            }
            setTimeout(function(){ $('#popupeditbox').height(''); }, 200);
        }
    });

    $('.datefield').datepicker({
        dateFormat: 'dd.mm.yy',
        firstDay: 1
    });
});

</script>
<style>

.selectDivModified {
    display:block;
}
.popupform, .popupeditform {
	width:100%;
	margin:0 auto;
	border:1px solid #e8e8e8;
	position:relative;
}
label.error { display: none !important; }
.popupform .popupforminput.error { border-color:#c11 !important;}
#popup-validate-message, .error-msg { font-weight:bold; color:#c11; padding-bottom:10px; }
/* css for timepicker */
.ui-timepicker-div .ui-widget-header { margin-bottom: 8px; }
.ui-timepicker-div dl { text-align: left; }
.ui-timepicker-div dl dt { height: 25px; margin-bottom: -25px; }
.ui-timepicker-div dl dd { margin: 0 10px 10px 65px; }
.ui-timepicker-div td { font-size: 90%; }
.ui-tpicker-grid-label { background: none; border: none; margin: 0; padding: 0; }
.clear {
	clear:both;
}
.inner {
	padding:10px;
}
.pplineV {
	position:absolute;
	top:0;bottom:0;left:70%;
	border-left:1px solid #e8e8e8;
}
.popupform input.popupforminput, .popupform textarea.popupforminput, .popupform select.popupforminput, .col-md-8z input {
	width:100%;
	border-radius: 4px;
	padding:5px 10px;
	font-size:12px;
	line-height:17px;
	color:#3c3c3f;
	background-color:transparent;
	-webkit-box-sizing: border-box;
	   -moz-box-sizing: border-box;
		 -o-box-sizing: border-box;
			box-sizing: border-box;
	font-weight:400;
	border: 1px solid #cccccc;
}
.popupform input.popupforminput.checkbox {
    width: auto;
}
.popupformname {
	font-size:12px;
	font-weight:bold;
	padding:5px 0px;
}
.popupforminput.botspace {
	margin-bottom:10px;
}
textarea {
	min-height:50px;
	max-width:100%;
	min-width:100%;
	width:100%;
}
.popupformname {
	font-weight: 700;
	font-size: 13px;
}
.popupformbtn {
	text-align:right;
	margin:10px;
}
.popupformbtn input {
	border-radius:4px;
	border:1px solid #0393ff;
	background-color:#0393ff;
	font-size:13px;
	line-height:0px;
	padding: 20px 35px;
	font-weight:700;
	color:#FFF;
	margin-left:10px;
}
.error {
	border: 1px solid #c11;
}
.popupform .lineTitle {
	font-weight:700;
}
.popupform .line .lineTitle {
	width:30%;
	float:left;
	font-weight:700;
	padding:5px 0;
}

.popupform .line .lineTitleWithSeperator {
    width:100%;
    margin: 20px 0;
    padding:0 0 10px;
    border-bottom:1px solid #EEE;
}

.popupform .line .lineInput {
	width:70%;
	float:left;
}
</style>
