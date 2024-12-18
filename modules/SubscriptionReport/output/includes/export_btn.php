<div class="exportLinks">
  <a href="#" class="exportOrderlines"><?php echo $formText_exportOrderlines_text;?></a>
  <a href="#" class="exportCompany"><?php echo $formText_exportCompany_text;?></a>
  <a href="#" class="exportContact"><?php echo $formText_exportContactPersons_text;?></a>
</div>

<script type="text/javascript">
$(function() {
  $(".exportCompany").unbind("click").bind("click", function(ev){
      ev.preventDefault();
      fw_loading_start();
      var data = {
          subscription_type: $('.subscriptionTypeFilter').val(),
          list_filter: '<?php echo $list_filter; ?>',
          search_filter: $('.searchFilter').val(),
          status_filter: $('.statusFilter').val(),
          customerselfdefinedlist_filter: $('.customerSelfdefinedListFilter').val(),
          ownercompany_filter: $('.ownercompanyFilter').val(),
          date_filter: $('.date_filter').val(),
          ajaxSave: 1,
          languageID: '<?php echo $variables->languageID;?>'
      };
      $.ajax({
        method: "POST",
        url: "<?php echo $extradir;?>/output/includes/export.php",
        data: data,
        cache: false
      })
      .done(function( msg ) {
        $(".fw_info_message_wraper .fw_info_messages").html('');

        var generateIframeDownload = function(){

            fetch("<?php echo $extradir;?>/output/includes/export.php?type=1&time=<?php echo time();?>")
              .then(resp => resp.blob())
              .then(blob => {
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.style.display = 'none';
                a.href = url;
                // the filename you want
                a.download = 'export.xls';
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                fw_loading_end();
              })
              .catch(() => fw_loading_end());
        }
        generateIframeDownload();
      });
  })
    $(".exportOrderlines").unbind("click").bind("click", function(ev){
        ev.preventDefault();
        fw_loading_start();
        var data = {
            subscription_type: $('.subscriptionTypeFilter').val(),
            list_filter: '<?php echo $list_filter; ?>',
            search_filter: $('.searchFilter').val(),
            status_filter: $('.statusFilter').val(),
            customerselfdefinedlist_filter: $('.customerSelfdefinedListFilter').val(),
            ownercompany_filter: $('.ownercompanyFilter').val(),
            date_filter: $('.date_filter').val(),
            ajaxSave: 1,
            languageID: '<?php echo $variables->languageID;?>'
        };
        $.ajax({
          method: "POST",
          url: "<?php echo $extradir;?>/output/includes/export_orderlines.php",
          data: data,
          cache: false
        })
        .done(function( msg ) {
          $(".fw_info_message_wraper .fw_info_messages").html('');

          var generateIframeDownload = function(){
              fetch("<?php echo $extradir;?>/output/includes/export_orderlines.php?time=<?php echo time();?>")
                .then(resp => resp.blob())
                .then(blob => {
                  const url = window.URL.createObjectURL(blob);
                  const a = document.createElement('a');
                  a.style.display = 'none';
                  a.href = url;
                  // the filename you want
                  a.download = 'export.xls';
                  document.body.appendChild(a);
                  a.click();
                  window.URL.revokeObjectURL(url);
                  fw_loading_end();
                })
                .catch(() => fw_loading_end());

          }
          generateIframeDownload();
        });
    })

  $(".exportContact").unbind("click").bind("click", function(ev){
      ev.preventDefault();
      fw_loading_start();
      var data = {
          subscription_type: $('.subscriptionTypeFilter').val(),
          list_filter: '<?php echo $list_filter; ?>',
          search_filter: $('.searchFilter').val(),
          status_filter: $('.statusFilter').val(),
          customerselfdefinedlist_filter: $('.customerSelfdefinedListFilter').val(),
          ownercompany_filter: $('.ownercompanyFilter').val(),
          date_filter: $('.date_filter').val(),
          ajaxSave: 1,
          contactperson: 1,
          extradir: '<?php echo $extradir;?>',
          languageID: '<?php echo $variables->languageID;?>'
      };
      $.ajax({
        method: "POST",
        url:  "<?php echo $extradir;?>/output/includes/export.php",
        data: data,
        cache: false
      })
      .done(function( msg ) {
            fw_loading_end();

            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(msg);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();

      });
  });
});
</script>
