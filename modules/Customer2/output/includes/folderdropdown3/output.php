<?php
/**
 * Base styling for fileupload block
 */
require __DIR__ . '/style.php';
/**
 * JavaScript logic for fileupload block (drag & drop events, upload handling)
 */
require __DIR__ . '/js.php';

/**
 * Function to build folder tree markup
 */
function buildFolderList($o_main, $parent_folder = 0) {
    global $formText_StoringItemsNotAllowed_output;

    echo '<ul>';
    $s_sql = "SELECT * FROM sys_filearchive_folder WHERE parent_id = ? AND content_status = '0'";
    $o_query = $o_main->db->query($s_sql, array($parent_folder));
    if($o_query && $o_query->num_rows()>0) {
        $f_rows = $o_query->result_array();
    }
    foreach($f_rows as $f_row){
        $subCount = 0;
        $s_sql = "SELECT * FROM sys_filearchive_folder WHERE parent_id = ? AND content_status = '0'";
        $o_query = $o_main->db->query($s_sql, array($f_row['id']));
        if($o_query && $o_query->num_rows()>0) {
            $subCount = $o_query->num_rows();
        }

        echo '<li>';

            // Subfolders icon
            echo '<span class="folderSelectSubfolderIcon">';
                if ($subCount) {
                    echo '<span class="glyphicon glyphicon-triangle-right"></span>';
                }
            echo '</span>';

            // Checkbox
            echo '<span class="folderSelectCheckboxBlock">';
                if (!$f_row['disallow_store_items']) {
                    echo '<input type="checkbox" data-folder-id="'.$f_row['id'].'">';
                }
                else {
                    echo '<span class="glyphicon glyphicon-exclamation-sign" data-toggle="tooltip" data-placement="right" title="'.$formText_StoringItemsNotAllowed_output.'"></span>';
                }
            echo '</span>';

            // Name
            echo '<a href="#" class="folderName"> '.$f_row['name'].'</a>';

            // Subfolders list
            if ($subCount) {
                buildFolderList($o_main, $f_row['id']);
            }
        echo '</li>';
    }
    echo '</ul>';
}

/**
 * Function to show tree + wrapper
 */
function showFolderDropdown() {
    global $formText_ChooseFolder_output; ?>
    <div class="folderSelect">
        <input type="hidden" name="folderId">
        <div class="folderSelectField">
            <div class="folderSelectFieldText"><?php echo $formText_ChooseFolder_output; ?></div>
            <span class="folderSelectArrowDown glyphicon glyphicon-triangle-bottom"></span>
        </div>
        <div class="folderSelectDropdown"><?php buildFolderList($o_main, 0); ?></div>
    </div>
<?php }
?>
