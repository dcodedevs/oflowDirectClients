<?php
$ownercompanyId = $_POST['ownercompanyId'] ? $o_main->db->escape_str($_POST['ownercompanyId']) : 0;
$cid = $_POST['cid'] ? $o_main->db->escape_str($_POST['cid']) : 0;

require_once __DIR__ . '/process_uploaded_image_to_entry.php';

$s_sql = "SELECT * FROM ownercompany_logos WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($cid));
$additionalLogo = $o_query ? $o_query->row_array() : array();

$s_sql = "SELECT * FROM moduledata WHERE name = '".$o_main->db->escape_str($module)."'";
$o_result = $o_main->db->query($s_sql);
$module_data = $o_result ? $o_result->row_array() : array();

$fwaFileuploadConfigs = array(
	array (
	  'module_folder' => 'OwnerCompany', // module id in which this block is used
	  'id' => 'logofileupload',
	  'upload_type'=>'image',
	  'content_table' => 'ownercompany_logos',
	  'content_field' => 'logo',
	  'content_id' => $cid,
	  'content_module_id' => $module_data['uniqueID'], // id of module
	  'dropZone' => 'block',
	  'callbackAll' => 'callBackOnUploadAll',
	  'callbackStart' => 'callbackOnStart',
	  'callbackDelete' => 'callbackOnDelete',
	)
);
/**

 * Update data on form submit
 */
if(isset($_POST['output_form_submit']))
{
    if($_POST['logo_width'] != "" && $_POST['logo_pos_x'] != "" && $_POST['logo_pos_y'] != "") {
        if($additionalLogo){
            $s_sql = "UPDATE ownercompany_logos SET created = NOW(), createdBy = ?, ownercompanyId = ?, logo_width = ?, logo_pos_x = ?, logo_pos_y = ? WHERE id = ?";
            $o_query = $o_main->db->query($s_sql, array($variables->loggID, $_POST['ownercompanyId'], $_POST['logo_width'], $_POST['logo_pos_x'], $_POST['logo_pos_y'], $additionalLogo['id']));

        } else {
            $s_sql = "INSERT INTO  ownercompany_logos SET created = NOW(), createdBy = ?, ownercompanyId = ?, logo_width = ?, logo_pos_x = ?, logo_pos_y = ?";
            $o_query = $o_main->db->query($s_sql, array($variables->loggID, $_POST['ownercompanyId'], $_POST['logo_width'], $_POST['logo_pos_x'], $_POST['logo_pos_y']));
            $cid = $o_query ? $o_main->db->insert_id(): 0;
        }
        if($cid > 0){
            // process images

            foreach($fwaFileuploadConfigs as $fwaFileuploadConfig) {
                $fieldName = $fwaFileuploadConfig['id'];
                $fwaFileuploadConfig['content_id'] = $cid;
                include( __DIR__ . "/fileupload10/contentreg.php");
            }
            $fw_redirect_url = 1;
        } else {
            $fw_error_msg[] = $formText_ErrorUpdatingEntry_output;
            return;
        }
    } else {
        $fw_error_msg[] = $formText_MissingFields_output;
        return;
    }
}
?>

<div class="popupformTitle"><?php echo $formText_AddImages_Output;?></div>
<div class="popupform">
    <form class="output-content-form main" action="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=addAdditionalLogos";?>" method="post">
    	<input type="hidden" name="fwajax" value="1">
    	<input type="hidden" name="fw_nocss" value="1">
    	<input type="hidden" name="output_form_submit" value="1">
    	<input type="hidden" name="ownercompanyId" value="<?php echo $ownercompanyId;?>" >
    	<input type="hidden" name="cid" value="<?php echo $cid;?>" >

        <?php
        $fwaFileuploadConfig = $fwaFileuploadConfigs[0];
        require __DIR__ . '/fileupload10/output.php';
        ?>

        <div class="line">
            <div class="lineTitle"><?php echo $formText_LogoWidth_output; ?></div>
            <div class="lineInput">
                <input type="text" class="popupforminput botspace" name="logo_width" value="<?php echo $additionalLogo['logo_width']; ?>" required autocomplete="off">
            </div>
            <div class="clear"></div>
        </div>
        <div class="line">
            <div class="lineTitle"><?php echo $formText_LogoPositionX_output; ?></div>
            <div class="lineInput">
                <input type="text" class="popupforminput botspace" name="logo_pos_x" value="<?php echo $additionalLogo['logo_pos_x']; ?>" required autocomplete="off">
            </div>
            <div class="clear"></div>
        </div>
        <div class="line">
            <div class="lineTitle"><?php echo $formText_LogoPositionY_output; ?></div>
            <div class="lineInput">
                <input type="text" class="popupforminput botspace" name="logo_pos_y" value="<?php echo $additionalLogo['logo_pos_y'];?>" required autocomplete="off">
            </div>
            <div class="clear"></div>
        </div>

    	<div class="popupformbtn"><input type="submit" class="saveFiles" name="sbmbtn" value="<?php echo $formText_save_output; ?>"></div>
    </form>
</div>

<script type="text/javascript">


function callBackOnUploadAll(data) {
    $('.popupformbtn .saveFiles').val('<?php echo $formText_Save; ?>').prop('disabled',false);

};
function callbackOnStart(data) {
    $('.popupformbtn .saveFiles').val('<?php echo $formText_UploadInProgress_output;?>...').prop('disabled',true);
};
function callbackOnDelete(data){
}

// // New uploaded images to process
// var imagesToProcess = [];
//
// function callbackOnImageUpload(data) {
//     for (first in data.result) break;
//     var uploaded_image = data.result[first][0];
//     imagesToProcess.push(uploaded_image.upload_id);
// }
// // function callbackOnStart(){
// // 	console.log(imagesToProcess);
// // 	if(imagesToProcess.length > 0){
// // 		// break;
// // 	}
// // }
// function callbackOnImageDelete(data) {
// 	if(data != undefined && data.upload_id != undefined){
// 		var index = imagesToProcess.indexOf(data.upload_id);
// 		if (index > -1) {
// 	  		imagesToProcess.splice(index, 1);
// 		}
// 	}
// }

$(document).ready(function() {
    $('.output-content-form').on('submit', function(event) {
        event.preventDefault();
        var formdata = $(this).serializeArray();
        var data = {};
        $(formdata ).each(function(index, obj){
            if(data[obj.name] != undefined) {
                if(Array.isArray(data[obj.name])){
                    data[obj.name].push(obj.value);
                } else {
                    data[obj.name] = [data[obj.name], obj.value];
                }
            } else {
                data[obj.name] = obj.value;
            }
        });
        // data.imagesToProcess = imagesToProcess;
        ajaxCall('addAdditionalLogos', data, function(json) {
            if(json.redirect_url !== undefined)
            {
                out_popup.addClass("close-reload");
                out_popup.close();
            }
        });
    });
});

</script>

<style>
.popupform, .popupeditform {
	width:100%;
	margin:0 auto;
	border:1px solid #e8e8e8;
	position:relative;
}
.popupeditform {
	border:none;
	border-top:1px dashed #e8e8e8;
}

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
.output-content-form input.popupforminput, .output-content-form textarea.popupforminput, .col-md-8z input {
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
.popupformname {
	font-size:12px;
	font-weight:bold;
	padding:5px 0px;
}
.popupforminput.botspace {
	margin-bottom:5px;
}
textarea.popupforminput {
	min-height:200px;
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
	border-radius: 4px;
	border:0px none;
	background-color:#0393ff;
	font-size:13px;
	line-height:0px;
	padding: 20px 35px;
	font-weight:700;
	color:#FFF;
}
.output-content-form input.error, .output-content-form textarea.error {
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
.popupform .line .lineInput {
	width:70%;
	float:left;
}

/**
 * Change image blocks
 */

.changeImage .changeImageUploadBlock {
    display:none;
}

.changeImageUploadBlock {
    padding:15px 0;
}
</style>
