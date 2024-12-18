<script type="text/javascript" language="javascript">

	$(document).ready(function() {

		// Functions
		function fwcl_load_user_list (active_groups, refresh, callback) {

			if(typeof(callback) != 'function') {
				callback = function() { };
			}

			var fadeTime = 250;

			if(refresh) {
				$('#fwcl_list_container').height($('#fwcl_list_container').height());
				$('#fwcl_list_container ul').fadeOut(fadeTime, function() {
					fwcl_load_user_list_data(active_groups, refresh, function(data) {
						$('#fwcl_list_container').html(data);
						$('#fwcl_list_container ul').fadeIn(fadeTime);
						$('#fwcl_list_container').height('auto');
						callback();
					});
				});
			}
			else {
				fwcl_load_user_list_data(active_groups, refresh, function(data) {
					$('#fwcl_list_container').html(data);
					$('#fwcl_list_container ul').fadeIn(fadeTime);
					callback();
				});
			}
		}

		function fwcl_load_user_list_data(active_groups, refresh, callback) {
			$.ajax({
				url: "<?php echo $variables->account_framework_url; ?>getynet_fw/modules/OnlineList/output/userlist_ajax.php",
				data: {
					set: active_groups.set,
					company: active_groups.company,
					accountname: '<?php echo $_GET['accountname'];?>',
					caID: '<?php echo (!$variables->fw_url_share?$_GET['caID']:$_COOKIE[$_GET['accountname'].'_caID']);?>',
					dlang: '<?php echo $variables->defaultLanguageID; ?>',
					lang: '<?php echo $variables->languageID;?>',
					refresh: refresh
				},
				success: function (data) {
					callback(data);
				},
				cache: false
			});
		}

		function fwcl_get_active_groups() {

			var set = [];
			var company = [];
			var count = 0;
			var countAll = $('#fwcl_groups input').length - 1;

			$("#fwcl_groups input:checked").each(function () {
				if(!$(this).hasClass('showall')) {
					set.push($(this).attr('data-setid'));
					company.push($(this).attr('data-companyid'));
					count++;
				}

			});

			var active_groups = {
				set: set,
				company: company,
				count: count,
				countAll: countAll
			};

			return active_groups;
		}

		function fwcl_fuzzy_filter(searchString) {
			if (searchString.length > 0) {
				$('#fwcl_list li').each(function() {
					var currentString = $(this).find('.name').text();
					if (currentString.toLowerCase().indexOf(searchString.toLowerCase()) > -1) {
						$(this).show(0);
					}
					else {
						$(this).hide(0);
					}
				});
			}
			else {
				$('#fwcl_list li').show(0);
			}
		}

		function fwcl_update_active_group_counter(active_groups) {
			$('#fwcl_groups_button .selected').html(active_groups.count);
		}

		function fwcl_expand_contact_list() {
			var elem = $('.fw_contact_list');
			if(elem.hasClass('collapsed') && !$('#fw_chat').is(':visible'))
				elem.removeClass('collapsed').addClass('collapseonleave expanded').css('width', '250px');
		}

		function fwcl_collapse_contact_list() {
			var elem = $('.fw_contact_list');
			if(elem.hasClass('collapseonleave') && !$('#fw_chat').is(':visible'))
				elem.addClass('collapsed').removeClass('collapseonleave expanded').css('width', '70px');
		}

		// Listeners
		var fwcl_contact_list_expand_timer, fwcl_contact_list_collapse_timer;
		var fwcl_contact_list_timeout = 700;

		$('.fw_contact_list').on('mouseenter', function() {
			clearTimeout(fwcl_contact_list_collapse_timer);
			fwcl_contact_list_expand_timer = setTimeout(function() {
				fwcl_expand_contact_list();
			}, fwcl_contact_list_timeout);
		});

		$('.fw_contact_list').on('mouseleave', function() {
			clearTimeout(fwcl_contact_list_expand_timer);
			fwcl_contact_list_collapse_timer = setTimeout(function() {
				fwcl_collapse_contact_list();
			}, fwcl_contact_list_timeout);
		});

		$('#fwcl_groups_button').on('click', function(e) {
			e.preventDefault();
			$('.fw_contact_list_filter.filter_groups ul').slideToggle();
			$('.fw_contact_list_filter.filter_groups .button').toggleClass('opened');
		});

		$('#fwcl_groups input').on('change', function() {
			var active_groups = fwcl_get_active_groups();
			if(!$(this).hasClass('showall')) {
				fwcl_load_user_list(active_groups, 1, function() {
					fwcl_update_active_group_counter(active_groups);
					fwcl_fuzzy_filter($('#fwcl_search').val());
				});
				if (active_groups.count < active_groups.countAll) {
					$('#fwcl_groups .showall').prop('checked', false);
				}
				else {
					$('#fwcl_groups .showall').prop('checked', true);
				}

			}
			if($(this).hasClass('showall')) {
				if($(this).prop('checked')) $('#fwcl_groups input').prop('checked', true);
				else $('#fwcl_groups input').prop('checked', false);
				active_groups = fwcl_get_active_groups();
				fwcl_load_user_list(active_groups, 1, function() {
					fwcl_update_active_group_counter(active_groups);
					fwcl_fuzzy_filter($('#fwcl_search').val());
				});
			}
		});

		$('#fwcl_search_button').on('click', function(e) {
			e.preventDefault();
			$('.fw_contact_list_filter.filter_search .fw_filter_search_field').slideToggle();
			$('.fw_contact_list_filter.filter_search .button').toggleClass('opened');
		});

		$('#fwcl_search').on('input', function() {
			fwcl_fuzzy_filter($(this).val());
		});

		// On load
		var active_groups = fwcl_get_active_groups();
		fwcl_load_user_list(active_groups, 0);


		$("div#username-status-box").ready(function()
		{
			$('#sys-userstatusmsgdate').datepicker({
				showButtonPanel: true,
				dateFormat: "dd.mm.yy"
			});
			$('#sys-userstatusmsgtime').timepicker({
				timeFormat: 'HH:mm',
				hour: 23,
				minute: 59,
				controlType: 'select'
			});
			$("#sys-username-status-edit-form").submit(function() {
				$("#sys-username-status-edit-btn").attr("disabled","disabled");
				var sysuserstatussmgshow = [];
				$(".sys-userstatussmgshow:checked").each(function (index, value) {
					sysuserstatussmgshow.push($(this).val());
				});
				var param = {userstatus: $("input:radio:checked[name=sys-userstatus]").val(), userstatusmessage: $("#sys-userstatusmessage").val(), userstatusmsgdate: $("#sys-userstatusmsgdate").val(), userstatusmsgtime: $("#sys-userstatusmsgtime").val(), userstatussmgshow: sysuserstatussmgshow, accountname: '<?php echo $_GET['accountname'];?>', caID: '<?php echo (!$variables->fw_url_share?$_GET['caID']:$_COOKIE[$_GET['accountname'].'_caID']);?>', dlang: '<?php echo $variables->defaultLanguageID; ?>', lang: '<?php echo $variables->languageID;?>'};

				$.ajax({
					url: "<?php echo $variables->languageDir;?>modules/OnlineList/output/userstatusupdate_ajax.php",
					data: param,
					dataType: "json",
					success: function (data) {
						$(".fancybox-close").trigger('click');
						$("#sys-username-status-edit-btn").removeAttr("disabled");
						if(data.statusmessage)
							$('#username-status-box .col-2 .username-status-msg').html('- ' + data.statusmessage);
						else
							$('#username-status-box .col-2 .username-status-msg').html('- <?php echo $formText_AddAStatusMessageHere;?>');
						$('#username-status-box .col-3').attr('class','col-3 col-3-status-' + data.status);
						//$("div#username-status-box").fadeOut("fast");
						//location.reload();
					},
					cache: false
				});
				return false;
			});
		});

});

</script>
