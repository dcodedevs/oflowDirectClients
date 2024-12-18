<?php
if($variables->developeraccess >= 20)
{
	?><div class="module-manager" style="width: 1200px;">
		<div><?php echo $formText_searchContentInFiles_fieldtype;?></div>
		<form name="searchFilesForm" method="post" action="<?php echo $extradir."/addOn_include/addlibrary.php?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&".(isset($_GET['moduleID'])?"moduleID=".$_GET['moduleID']."&":'')."module=".$buttonSubmodule."&searchcontent=1";?>">
			<input type="text" name="searchword" value="" />
			 
			<div><input name="submbtn" class="btn btn-success" value="<?php echo $formText_searchContentBtn_input;?>" type="submit"></div>
		</form>
	</div>
    <div>
        <div><?php echo $formText_searchResultInfoText_fieldtype;?></div>
        <div class="searchlogcontent">
            <div class="loadingspinner" <?php if(!isset($_GET['waitresult'])){ ?> style="display: none;" <?php  } ?>><img src="../modules/Modulemanager/input/includes/images/loading_spinner.gif" alt="spinner"></div>
            <?php
                if(is_file(dirname(__FILE__)."/../../../uploads/modulesearch.txt"))
                    echo nl2br(file_get_contents(dirname(__FILE__)."/../../../uploads/modulesearch.txt"));
                else
                    echo $formText_noPreviousSearch_fieldtype;
            ?>
        </div>
    </div>
	 

	<?php
    if(isset($_GET['waitresult']))
    {
        ?>
        <script type="text/javascript">
            
            $( document ).ready(function() {  
                new get_searchlogdata(); 
                 
                

            });
            function get_searchlogdata(){
                // console.log("test");
               $.get( "../modules/Modulemanager/addOn_include/ajax_checksearchfile.php", function( data ) {
                  if(data != '')
                    {  $( ".loadspinner" ).hide();
                        $('.searchlogcontent').html( data );
                        //alert( "data." );
                    }
                    else{
                        // console.log("test");
                        setTimeout(function(){get_searchlogdata();}, 10000);
                    }
                });
            }
        </script>
        <?php
        
    }
    
} else {
	?><div id="hovedfeltStrek"><table style="width:100%"><tr><td class="notAccessField"><?php echo $formText_YouHaveNoAccessToThisModule_input;?></td></tr></table></div><?php
}

?>
<style type="text/css">
.modulecontent {
	width:100% !important;
}
</style>