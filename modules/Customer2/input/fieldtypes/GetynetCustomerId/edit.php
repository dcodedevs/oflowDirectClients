<?php
$items = explode("::",$field[11]);
?>
<input <?=$field_attributes;?> id="<?=$field_ui_id;?>" type="hidden" name="<?=$field[1].$ending;?>" value="<?=$field[6][$langID];?>" />
<span id="<?=$field_ui_id;?>_out"><?=$field[6][$langID];?></span>
<?php if(1==0 and $field[10] != 1 and $access >= 10) { ?>
<button id="<?=$field_ui_id;?>_chooseCustomer" class="btn btn-xs btn-primary" type="button"><?=$formText_Choose_Fieldtype;?></button>
<button id="<?=$field_ui_id;?>_grantAdminAccess" class="btn btn-xs btn-default" type="button"><?=$formText_GrantAdminAccess_Fieldtype;?></button>
<button id="<?=$field_ui_id;?>_grantDeveloperAccess" class="btn btn-xs btn-default" type="button"><?=$formText_GrantDeveloperAccess_Fieldtype;?></button>
<div class="modal fade" id="<?=$field_ui_id;?>modal" tabindex="-1" role="dialog" aria-labelledby="<?=$field_ui_id;?>modal" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            	<h4 class="modal-title"></h4>
            </div>
            <div class="modal-body">
				<form action="#" id="<?=$field_ui_id;?>_form">
				<div class="row">
					<div class="col-md-6"><input id="<?=$field_ui_id;?>_input" type="text" /></div>
					<div class="col-md-3"><input id="<?=$field_ui_id;?>_search" class="btn btn-xs btn-default" type="button" value="<?=$formText_Search_fieldtype;?>"/></div>
					<div class="col-md-3"><input id="<?=$field_ui_id;?>_add" class="btn btn-xs btn-default" type="button" value="<?=$formText_AddNew_fieldtype;?>"/></div>
				</div>
				</form>
				<div id="<?=$field_ui_id;?>_result"></div>
			</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal"></button>
			</div>
        </div>
    </div>
</div>
<?php } ?>
<script type="text/javascript">
$(function () {
	$('#<?=$field_ui_id;?>_chooseCustomer').on('click',function(){
		$('#<?=$field_ui_id;?>modal').on('show.bs.modal', function (event){
			var modal = $(this);
			modal.find('.modal-title').text('<?=$formText_SearchForExistingCustomerOrAddNew_GetynetCustomerID;?>');
			modal.find('.modal-body form').removeClass('hide');
			modal.find('.modal-footer button').text('<?=$formText_cancel_input;?>');
		}).modal('show');
	});
	$("#<?=$field_ui_id;?>_search").on('click', function () {
		$.ajax({
			cache: false,
			url: "<?=$extradir;?>/input/fieldtypes/<?=$field[4];?>/ajax_search.php",
			data: {accountname: '<?=$_GET['accountname'];?>', caID: '<?=$_GET['caID'];?>', searchtext: $("#<?=$field_ui_id;?>_input").val(), fnctn: 'change_<?=$field_ui_id;?>'},
			success: function (data) {
				if(data=='NO_RESULT') data = '<div class="alert alert-info"><?=$formText_NothingWasFoundTryWithDifferentNameOrAddNewCustomer_GetynetCustomerID;?></div>';
				else if(data=='ERROR') data = '<div class="alert alert-danger"><?=$formText_ErrorOccured_GetynetCustomerID;?></div>';
				$('#<?=$field_ui_id;?>_result').html(data);
				$('#<?=$field_ui_id;?>modal').modal('handleUpdate');
			}
		});
	});
	$("#<?=$field_ui_id;?>_add").on('click', function () {
		create_<?=$field_ui_id;?>();
	});
	
	$('#<?=$field_ui_id;?>_grantAdminAccess').on('click', function() {
		$.ajax({
			type: 'POST',
			/*dataType: 'json',*/
			url: '<?=$extradir;?>/input/fieldtypes/<?=$field[4];?>/ajax_grantAccess.php',
			cache: false,
			data: { companyID : $('#<?=$field_ui_id;?>').val() },
			success: function(data) {
				var msg = '<?=$formText_AdminAccessGranted_Fieldtype;?>';
				if(data=='ALREADY_EXIST') msg = '<?=$formText_YouHaveAlreadyAdminAccess_Fieldtype;?>';
				else if(data=='NO_ACCESS') msg = '<?=$formText_AccessDeniedOrGetynetCustomerIdMissingOrCustomerDoesNotHaveAnyAccount_Fieldtype;?>';
				$('#<?=$field_ui_id;?>_result').html(msg);
				$('#<?=$field_ui_id;?>modal').on('show.bs.modal', function (event){
					var modal = $(this);
					modal.find('.modal-title').text('<?=$formText_GrantAdminAccess_Fieldtype;?>');
					modal.find('.modal-body .row').addClass('hide');
					modal.find('.modal-footer button').text('<?=$formText_Close_input;?>');
				}).modal('show');
			}
		});
	});
	
	$('#<?=$field_ui_id;?>_grantDeveloperAccess').on('click', function() {
		$.ajax({
			type: 'POST',
			/*dataType: 'json',*/
			url: '<?=$extradir;?>/input/fieldtypes/<?=$field[4];?>/ajax_grantAccess.php',
			cache: false,
			data: { companyID : $('#<?=$field_ui_id;?>').val(), developer : 1 },
			success: function(data) {
				var msg = '<?=$formText_DeveloperAccessGranted_Fieldtype;?>';
				if(data=='ALREADY_EXIST') msg = '<?=$formText_YouHaveAlreadyDeveloperAccess_Fieldtype;?>';
				else if(data=='NO_ACCESS') msg = '<?=$formText_AccessDeniedOrGetynetCustomerIdMissing_Fieldtype;?>';
				$('#<?=$field_ui_id;?>_result').html(msg);
				$('#<?=$field_ui_id;?>modal').on('show.bs.modal', function (event){
					var modal = $(this);
					modal.find('.modal-title').text('<?=$formText_GrantDeveloperAccess_Fieldtype;?>');
					modal.find('.modal-body .row').addClass('hide');
					modal.find('.modal-footer button').text('<?=$formText_Close_input;?>');
				}).modal('show');
			}
		});
	});
});

function create_<?=$field_ui_id;?>(force)
{
	<?php
	$print_mandatory = $print_param = "";
	foreach($items as $item)
	{
		$obj = explode(":",$item);
		if(isset($fieldsStructure[$obj[0]]))
		{
			$item_ui = $fieldsStructure[$obj[0]]['ui_id'.$ending];
			if($obj[2] == 1)
			{
				$print_mandatory.='if(!$("#'.$item_ui.'") || $("#'.$item_ui.'").val().length == "") { $("#'.$field_ui_id.'_result").html("<div class=\'alert alert-danger\'>'.$obj[0].' '.$formText_isMandatory_input.'</div>"); return false; } ';
			}
			$print_param.=', '.$obj[1].': $("#'.$item_ui.'").val()';
		} else {
			$print_mandatory='$("#'.$field_ui_id.'_result").html("<div class=\'alert alert-danger\'>'.$formText_IncorrectlyConfiguredField_input.' ('.$obj[0].')</div>"); return false; ';
			break;
		}
	}
	print $print_mandatory;
	?>
	var param = {accountname: '<?=$_GET['accountname'];?>', caID: '<?=$_GET['caID'];?>'<?=$print_param;?>};
	if(force) param['FORCE'] = 1;
	$.ajax({
		url: "<?=$extradir;?>/input/fieldtypes/<?=$field[4];?>/ajax_createCompany.php",
		data: param,
		cache: false,
		success: function (data) {
			if(data == 'CUSTOMER_EXISTS')
			{
				bootbox.confirm({
					message:"<?=$formText_CustomerWithSameNameExistDoYouWantToSaveItAnyway_GetynetCustomerID;?>?",
					buttons:{confirm:{label:"<?=$formText_Yes_input;?>"},cancel:{label:"<?=$formText_No_input;?>"}},
					callback: function(result){
						if(result)
						{
							create_<?=$field_ui_id;?>(true);
						}
					}
				});
			} else if(data == 'ERROR')
			{
				$('#<?=$field_ui_id;?>_result').html('<div class="alert alert-danger"><?=$formText_ErrorOccured_GetynetCustomerID;?></div>');
			} else {
				change_<?=$field_ui_id;?>(data);
			}
		}
	});
}

function change_<?=$field_ui_id;?>(id)
{
	$('#<?=$field_ui_id;?>').val(id);
	$('#<?=$field_ui_id;?>_out').text(id);
	$('#<?=$field_ui_id;?>modal').modal('hide');
	if(typeof changed_<?=$field_ui_id;?>=='function')
	{
		changed_<?=$field_ui_id;?>(id);
	}
}
</script>