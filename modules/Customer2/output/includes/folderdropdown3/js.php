<script type="text/javascript">
$(document).ready(function() {
    $('.folderSelectSubfolderIcon, .folderSelect a').on('click', function(event) {
        event.preventDefault();
        $(this).closest('li').toggleClass('active').find('> ul').slideToggle(100)
    });

    $('.folderSelectField').on('click', function(event) {
        event.preventDefault();
        $(this).closest('.folderSelect').toggleClass('opened');
    });

    $('.folderSelect input[type="checkbox"]').on('change', function(event) {
        event.preventDefault();
        $('.folderSelect').removeClass('folderSelectError');
        if ($(this).is(':checked')) {
            var folderSelect = $(this).closest('.folderSelect');
            folderSelect.find('input[type="checkbox"]').prop('checked',false);
            $(this).prop('checked',true);
            folderSelect.find('[name="folderId"]').val($(this).data('folder-id')).trigger('change');
            folderSelect.removeClass('opened');
            path = getFullPath($(this));
            folderSelect.find('.folderSelectFieldText').text(path);
        }
    });
    $('[data-toggle="tooltip"]').tooltip();

    // Get full path - take clicked checkbox element as argument
    function getFullPath(elem) {
        var path;
        elem.parentsUntil('.folderSelectDropdown','li').each(function(index, el) {
            if (path) path = $(el).find('> .folderName').text() + ' / ' + path;
            else path = $(el).find('> .folderName').text();
        });
        return path;
    }

});
</script>
