<div class="p_headerLine">
    <?php
	if($variables->developeraccess >= 20)
	{
        ?>
		<div class="output-add-language btnStyle">
			<div class="plusTextBox active">
				<div class="text"><?php echo $formText_AddLanguage_Output;?></div>
			</div>
			<div class="clear"></div>
		</div>
        <?php
	}
	?>
</div>
<script type="text/javascript">
var out_popup;
var out_popup_options={
    follow: [true, true],
    followSpeed: 0,
    fadeSpeed: 0,
    modalClose: false,
    escClose: false,
    closeClass:'b-close',
    onOpen: function(){
        $(this).addClass('opened');
        //$(this).find('.b-close').on('click', function(){out_popup.close();});
    },
    onClose: function(){
        $(this).removeClass('opened');
        if($(this).is('.close-reload')) output_reload_page();
    }
};
$(function(){
	$('.output-add-language').off('click').on('click', function(e){
		e.preventDefault();
		var data = { languageID: '' };
		ajaxCall('edit_language', data, function(obj) {
			$('#popupeditboxcontent').html(obj.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	});
});
</script>
