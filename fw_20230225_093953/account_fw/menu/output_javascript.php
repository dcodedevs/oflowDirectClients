<script language="javascript" type="text/javascript">
var fw_ctrl_key_hold = fw_history_pushed = fw_click_instance = fw_changes_made = fw_editing_instance = false;
var fw_window_height, fw_scroll_active;
var fw_initial_url = location.href;

// Are we on mobile view?
var fw_collapsed_to_mobile = false;
var fw_collapsed_to_mobile_load_col2 = false;
var fw_collapsed_to_mobile_setup_done = false;


window.outputModuleViewportSettings = {};

$(function(){
	$('.fw_var_help_text').off('click').on('click', function(e){
		e.preventDefault();
		return false;
	});
	$('#fw_change_group_btn').on('click', function(e) {
		e.preventDefault();
		document.cookie = "selected_group=0";
		document.location.reload();
	});

	document.title = '<?php echo $variables->companyname.' - '.$variables->accountnamefriendly; ?>';
	<?php if(!isset($_POST['fwajax']) || $_POST['fwajax'] != "fw") { ?>
	setTimeout(fw_doSessionUpdate, <?php echo ($variables->fw_session['refreshtime'] > 300 ? 300 : ($variables->fw_session['refreshtime'] < 1 ? 1 : $variables->fw_session['refreshtime']) ) * 1000; ?>); // update every 5 minutes
	<?php } ?>
	fw_optimize_urls();

	$(window).keydown(function(e){
		if(e.which == 17) fw_ctrl_key_hold = true;
		else fw_ctrl_key_hold = false;
	});
	$(window).keyup(function(e){
		if(e.which == 17) fw_ctrl_key_hold = false;
	});

	$(window).bind("popstate", function(e) {
		var returnLocation = history.location || document.location;
		window.location = returnLocation;
		//fw_load_ajax(returnLocation);
		// because of this function on full page refresh content loads "twice" and all in module
		// events fires twice (including listeners)
	});
	$(window).resize(function(){
		fw_updateLayout();
	});
	$(window).on("scroll", function(){
		// if(!fw_scroll_active) fw_updateLayout();
		// clearTimeout(fw_scroll_active);
		// fw_scroll_active = setTimeout(function(){fw_scroll_active=false;},0);
	});

	/**
	 * Responsive - fix Listen to menu button
	 */
	$('.fw_module_list_button').on('click', function() {
		if (fw_collapsed_to_mobile) $('.col0').slideToggle();
	});

	/**
	 * Responsive fix -Close button is clicked in fw_module_head (when content edit form is opened)
	 */
	$('body').on('click', '.fw_module_head_close_btn', function() {
		$('.col2').hide();
		$('.col1').show();
		$(this).remove();
	});

	//update tinyscrollbar on slideDown/slideUp
	$(".content-toggler").on("click",function(){
		setTimeout(function(){$(window).resize();}, 500);
	});
	// module button click
	$(".fw_module_btn").data("loaded",false).on("click",function(e){
		if($(this).data("loaded")) {} else {
			e.preventDefault();
			var _this = $(this);
			fw_loading_start();
			$.ajax({
				cache: false,
				type: 'POST',
				dataType: 'json',
				data: { fwajax: 1 },
				url: $(_this).data("load"),
				success: function(data){
					$(_this).closest(".dropdown").find("ul.dropdown-menu").html(data.html);
					fw_optimize_urls();
					fw_loading_end();
					$(_this).data("loaded",true).trigger("click");
				}
			});
			return false;
		}
	});
	// calculate dropdown position
	$(document).on("shown.bs.dropdown.position-calculator", function(event, data) {
		var $item = $('.dropdown-menu', event.target);
		var target = data.relatedTarget;
		var itemWidth = $item[0].scrollWidth;

		// reset position
		$item.css({top:0, left:0});

		// calculate new position
		var calculator = new $.PositionCalculator({
			item    : $item,
			target  : target,
			itemAt  : "top left",
			itemOffset: { y:3, x:0, mirror:true },
			targetAt: "bottom left",
			flip    : "both"
		});
		var posResult = calculator.calculate();

		// set new position
		$item.css({
			top: posResult.moveBy.y + "px",
			left: posResult.moveBy.x + "px",
			width: itemWidth + "px"
		});
	});
	$("#fw_container").on("click",".fw_info_messages .item .close",function(e){
		e.preventDefault();
		$(this).closest(".item").remove();
	});
});

/*Scrollbar
---------------------------------*/
$(window).load(function(){
	fw_updateLayout();
	$(".tinyScrollbar").tinyscrollbar();
	setTimeout(function(){$(window).resize();}, 500);
});
$(document).ready(function() {
	var lastHeights = [];
	var elements = [];
	$(".fw_col").each(function(){
		var el = $(this).find(".overview");
		lastHeights.push(el.css('height'));
		elements.push(el)
	})
   	checkForChanges();

	function checkForChanges() {
		$.each(elements, function(key, value){
			if (value.css('height') != lastHeights[key]){
		        lastHeights[key] = value.css('height');
		        $(window).resize();
		    }
		})
	    setTimeout(checkForChanges, 500);
	}
	var out_popup_options_renew={
		follow: [true, true],
		fadeSpeed: 0,
		modalClose: false,
		escClose: false,
		closeClass:'b-close',
		onOpen: function(){
			$(this).addClass('opened');
			//$(this).find('.b-close').on('click', function(){out_popup.close();});
		},
		onClose: function(){
			if($(this).hasClass("close-page-reload")){
				fw_loading_start();
				window.location.reload();
			}
			$(this).removeClass('opened');
		}
	};
	$(".addNewGroup").off("click").on("click", function(){
		fw_loading_start();
		$.ajax({
			cache: false,
			type: 'POST',
			dataType: 'json',
			data: { fwajax: 1, fw_nocss: 1 },
			url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".(!$variables->fw_url_share?$_GET['caID']:$_COOKIE[$_GET['accountname'].'_caID'])."&module=GroupPage&folder=output&folderfile=output&inc_obj=ajax&inc_act=editGroup&cid=0"; ?>',
			success: function(json){
		        $('#popupeditboxcontent').html('');
		        $('#popupeditboxcontent').html(json.html);
		        out_popup = $('#popupeditbox').bPopup(out_popup_options_renew);
		        $("#popupeditbox:not(.opened)").remove();
				fw_loading_end();
			}
		});
		return false;
	})
	$(".addNewDepartment").off("click").on("click", function(){
		fw_loading_start();
		$.ajax({
			cache: false,
			type: 'POST',
			dataType: 'json',
			data: { fwajax: 1, fw_nocss: 1, department: 1 },
			url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".(!$variables->fw_url_share?$_GET['caID']:$_COOKIE[$_GET['accountname'].'_caID'])."&module=GroupPage&folder=output&folderfile=output&inc_obj=ajax&inc_act=editGroup&cid=0"; ?>',
			success: function(json){
		        $('#popupeditboxcontent').html('');
		        $('#popupeditboxcontent').html(json.html);
		        out_popup = $('#popupeditbox').bPopup(out_popup_options_renew);
		        $("#popupeditbox:not(.opened)").remove();
				fw_loading_end();
			}
		});
		return false;
	})
	$(".showAllDepartments").off("click").on("click", function(){
		$("#fw_menu .extradepartments").toggle();
	})
	$(".showAllGroups").off("click").on("click", function(){
		$("#fw_menu .extragroups").toggle();
	})
	$(".showDepartmentsNopage").off("click").on("click", function(){
		$("#fw_menu .nopagedepartments").toggle();
	})
	$(".giveYourselfAccess").off("click").on("click", function(){
		fw_loading_start();
		var group_id = $(this).data("group-id");
		var is_member = $(this).data("is_member");
		$.ajax({
			cache: false,
			type: 'POST',
			dataType: 'json',
			data: { fwajax: 1, fw_nocss: 1, is_member: is_member },
			url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".(!$variables->fw_url_share?$_GET['caID']:$_COOKIE[$_GET['accountname'].'_caID'])."&module=GroupPage&folder=output&folderfile=output&inc_obj=ajax&inc_act=giveyourselfAccess&cid="; ?>'+group_id,
			success: function(json){
		        $('#popupeditboxcontent').html('');
		        $('#popupeditboxcontent').html(json.html);
		        out_popup = $('#popupeditbox').bPopup(out_popup_options_renew);
		        $("#popupeditbox:not(.opened)").remove();
				fw_loading_end();
			}
		});
		return false;
	})

	$(".fw_menu_item .edit_department").off("click").on('click', function(e){
		fw_loading_start();
		var group_id = $(this).data("group-id");
		$.ajax({
			cache: false,
			type: 'POST',
			dataType: 'json',
			data: { fwajax: 1, fw_nocss: 1 },
			url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".(!$variables->fw_url_share?$_GET['caID']:$_COOKIE[$_GET['accountname'].'_caID'])."&module=GroupPage&folder=output&folderfile=output&inc_obj=ajax&inc_act=editGroup&cid="; ?>'+group_id,
			success: function(json){
		        $('#popupeditboxcontent').html('');
		        $('#popupeditboxcontent').html(json.html);
		        out_popup = $('#popupeditbox').bPopup(out_popup_options_renew);
		        $("#popupeditbox:not(.opened)").remove();
				fw_loading_end();
			}
		});
		return false;
	});
	$(".fw_menu_item .edit_department_members").off("click").on('click', function(e){
		fw_loading_start();
		var group_id = $(this).data("group-id");
		$.ajax({
			cache: false,
			type: 'POST',
			dataType: 'json',
			data: { fwajax: 1, fw_nocss: 1, department: 1, groupId: group_id },
			url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".(!$variables->fw_url_share?$_GET['caID']:$_COOKIE[$_GET['accountname'].'_caID'])."&module=People&folder=output_groups&folderfile=output&inc_obj=ajax&inc_act=editMembers&cid="; ?>'+group_id,
			success: function(json){
		        $('#popupeditboxcontent').html('');
		        $('#popupeditboxcontent').html(json.html);
		        out_popup = $('#popupeditbox').bPopup(out_popup_options_renew);
		        $("#popupeditbox:not(.opened)").remove();
				fw_loading_end();
			}
		});
		return false;
	});
	$("#fw_account .fw_menu_hamburger_label").off("click").on("click", function(){
		$("#fw_menu").slideToggle();
	})
	// $("#fw_account .fw_menu_collapse").off("click").on("click", function(){
	// 	$("#fw_account.alternative").addClass("menu_collapsed");
	// 	$(".fw_menu_hamburger").append($("#fw_menu"));
	// 	$(window).resize();
	// })
	// $("#fw_account .fw_menu_expand").off("click").on("click", function(){
	// 	$("#fw_account.alternative").removeClass("menu_collapsed");
	// 	$("#fw_account.alternative .fw_col.col0 ").css("position", "relative");
	// 	$("#fw_account.alternative .fw_col.col0").append($("#fw_menu"));
	// 	$(window).resize();
	// })

});
/*Scrollbar End
---------------------------------*/


/*
---------------------------------------------------

FW update layout function, for:

- sizing colums horizontally
- adding body class mobile
- sizing columns vertically
---------------------------------------------------
*/


function fw_updateLayout() {

	<?php if($variables->fw_settings_basisconfig['user_alternative_design']){ ?>
		//don't change visiblity if chat is active
		if(!$("#fw_chat").is(":visible")){
			$("#fw_account").css({'visibility': 'hidden'});
		}
		<?php
		if($variables->fw_settings_basisconfig['menu_min_width'] != ""){
			?>
			window.outputModuleViewportSettings.minWidthDesktopMenu = <?php echo $variables->fw_settings_basisconfig['menu_min_width']?>;
			<?php
		}
		if($variables->fw_settings_basisconfig['menu_max_width'] != ""){
			?>
			window.outputModuleViewportSettings.maxWidthDesktopMenu = <?php echo $variables->fw_settings_basisconfig['menu_max_width']?>;
			<?php
		}
		if($variables->fw_settings_basisconfig['content_min_width'] != ""){
			?>
			$("#fw_account").css({"min-width": '<?php echo $variables->fw_settings_basisconfig['content_min_width']?>px'});
			<?php
		}
		if($variables->fw_settings_basisconfig['content_max_width'] != ""){
			?>
			$("#fw_account").css({"max-width": '<?php echo $variables->fw_settings_basisconfig['content_max_width']?>px'});
			<?php
		}
		?>
	<?php } ?>
	var ini_h = $(window).height() - $('#fw_getynet').height() + 1;
	$('#fw_loading div').css('margin-top',(ini_h-40)/2);

	// NOTE: disabled because it was conflicting with mobile version. Not sure
	// about other side effects
	// $('.fw_col').removeAttr('style');
	$('.fw_col .modulecontent').removeAttr('style');
	$('.fw_module_head').removeAttr('style');

	<?php if($variables->fw_settings_basisconfig['user_alternative_design']){ ?>
	var windowWidth = $(window).width();
	var containerWidth = $("#fw_account").width(); // cache it for better performance
	if($(".fw_contact_list").is(":visible")){
		var windowDifference = (windowWidth - containerWidth)/2;
		if(windowDifference > $(".fw_contact_list").width()){
			containerWidth += $(".fw_contact_list").width();
		} else {
			containerWidth += $(".fw_contact_list").width()-($(".fw_contact_list").width()-windowDifference)-15;
		}
	}
	// var windowWidth = $(window).width();
	<?php } else { ?>
	var windowWidth = $(window).width(); // cache it for better performance
	var containerWidth = $(window).width(); // cache it for better performance
	<?php } ?>

	/*
	---------------------------------------------------

	Column default sizes (if data-minwidth and
	data-maxwidth is not set these will be used)

	---------------------------------------------------
	*/

	<?php if($variables->fw_settings_basisconfig['user_alternative_design']){ ?>
		var columnSizes = {

			// Module list
			'.col0': {
				min: 200,
				max: 240,
			},

			// Content list
			'.col1': {
				min: 230,
				max: 300
			},

			// Module content - automatically copied to .col1 if .col2 is not used
			// and .col1 is module content column
			'.col2': {
				min: 500,
				max: 700
			},

			// Contact list
			'.fw_contact_list': {
				min: 70,
				max: 250
			},

			// Placeholder for totals

			'total': {
				min: 0,
				max: 0,
				used: 0
			}

		};
	<?php } else { ?>
	var columnSizes = {

		// Module list
		'.col0': {
			min: 200,
			max: 240,
		},

		// Content list
		'.col1': {
			min: 230,
			max: 300
		},

		// Module content - automatically copied to .col1 if .col2 is not used
		// and .col1 is module content column
		'.col2': {
			min: 400,
			max: 700
		},

		// Contact list
		'.fw_contact_list': {
			min: 70,
			max: 250
		},

		// Placeholder for totals

		'total': {
			min: 0,
			max: 0,
			used: 0
		}

	};
	<?php } ?>

	/*
	---------------------------------------------------

	Available columns

	In sizing sequence (strating from small to large
	screen sizes)

	---------------------------------------------------
	*/

	var columns = [];

	// 3 columns + contact list
	if ($('.fw_contact_list').length && !$('.col1.end').length && !$('.fw_contact_list_hidden').length) {
		columns = ['.col2', '.col1', '.fw_contact_list', '.col0'];
	}
	// 2 columns + contact list
	else if ($('.fw_contact_list').length && $('.col1.end').length && !$('.fw_contact_list_hidden').length) {
		columns = ['.col1', '.fw_contact_list', '.col0'];
		columnSizes['.col1'] = columnSizes['.col2'];
	}
	// 3 columns
	else if (!$('.col1.end').length) {
		columns = ['.col2', '.col1', '.col0'];
	}
	// 2 columns
	else {
		columns = ['.col1', '.col0'];
		columnSizes['.col1'] = columnSizes['.col2'];
	}


	// Delete column sizes that will not be used (are not in columns array)
	for (var item in columnSizes) {
		if (columns.indexOf(item) == -1 && item != 'total') {
			delete columnSizes[item];
		}
	}

	/*
	---------------------------------------------------

	Hardcoded output module values getter

	---------------------------------------------------
	*/

	if (window.outputModuleViewportSettings) {
		<?php if($variables->fw_settings_basisconfig['user_alternative_design']){ ?>
			if (window.outputModuleViewportSettings.minWidthDesktopMenu) {
				columnSizes['.col0'].min = window.outputModuleViewportSettings.minWidthDesktopMenu;
			}
			if (window.outputModuleViewportSettings.maxWidthDesktopMenu) {
				columnSizes['.col0'].max = window.outputModuleViewportSettings.maxWidthDesktopMenu;
			}
		<?php } ?>
		if (window.outputModuleViewportSettings.minWidthDesktop) {
			columnSizes['.col1'].min = window.outputModuleViewportSettings.minWidthDesktop;
		}
		if (window.outputModuleViewportSettings.maxWidthDesktop) {
			columnSizes['.col1'].max = window.outputModuleViewportSettings.maxWidthDesktop;
		}
		// minWidthOnMobile implemented further down
	}

	/*
	---------------------------------------------------

	Setting min and max width fallbacks if needed

	---------------------------------------------------
	*/

	for (var i = 0; i < columns.length; i++) {

		// min width
		if($(columns[i]).attr('data-minwidth')) {
			columnSizes[columns[i]].min = parseInt($(columns[i]).attr('data-minwidth'));
		}

		// max width
		if($(columns[i]).attr('data-maxwidth')) {
			columnSizes[columns[i]].max = parseInt($(columns[i]).attr('data-maxwidth'));
		}

		// Calculating
		columnSizes.total.min += parseInt(columnSizes[columns[i]].min);
		columnSizes.total.max += parseInt(columnSizes[columns[i]].max);

	}
	<?php
	if($variables->fw_settings_basisconfig['mobile_breakpoint'] != ""){
		?>
		columnSizes.total.min = <?php echo $variables->fw_settings_basisconfig['mobile_breakpoint']?>;
		<?php
	}
	?>
	/*
	---------------------------------------------------

	Creating mini-breakpoints

	---------------------------------------------------
	*/

	var breakpoints = [];
	var currentBreakpoint = columnSizes.total.min;
	var activeBreakpoint = 0;
	breakpoints.push(currentBreakpoint);

	for (var i = 0; i < columns.length; i++) {

		currentBreakpoint += parseInt(columnSizes[columns[i]].max) - parseInt(columnSizes[columns[i]].min);
		breakpoints.push(currentBreakpoint);

		if (containerWidth > currentBreakpoint) {
			activeBreakpoint = i + 1;
		}

	}

	/*
	---------------------------------------------------

	Calculata and add final width values to object

	---------------------------------------------------
	*/

	for (var i = 0; i < columns.length; i++) {

		if (activeBreakpoint == i) continue; // let not do anything for active column yet

		// For those who must be small yet
		if (activeBreakpoint < i) {
			columnSizes[columns[i]].current = columnSizes[columns[i]].min;
			columnSizes.total.used += columnSizes[columns[i]].current;
		}

		// For those who must be max width already
		if (activeBreakpoint > i) {
			columnSizes[columns[i]].current = columnSizes[columns[i]].max;
			columnSizes.total.used += columnSizes[columns[i]].current;
		}

	}

	// Sizing "active" column - the that now is fluid
	if (activeBreakpoint < columns.length) {
		columnSizes.total.available = containerWidth - columnSizes.total.used;
		columnSizes[columns[activeBreakpoint]].current = columnSizes.total.available;

		// Exception for contact list, it's not fluid, either max or min
		if(columns[activeBreakpoint] == '.fw_contact_list') {
			if (columnSizes.total.available >= columns[activeBreakpoint].max)
				columnSizes[columns[activeBreakpoint]].current = columnSizes[columns[activeBreakpoint]].max;
			else {
				columnSizes[columns[activeBreakpoint]].unused = columnSizes[columns[activeBreakpoint]].current - columnSizes[columns[activeBreakpoint]].min;
				columnSizes[columns[activeBreakpoint]].current = columnSizes[columns[activeBreakpoint]].min;
			}
		}
	}

	// Set class for expanded collapsed contactlist
	if(columnSizes['.fw_contact_list']) {
		if (columnSizes['.fw_contact_list'].current == columnSizes['.fw_contact_list'].min) {

			if(!$('#fw_chat').is(':visible'))
				$('.fw_contact_list').removeClass('expanded collapsed').addClass('collapsed');

			// Ad space saved to content column wraper
			if($('.col1.end').length && columnSizes['.fw_contact_list'].unused > 0 ) columnSizes['.col1'].wrapper = columnSizes['.col1'].current + columnSizes['.fw_contact_list'].unused;
			if($('.col2.end').length && columnSizes['.fw_contact_list'].unused > 0 ) columnSizes['.col2'].wrapper = columnSizes['.col2'].current + columnSizes['.fw_contact_list'].unused;

		}
		else {
			if(!$('#fw_chat').is(':visible'))
				$('.fw_contact_list').removeClass('expanded collapsed').addClass('expanded');
		}
	}

	// QUICK FIX FOR MODULE LIST Column sizing issue on Firefox Mac - asked by Frode, tweak and test later
	// Currently it shouldn't be in central lib fw version!
	// columnSizes['.col0'].current = columnSizes['.col0'].current - 10;

	/*
	---------------------------------------------------

	Set width values

	---------------------------------------------------
	*/

	// Fix to stretch module content container further
	if (containerWidth >= columnSizes.total.max) {
		if($('.col1.end').length) columnSizes['.col1'].wrapper = columnSizes['.col1'].current + containerWidth - columnSizes.total.max;
		if($('.col2.end').length) columnSizes['.col2'].wrapper = columnSizes['.col2'].current + containerWidth - columnSizes.total.max;
	}

	// Fix for some browsers (Firefox Mac Retina)
	// Just remove one pix from col0 size
	columnSizes['.col0'].current -= 1;

	// Setting
	if (windowWidth < columnSizes.total.min) {
		$('body').removeClass('desktop').addClass('mobile');
		fw_collapsed_to_mobile = true;
		for (var item in columnSizes) {
			if (item != 'total') {
				$(item).css('width', '100%');
				$(item).css('marginLeft', '0px');
			}

			// Mobile wrapper, to make unresponsive modules scrollable horizontally
			if (window.outputModuleViewportSettings.minWidthMobile) {
				if (item == '.col1') {
					if (!$(item + ' .overview .mobileViewport').length) {
						$(item + ' .overview').wrapInner('<div class="mobileViewport"><div class="mobileViewportInner"></div></div>');
					}
					$('.mobileViewport').attr('style','');
					$('.mobileViewport').height($(item + ' .viewport').height() - 30);
					$('.mobileViewportInner').css('min-width', window.outputModuleViewportSettings.minWidthMobile + 'px');
				}
			}
			else {
				$('.mobileViewportInner').attr('style','');
			}

		}
	}

	else {
		$('body').removeClass('mobile').addClass('desktop');
		$('.mobileViewportInner').contents().unwrap();
		$('.mobileViewport').contents().unwrap();
		fw_collapsed_to_mobile = false;
		for (var item in columnSizes) {
			if (item != 'total') {
				if (columnSizes[item].wrapper) {
					if (!$(item).has('.forceFullWidthModuleContent').length)
						$(item + ' .modulecontent').css('width', columnSizes[item].current + 'px');
					<?php if($variables->fw_settings_basisconfig['user_alternative_design']){ ?>
						if(item == '.col1'){
							if($("#fw_account").hasClass("menu_collapsed")){
								$(item).css('width', '100%');
								$(item).css('marginLeft', '0px');
							} else {
								$(item).css('width', (columnSizes[item].wrapper-20) + 'px');
								$(item).css('marginLeft', '20px');
							}
						} else {
							$(item).css('width', (columnSizes[item].wrapper) + 'px');
							$(item).css('marginLeft', '0px');
						}
					<?php } else { ?>
						$(item).css('width', columnSizes[item].wrapper + 'px');
						$(item).css('marginLeft', '0px');
					<?php } ?>
				}
				else {
					<?php if($variables->fw_settings_basisconfig['user_alternative_design']){ ?>
						if(item == '.col1'){
							$(item).css('width', (columnSizes[item].current-20) + 'px');
							$(item).css('marginLeft', '20px');
						} else {
							$(item).css('width', (columnSizes[item].current) + 'px');
							$(item).css('marginLeft', '0px');
						}
					<?php } else { ?>
						$(item).css('width', columnSizes[item].current + 'px');
					<?php } ?>
				}
			}
		}
	}

	// Responsive - fix module head width
	if (!fw_collapsed_to_mobile) {
		// IMPORTANTANT!
		// There is fix for some browsers (Firefox Mac Retina)
		// Removing 1px from total width
		<?php if($variables->fw_settings_basisconfig['user_alternative_design']){ ?>
			if($('#fw_account').hasClass("menu_collapsed")) {
				$('#fw_account .fw_module_head').css('width', containerWidth+"px");
				$('#fw_account .fw_module_head').css('marginLeft', '0px');
				$(".fw_menu_hamburger").css("left", $("#fw_account").offset().left);
				if($(".fw_menu_hamburger #fw_menu").length == 0){
					$(".fw_menu_hamburger").append($(".fw_col.col0 #fw_menu").hide());
				}
			} else {
				if($(".fw_col.col0 #fw_menu").length == 0){
					$(".fw_col.col0").append($(".fw_menu_hamburger #fw_menu"));
					$(".fw_col.col0").show();
					$(".fw_col.col0 #fw_menu").removeAttr("style");
				}
				var	headModuleWidth = (containerWidth - $('#fw_account .fw_col.col0').width() - 1 - 20 - $("#fw_account .fw_contact_list:not(.fw_contact_list_hidden)").width());

				$('#fw_account .fw_module_head').css('width',  headModuleWidth + 'px');
				$('#fw_account .fw_module_head').css('marginLeft', '20px');
			}
		<?php } else {
			?>
			$('#fw_account .fw_module_head').css('width', (containerWidth - $('#fw_account .fw_col.col0').width() - 1 - $("#fw_account .fw_contact_list:not(.fw_contact_list_hidden)").width()) + 'px');
			$('#fw_account .fw_module_head').css('marginLeft', '0px');
		<?php } ?>
	}
	else {
		$('#fw_account .fw_module_head').css('width', '100%');
		$('#fw_account .fw_module_head').css('marginLeft', '0px');

		if($(".fw_col.col0 #fw_menu").length == 0){
			$(".fw_col.col0").append($(".fw_menu_hamburger #fw_menu").hide());
			$(".fw_col.col0").show();
			$(".fw_col.col0 #fw_menu").removeAttr("style");
		}
	}

	// Responsive fix - what content load on FIRST load if on mobile?
	if (fw_collapsed_to_mobile && !fw_collapsed_to_mobile_setup_done) {
		// Content ID
		var hasId = window.location.href.indexOf("&ID=");

		// If no particular content is requested, let's force content list
		if (hasId < 0) {
			$('.col1').show();
			fw_collapsed_to_mobile_setup_done = true;
		} else if(!$('.fw_col.col1').hasClass('end')) {
			$('.fw_col.col1').hide();
			if($('.fw_module_head').length) {
				$('.fw_module_head').append('<span class="fw_module_head_close_btn"><span class="glyphicon glyphicon-remove"></span></span>');
			}
			fw_collapsed_to_mobile_setup_done = true;
		}
		if ($('.fw_col.col1').hasClass('end')) {
			$('.col1').show();
			fw_collapsed_to_mobile_setup_done = true;
		}
	}

	// Final tweak if chat is opened (with mousenter)
	if($('#fw_chat').is(':visible'))
		$('.fw_contact_list').css('width', '250px');

	/*
	---------------------------------------------------

	Height updates

	---------------------------------------------------
	*/
	var containerHeight = $("#fw_container").height(),
		getynetHeight = $("#fw_getynet").height(),
		accountHeight = containerHeight - getynetHeight;
	<?php if($variables->fw_settings_basisconfig['user_alternative_design']){ ?>
		$("#fw_account").css({"margin-top": $("#fw_getynet").height()+"px", "min-height": (viewport().height-$("#fw_getynet").height()-20)+"px"});
		$("#fw_chat").css({"margin-top": 0, "min-height": (viewport().height-$("#fw_getynet").height())+"px"});


		$("body.alternative.seperateMenuScroll .fw_col.col0").css({
			"max-height": viewport().height-$("#fw_getynet").height()-20,
			"overflow": "auto"
		})

	<?php } else { ?>
		$("#fw_account").height(accountHeight);
	<?php } ?>

	// fix position for button panel
	if($('#sys-edit-button-panel').length)
	{
		var $column = $('#sys-edit-button-panel').closest('.fw_col'),
		show_offset = $('#fw_account .fw_col.col0').width();
		if($column.is('.col2')) show_offset = show_offset + $('#fw_account .fw_col.col1').width();

		$('#sys-edit-button-panel').css('left', show_offset);
		$('#sys-edit-button-panel-trigger').data('show',show_offset);
		if($('#sys-edit-button-panel-trigger').is('.open'))
		{} else {
			$('#sys-edit-button-panel').css({width:1,'padding-left':0,'padding-right':0}).find('input').hide();
		}
	}
	<?php if($variables->fw_settings_basisconfig['user_alternative_design']){ ?>
		//update scroll`
		setTimeout(function(){scrollLayout();}, 100);
		//don't change visiblity if chat is active
		if(!$("#fw_chat").is(":visible")){
			$("#fw_account").css({'visibility': 'visible'});
		}
	<?php } else { ?>

	<?php } ?>
}
function fw_doSessionUpdate() {
	<?php if(!$b_fw_getynet_app) { ?>
	$.ajax({
		cache: false,
		url: "<?php echo $variables->languageDir;?>account_fw/menu/ajax_updatesession.php",
		data: { companyID: '<?php echo $_GET['companyID'];?>', caID: '<?php echo (!$variables->fw_url_share?$_GET['caID']:$_COOKIE[$_GET['accountname'].'_caID']);?>' }
	});
	setTimeout(fw_doSessionUpdate, 300000); // update every 5 minutes
	//update tinyscrollbars
	$(window).resize();
	<?php } ?>
}

function fw_optimize_urls()
{
	<?php if(!$b_fw_getynet_app) { ?>
	$(document).off("click","a").on("click", "a", function(e) {
		if(!fw_click_instance && !$(this).is(".script") && $(this).is("[href]") && $(this).attr("href")[0] != '#')
		{
			fw_click_instance = true;
			var _this = $(this);
			var _href = _this.attr('href');
			var _ajaxurl = '';
			var _target = '';
			var _loaded = false;


			if(_href.indexOf('open_module(') >= 0)
			{

				_ajaxurl = _href.substring(_href.indexOf('open_module(')+13, _href.length);
				_ajaxurl = '<?php echo $_SERVER['PHP_SELF'];?>?' + _ajaxurl.substring(0,_ajaxurl.indexOf('\''));
			}
			if($(this).is('.abort_bg')) fwAbortXhrPool();
			if(_this.is('.optimize'))
			{
				/**
				 * Responsive fixes
				 */
				if (fw_collapsed_to_mobile) {
					// Module menu is clicked
					if (_this.parent().hasClass('fw_menu_item')) {
						// Close module menu
						$('.col0').slideUp();
						fw_collapsed_to_mobile_load_col2 = false;
					}
					// Content list menu is clicked
					if (_this.closest('.listItems').length > 0) {
						if (!$('.fw_col.col1').hasClass('end'))
							fw_collapsed_to_mobile_load_col2 = true;
					}
					// Module head menu
					if (_this.closest('.fw_module_head').length > 0) {
						fw_collapsed_to_mobile_load_col2 = false;
					}
					// Module actions (add, edits fields, etc)
					if (_this.closest('.module_list').length > 0) {
						if (!$('.fw_col.col1').hasClass('end'))
							fw_collapsed_to_mobile_load_col2 = true;
					}

					// Add close butotn on fw_module_head
					function fw_module_head_add_close_btn() {
						if($('.fw_module_head').length) {
							$('.fw_module_head').append('<span class="fw_module_head_close_btn"><span class="glyphicon glyphicon-remove"></span></span>');
						}
					}
				}

				_ajaxurl = _href;
			}
			if(_this.data('target'))
			{
				_target = _this.data('target');
				//_ajaxurl = $(_target).attr('data-url');
				if($(_target).is('.loaded')) _loaded = true;
				if($(_target).is('.refresh')) _loaded = false;
			} else {
				if(_this.is('.optimize')){
					//Reset popup
					$("#popupeditbox #popupeditboxcontent").html("");
				}
			}

			if(_ajaxurl != "")
			{
				e.preventDefault();
				if(fw_ctrl_key_hold)
				{
					fw_ctrl_key_hold = fw_click_instance = false;
					var win = window.open(_ajaxurl, '_blank');
					win.focus();
				} else {
					if(_loaded)
					{
						$(_target).slideToggle(500,function(){fw_click_instance = false;});
					} else {
						if(fw_check_changes()) return false;

						var _activate = _this.closest('.activate');
						if(_activate && !_activate.is('.active'))
						{
							$('.' + _activate.data('group') + '.active').removeClass('active');
							_this.closest('.activate').addClass('active');
						}
						if(_target == "") history.pushState({index: $(_this).attr('id')}, document.title, _ajaxurl);
						fw_load_ajax(_ajaxurl, _target);
					}
				}
			} else {
				fw_click_instance = false;
				if(fw_ctrl_key_hold)
				{
					e.preventDefault();
					fw_ctrl_key_hold = false;
					var win = window.open(_href, '_blank');
					win.focus();
				} else {
					if(fw_check_changes()) return false;
				}
			}
		}
	});
	<?php } ?>
	$('.module_list .list_buttons, .subcontent .list_buttons').hover(function() {
		$(this).find('.buttons').stop().hide().fadeIn();
	}, function() {
		$(this).find('.buttons').fadeOut();
	});

	$(document).on("click", ".module_list .list_buttons a, .subcontent .list_buttons a", function(){ $(this).closest('.buttons').hide(); });

	$(document).off("click","a.delete-confirm-btn").on("click", "a.delete-confirm-btn", function(e){
		e.preventDefault();
		if(!fw_changes_made && !fw_click_instance)
		{
			fw_click_instance = true;
			var $_this = $(this);
			var s_msg_sufix = "";
			if($(this).data("name")) s_msg_sufix = ": " + $(this).data("name");
			bootbox.confirm({
				message:"<?php echo $formText_DeleteItem_framework;?>" + s_msg_sufix + "?",
				buttons:{confirm:{label:"<?php echo $formText_Yes_framework;?>"},cancel:{label:"<?php echo $formText_No_framework;?>"}},
				callback: function(result){
					if(result)
					{
						window.location = $_this.attr("href");
					}
					fw_click_instance = false;
				}
			});
		}
	});
}

function fw_check_changes()
{
	/*if(fw_changes_made && !confirm("Changes were made Are you sure you want to continue without saving?"))
	{
		fw_click_instance = false;
		return true;
	}*/
	return false;
}
var javascript_load = $("#javascript_load");
function fw_load_ajax(_url, _target, _push_state)
{
	fw_history_pushed = true;
	window.outputModuleViewportSettings = {};
	fw_loading_start();
	if(_push_state !== undefined && _push_state == true)
	{
		history.pushState({index: Math.random()}, document.title, _url);
	}
	$.ajax({
		url: _url,
		cache: false,
		type: 'POST',
		dataType: 'json',
		data: { fwajax : 1 },
		success: function (data) {
			$("#fw_account").removeClass("menu_collapsed");
			if(_target !== undefined && _target != "")
			{
				if($(_target).data('replace') == 1)
				{
					$(_target).replaceWith(data.html);
				} else {
					$(_target).addClass('loaded').html(data.html);
				}

			} else {
				fw_info_message_empty();
				if(data.module !== undefined) {
					$('.fw_menu_item.active').removeClass('active');
					$('.fw_menu_item.' + data.module).addClass('active');
					if(!$('.fw_menu_item.active').parents('.collapse').is('.in'))
					{
						$($('.fw_menu_item.active').parents('.collapse').data('trigger')).trigger('click');
					}
					$('#fw_account .fw_module_head').empty();
				}
				if(data.module_head !== undefined) {
					$('#fw_account .fw_module_head').html(data.module_head);
				}
				if(data.width !== undefined) {
					$(data.width.split(':')).each(function(index, value){
						if(value!="") $('.fw_col.col' + (index+1)).attr('data-width', value);
						else $('.fw_col.col' + (index+1)).removeAttr('data-width');
					});
				}
				if(data.col1MaxWidth !== "null") {
					$('.fw_col.col1').attr("data-maxwidth", data.col1MaxWidth);
				}else{
					$('.fw_col.col1').removeAttr("data-maxwidth");
				}
				if(data.col1MinWidth !== "null"){
					$('.fw_col.col1').attr("data-minwidth", data.col1MinWidth);
				}else{
					$('.fw_col.col1').removeAttr("data-minwidth");
				}
				if(data.col2MaxWidth !== "null"){
					$('.fw_col.col2').attr("data-maxwidth", data.col2MaxWidth);
				}else{
					$('.fw_col.col2').removeAttr("data-maxwidth");
				}
				if(data.col2MinWidth !== "null"){
					$('.fw_col.col2').attr("data-minwidth", data.col2MinWidth);
				}else{
					$('.fw_col.col2').removeAttr("data-minwidth");
				}
				fw_fix_columns(data.column, data.columns);
				$('.fw_col.col' + data.column + ' .data').html(data.html);
			}

			if(data.error !== undefined)
			{
				$.each(data.error, function(index, value){
					var _type = Array("error");
					if(index.length > 0 && index.indexOf("_") > 0) _type = index.split("_");
					fw_info_message_add(_type[0], value);
				});
				fw_info_message_show();
			}
			fw_optimize_urls();
			if(data.javascript !== undefined) {
				//console.log(data.javascript);
				$.globalEval(data.javascript);
			}
			if(typeof apply_content_load === 'function') apply_content_load();
			fw_loading_end();
			fw_click_instance = fw_changes_made = false;

			<?php if(!$variables->fw_settings_basisconfig['user_alternative_design']){ ?>
				//update tinyscrollbar
				$(window).resize();
				setTimeout(function(){$(window).resize();}, 100);
			<?php } ?>
			//update tinyscrollbar on slideDown/slideUp
			$(".content-toggler").click(function(){
				setTimeout(function(){$(window).resize();}, 100);
			});

			// Responsive fixes - must be updated - only applies to MOBILE!!
			if (fw_collapsed_to_mobile) {
				if (!fw_collapsed_to_mobile_load_col2) {
					$('.col1').removeClass('hide').show();
				}
				// Add or remove close button for content, if content was loaded,
				// add button, else remove
				if(fw_collapsed_to_mobile_load_col2) {
					$('.fw_col.col1').hide();
					$('.fw_module_head').append('<span class="fw_module_head_close_btn"><span class="glyphicon glyphicon-remove"></span></span>');
				}
				else {
					$('.fw_col.col1').show();
					$('.fw_module_head').find('.fw_module_head_close_btn').remove();
				}
			}

			$("#fw_account.menu_collapsed .col0").hide();

		}
	}).fail(function() {
		fw_info_message_add("error", "<?php echo $formText_ErrorOccuredPleaseContactSupport_framework;?>", true, true);
		fw_loading_end();
		fw_click_instance = fw_changes_made = false;
	});
}
function fw_fix_columns(column, columns)
{
	var _class = '';
	if(column == columns) _class = 'end ';
	$('.fw_col.col0').nextAll('.fw_col').removeAttr('style').each(function(idx) {
		if(idx+1 < column) $(this).removeClass('end hide');
		if(idx+1 == column) $(this).removeClass('end hide').addClass(_class);
		if(idx+1 > column) $(this).removeClass('end').addClass('hide');
	});

	for(var i = column+1; i < 5; i++) {
		$('.fw_col.col' + i + ' .data').empty();
	}
	scrollLayout();
}
function fw_loading_start()
{
	$('#fw_loading').show();
}
function fw_loading_end()
{
	fw_updateLayout();
	window.onload = fw_updateLayout;
	$('#fw_loading').hide();
}
function fw_info_message_empty()
{
	$('.fw_info_messages').slideUp(100).empty();
}
function fw_info_message_add(type, message, _show, _empty)
{
	if(_empty !== undefined) fw_info_message_empty();
	$(".fw_info_messages").append('<div class="item ui-corner-all ' + type + '" role="alert"><button type="button" class="close"><span>&times;</span></button>' + message + '</div>');
	if(_show !== undefined) fw_info_message_show();
}
function fw_info_message_show()
{
	if($('.fw_info_messages').length) $('.fw_info_messages').slideDown(500);
}

function createRequestObject()
{
	var request_o; //declare the variable to hold the object.
	var browser = navigator.appName; //find the browser name
	if(browser == "Microsoft Internet Explorer")
	{
		/* Create the object using MSIE's method */
		request_o = new ActiveXObject("Microsoft.XMLHTTP");
	} else {
		/* Create the object using other browser's method */
		request_o = new XMLHttpRequest();
	}
	return request_o; //return the object
}
http = createRequestObject();

function str_replace(search, replace, subject)
{
	var result = "";
	var  oldi = 0;
	for (i = subject.indexOf (search); i > -1; i = subject.indexOf (search, i))
	{
		result += subject.substring (oldi, i);
		result += replace;
		i += search.length;
		oldi = i;
	}
	return result + subject.substring (oldi, subject.length);
}

function viewport() {
	var e = window, a = 'inner';
	if (!('innerWidth' in window )) {
		a = 'client';
		e = document.documentElement || document.body;
	}
	return { width : e[ a+'Width' ] , height : e[ a+'Height' ] };
}
$(window).scroll(function (){
	scrollLayout();
});
$(window).load(function(){
	$(window).scroll();
})

function scrollLayout(){
	if($("body").hasClass('desktop') && $("body").hasClass('alternative') && $("#fw_account").is(":visible")){
		if(!$("#fw_account").hasClass("menu_collapsed")) {
			var leftColumn = $(".fw_col.col0");
			var middleColumn = $(".fw_col.col1");
			var rightColumn = $(".fw_col.col2");
			//check if 3rd column exists if not look up column inside middle column
			if(!rightColumn.is(":visible")){
				if(middleColumn.find(".fw_col_right").length > 0 && middleColumn.find(".fw_col_left").length > 0) {
					var middleColumnWrapper = middleColumn;
					var rightColumn = middleColumnWrapper.find(".fw_col_right");
					var middleColumn = middleColumnWrapper.find(".fw_col_left");
					rightColumn.css({
						"position": "relative",
						"top": "0px",
						"left": "0px",
						"width":''
					});
				}
			}
			var leftHeight = leftColumn.innerHeight();
			var leftWidth = leftColumn.width();
			var middleHeight = middleColumn.innerHeight();
			if(middleColumnWrapper != undefined){
				var middleHeight = middleColumnWrapper.innerHeight();
			}
			var rightHeight = rightColumn.innerHeight();

			var rightWidth = rightColumn.width();
			var scrollTop = $(window).scrollTop();
			var wh = viewport().height;
			var leftTop = scrollTop + wh;
			var leftLeft = $("#fw_account").offset().left;


			var rightTop = scrollTop + wh;
			if(middleColumnWrapper != undefined){
				var rightLeft = leftLeft + middleColumnWrapper.outerWidth(true) - rightWidth + leftWidth;
			} else {
				var rightLeft = leftLeft + middleColumn.outerWidth(true) + leftWidth ;
			}

			var rightOffsetTop = middleColumn.offset().top;

			if(middleColumnWrapper != undefined){
				var leftOffsetTop = middleColumnWrapper.offset().top;
			} else {
				var leftOffsetTop = rightOffsetTop - $(".fw_module_head_wrapper").height();
			}

			var middleContainerLeft = leftWidth;

			var resetLeft = false;
			var resetRight = false;
			if(scrollTop > 0) {
				if(scrollTop + $("#fw_getynet").height() > leftOffsetTop) {
					if((scrollTop + wh - leftOffsetTop) > leftHeight) {
						var leftTopPx = leftHeight - wh + 20;
						if(leftTopPx < 0){
							leftTopPx = $("#fw_account").offset().top;
						} else {
							leftTopPx = "-"+leftTopPx;
						}
						leftColumn.css({
							"position": "fixed",
							"top": 	leftTopPx +"px",
							"left": leftLeft+"px"
						});
						if(middleColumnWrapper != undefined) {
							middleColumnWrapper.css({
								"position": "relative",
								"left": middleContainerLeft+"px"
							})
						} else {
							middleColumn.css({
								"position": "relative",
								"left": middleContainerLeft+"px"
							})
						}
					} else {
						resetLeft = true;
					}
				} else {
					resetLeft = true;
				}
				if(resetLeft){
					leftColumn.css({
						"position": "relative",
						"top": "0px",
						"left": "0px"
					});
					if(middleColumnWrapper != undefined) {
						middleColumnWrapper.css({
							"left": "0px"
						})
					} else {
						middleColumn.css({
							"left":"0px"
						})
					}
				}

				if(scrollTop + $("#fw_getynet").height() > rightOffsetTop) {
					if((scrollTop + wh - rightOffsetTop - 20) > (rightHeight)) {
						var rightTopPx = rightHeight - wh;

						if(rightTopPx < 0 ){
							rightTopPx = $("#fw_getynet").height();
						} else {
							rightTopPx = "-"+rightTopPx;
						}
						rightColumn.css({
							"position": "fixed",
							"top": rightTopPx +"px",
							"left": rightLeft+"px",
							"margin-left": "0px"
						});
						if(middleColumnWrapper != undefined) {
							rightColumn.css({
								"width" :rightWidth+"px",
							});
						}
					} else {
						resetRight = true;
					}
				} else {
					resetRight = true;
				}

				// var bottomScrolled = scrollTop + wh - 100;
				// if(middleHeight < bottomScrolled) {
				// 	// loadMoreNews();
				// } else {
				//
				// }

				//put height on the container
				var pageHeight = leftHeight;
				if(pageHeight < $(".fw_col.col2").innerHeight()){
					pageHeight = $(".fw_col.col2").innerHeight();
				}
				if(middleColumnWrapper != undefined){
					if(middleColumn.innerHeight() < rightColumn.innerHeight()){
						middleColumnWrapper.height(rightColumn.height() + rightOffsetTop - middleColumnWrapper.offset().top);
					} else {
						middleColumnWrapper.height("auto");
					}
				}
				if($(".fw_col.col1").innerHeight() < $(".fw_col.col0").innerHeight()) {
					$(".fw_col.col1").css({"min-height": $(".fw_col.col0").innerHeight() +"px"});
				}
				// if(middleHeight > pageHeight) {
					$("#fw_account").height("auto");
				// } else {
				// 	$("#fw_account").height(pageHeight);
				// }
			} else {
				$(".fw_col").css({
					"position": "relative",
					"top": "0px",
					"left": "0px"
				})
			}
		} else {
			$(".fw_col.col0").css({
				"position": "absolute",
				"top": "60px",
				"left": "0px",
				"z-index": "15"
			})
			$(".fw_col.col1").css({
				"position": "relative",
				"top": "0px",
				"left": "0px"
			})
		}
	} else {
		$(".fw_col").css({
			"position": "relative",
			"top": "0px",
			"left": "0px"
		})
		if($("body").hasClass('mobile') && $("body").hasClass('alternative')) {
			$(".fw_col.col0").css({
				"position": "fixed",
				"top": "50px",
				"left": "0px"
			})
		}
	}
}

//
// Automatically cancel unfinished ajax requests
// when the user navigates elsewhere if request is abortable=1
//
var fwXhrPool = [];
(function($) {
	$(document).ajaxSend(function(event, jqXHR, settings){
		var ajaxUrl = settings.url;
		if (ajaxUrl.search('abortable=1') > 0) {
			fwXhrPool.push(jqXHR);
		}
	});
	$(document).ajaxComplete(function(event, jqXHR, settings) {
		fwXhrPool = $.grep(fwXhrPool, function(x){return x!=jqXHR});
	});
	var oldbeforeunload = window.onbeforeunload;
	window.onbeforeunload = function() {
		var r = oldbeforeunload ? oldbeforeunload() : undefined;
		if (r == undefined) {
			// only cancel requests if there is no prompt to stay on the page
			// if there is a prompt, it will likely give the requests enough time to finish
			fwAbortXhrPool();
		}
		return r;
	}
})(jQuery);

//fwAbortXhrPool - abort all active and abortable XHR calls
function fwAbortXhrPool() {
	$.each(fwXhrPool, function(idx, jqXHR) {
		try {
			jqXHR.onreadystatechange = jqXHR.onerror = jqXHR.onload = null;
			jqXHR.abort();
		} catch (e) {}
	});
}


// Allow to translate 
<?php if(5 == $variables->developeraccess && !isset($_POST['skip_translate'])) { ?>
function fw_activate_translate()
{
	$("body :not(script) :contains('sys_translate_')").contents().filter(function() {
		return this.nodeType === 3;
	}).replaceWith(function() {
		return this.nodeValue.replace(/sys_translate_([A-Za-z0-9_]*)@([A-Za-z0-9_]*)@([A-Za-z0-9_]*)@/g,'<i class="fw_translate_var bind glyphicon glyphicon-text-background" data-id="$1" data-folder="$2" data-module="$3"></i>');
	});
	$('.fw_translate_var.bind').removeClass('bind').off('click').on('click', function(e){
		e.preventDefault();
		var data = {
            fwajax: 1,
			fw_nocss: 1,
			variable_id: $(this).data('id'),
			module: $(this).data('module'),
			folder: $(this).data('folder'),
			skip_translate: 1,
        };
		$.ajax({
			cache: false,
			type: 'POST',
			dataType: 'json',
			url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=Languages&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_language_variable";?>',
			data: data,
			success: function(json){
				fw_loading_end();
				$('#popupeditboxcontent').html(json.html);
				out_popup = $('#popupeditbox').bPopup(out_popup_options);
				$("#popupeditbox:not(.opened)").remove();
			}
		}).fail(function(){
			fw_loading_end();
		});
		return false;
	});
}
setInterval(fw_activate_translate, 1000);
<?php } ?>
</script>
