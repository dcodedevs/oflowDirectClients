<style>

.fwaFileupload.dragover .fwaFileupload_Files{
  border:1px solid #46b2e2;
}

.fwaFileupload_Files {
  border:1px dashed #DFDFDF;
  border-radius:3px;
}

.fwaFileupload_Files input[type="file"] {
  display:none;
}

.fwaFileupload_FilesBrowseDrop {
  margin:0 20px;
  padding:20px 0 13px 0;
  font-size:14px;
  border-bottom:1px solid #F2F2F2;
}

.fwaFileupload_FilesBrowseDrop:after {
  display:table;
  content:" ";
  clear:both;
}

.fwaFileupload_FilesBrowseDrop_Title {
  float:left;
  width:40%;
  padding-top:5px;
}

.fwaFileupload_FilesBrowseDrop_Icon {
  float:left;
  width:20%;
  font-size:2em;
  text-align:center;
  color:#CCC;
}

.fwaFileupload_FilesBrowseDrop_Browse_Or {
  text-align:center;
  margin-top:-15px;
}

.fwaFileupload_FilesBrowseDrop_Browse {
  float:left;
  width:40%;
}

.fwaFileupload_FilesBrowseDrop_Browse a {
  text-decoration:none;
  color:#46b2e2;
  /*border-bottom:1px dashed #46b2e2;*/
}

.fwaFileupload_FilesList {
  padding:20px 10px;
  height:200px;
  overflow:hidden;
}

.fwaFileupload_FilesList_Files {
  /*margin:10px;*/
  padding:0;
  list-style-type: none;
}

.fwaFileupload_FilesList_Files li {
  display:block;
  margin:0;
  padding:10px 10px;
  position:relative;
  font-size:0.85em;
}
.fwaFileupload_FilesList_Files li:after {
  display:table;
  content: " ";
  clear:both;
}

.fwaFileupload_FilesList_Files li:hover {
  background:#F2F2F2;
  border-radius:3px;
}

.fwaFileupload_FilesList_Files .name {
  float:left;
}

.fwaFileupload_FilesList_Files .progress {
  /*display:inline-block;*/
  margin:6px 0 0 6px;
  float:left;
  width:120px;
  height:auto;
  border-radius:10px;
  background:#F6F6F6;
  border:1px solid #ECECEC;
}

.fwaFileupload_FilesList_Files .progress-fill {
  width:1px;
  background:#70D000;
  height:6px;
}

.fwaFileupload_FilesList_Files .progress-complete {
  display:none;
}

.fwaFileupload_FilesList_Files .progress-complete-icon {
  font-weight:300;
  font-size:0.9em;
  color:#70D000;
  display:inline-block;
  margin-right:10px;
}

.fwaFileupload_FilesList_Files .delete-upload {
  display:none;
  position:absolute;
  top:12px;
  right:12px;
  color:#C5C5C5;
  font-size:0.9em;
}

.fwaFileupload_FilesList_Files li:hover .delete-upload {
  display:inline-block;
}

</style>
