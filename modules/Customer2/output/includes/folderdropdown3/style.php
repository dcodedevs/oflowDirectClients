<style media="screen">
    .folderSelect {
        margin:5px 0;
        position:relative;
    }

    .folderSelectArrowDown {
        position:absolute;
        top:16px;
        right:20px;
        color:#0393ff;
        font-size:0.9em;
    }

    .folderSelect.opened .folderSelectArrowDown:before {
        content:"\e014";
    }
    .folderSelectField {
        border:1px solid #F4F4F4;
        padding:10px;
        font-size:14px;
        cursor:pointer;
        position:relative;
    }

    .folderSelectError .folderSelectField {
        border:1px solid #c11;
        color:#c11;
    }

    .folderSelectDropdown {
        display:none;
        position:absolute;
        max-height:300px;
        overflow:hidden;
        overflow-y:scroll;
        box-shadow:0px 2px 3px #CCC;
        left:0px;
        right:0px;
        z-index:999;
    }

    .folderSelect.opened .folderSelectDropdown {
        display:block;
    }

    .folderSelect.opened {
        box-shadow:0px 2px 3px #CCC;
    }

    .folderSelectDropdown .glyphicon {
        color:#999;
    }

    .folderSelectDropdown ul {
        display:none;
        padding:0;
        margin:0;
    }

    .folderSelectDropdown ul li {
        display:block;
    }

    .folderSelectDropdown li {
        padding:8px;
        background:#FCFCFC;
        border:1px solid #F4F4F4;
        border-top:none;
    }

    .folderSelectDropdown li:first-child {
        border-top:1px solid #F4F4F4;
    }

    .folderSelectDropdown li a {
        color:#333;
    }

    .folderSelectDropdown li a:hover,
    .folderSelectDropdown li a:focus,
    .folderSelectDropdown li a:active
     {
        text-decoration:none;
        text-decoration:#222;
    }

    .folderSelectDropdown > ul {
        display:block;
    }

    .folderSelect li.active > span > .glyphicon-triangle-right:before {
        content:"\e252";
    }

    .folderSelectSubfolderIcon {
        width:15px;
        display:inline-block;
        cursor: pointer;
    }

    .folderSelectCheckboxBlock {
        width:15px;
        display:inline-block;
    }
</style>
