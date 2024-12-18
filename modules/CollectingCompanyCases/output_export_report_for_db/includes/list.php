<?php
require_once __DIR__ . '/list_btn.php';
$s_sql = "SELECT * FROM collecting_company_case_report WHERE conetent_status = 0 ORDER BY id DESC";
$o_query = $o_main->db->query($s_sql);
$v_collecting_company_case_report = ($o_query && $o_query->num_rows()>0) ? $o_query->result_array() : array();
?>
<div id="p_container" class="p_container">
	<div class="p_containerInner">
		<div class="p_content">
			<div class="p_pageContent">
				<div class="p_contentBlock">
                    <a href="#" class="show_credit_report_for_db">
                        <?php echo $formText_ShowCreditReportForDb_Output; ?>
                    </a>&nbsp;&nbsp;&nbsp;&nbsp;
                    <a href="#" class="show_credit_report_for_db" data-save="1">
                        <?php echo $formText_GenerateCreditReportForDb_Output; ?>
                    </a>
				</div>
				<div class="p_contentBlock">
					<h4><?php echo $formText_History_Output; ?></h4>
					<?php if (count($v_collecting_company_case_report)): ?>
					<div class="gtable" id="gtable_search">
						<div class="gtable_row">
							<div class="gtable_cell gtable_cell_head"><?php echo $formText_CreatedDate_output;?></div>
							<div class="gtable_cell gtable_cell_head"><?php echo $formText_DownloadFile_output;?></div>
							<div class="gtable_cell gtable_cell_head"><?php echo $formText_Send_output;?></div>
						</div>

						<?php foreach($v_collecting_company_case_report as $v_row): ?>
							<?php
							$file = json_decode($v_row['file']);
							$fileUrl = $extradomaindirroot.'/../'.$file[0][1][0].'?accountname='.$_GET['accountname'].'caID='.$_GET['caID'].'&table=collecting_company_case_report&field=file&ID='.$file[0][4];
							?>
							<div class="gtable_row">
								<div class="gtable_cell"><?php echo date('d.m.Y', strtotime($v_row['created'])); ?></div>
								<div class="gtable_cell">
									<a href="<?php echo $fileUrl; ?>"><?php echo $formText_DownloadFile_output; ?></a>
								</div>
								<div class="gtable_cell c6">
									<?php if(!$v_row['sentTime']): ?>
										<a href="#" class="sendExport" data-id="<?php echo $v_row['id']; ?>"><?php echo $formText_Send_output; ?></a>
									<?php else: ?>
										<?php echo date('d.m.Y', strtotime($v_row['sentTime'])); ?>
									<?php endif; ?>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				<?php else: ?>
					<div class="noResultsPanel"><?php echo $formText_NoPreviousExportResults_output; ?></div>
				<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
var out_popup;
var out_popup_options={
    follow: [true, false],
    modalClose: false,
    escClose: false,
    closeClass:'b-close',
    onOpen: function(){
        $(this).addClass('opened');
        //$(this).find('.b-close').on('click', function(){out_popup.close();});
    },
    onClose: function(){
        $(this).removeClass('opened');
        if($(this).is('.close-reload')) {
            var redirectUrl = $(this).data("redirect");
            if(redirectUrl != "" && redirectUrl != undefined){
                document.location.href = redirectUrl;
            } else {
                loadView("list");
            }
          // window.location.reload();
        }
    }
};

$(document).ready(function() {

    $('.sendExport').on('click', function(e) {
        e.preventDefault();
        e.preventDefault();
        var data = {
            exportId: $(this).data('export-id'),
            idFrom: $(this).data('id-from'),
            idTo: $(this).data('id-to')
        };

        ajaxCall('sendExport', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });

    });

    $(".show_credit_report_for_db").off('click').on('click', function(event) {
        event.preventDefault();
		var extra_param = '';
		if(1 == $(this).data('save')) extra_param = "&generate=1"
		fetch('<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folder=".$folder."&folderfile=ajax_credit_report_for_db&fwajax=1&fw_nocss=1"; ?>' + extra_param/*, { method: "POST", body: JSON.stringify({ fwajax: 1, fw_nocss: 1 })}*/)
		.then(resp => resp.blob())
		.then(blob => {
			const url = window.URL.createObjectURL(blob);
			const a = document.createElement('a');
			a.style.display = 'none';
			a.href = url;
			// the filename you want
			a.download = 'export.txt';
			document.body.appendChild(a);
			a.click();
			window.URL.revokeObjectURL(url);
			fw_loading_end();
		})
		.catch(() => fw_loading_end());
    })
});

</script>

<style>
.noResultsPanel {
    background:#FFF;
    padding:10px 15px;
    border:1px solid #efecec;
}

.ownercompany_export_history_block {
    margin-bottom:60px;
}

.ownercompany_export_history_block_title {
    margin-bottom:10px;
    padding-bottom:10px;
    border-bottom:1px solid #EEE;
    font-weight:bold;
    margin: 0px 10px 10px 10px;
}
.ownercompany_export_history_block h4 {
    padding: 0px 10px;
}
</style>
