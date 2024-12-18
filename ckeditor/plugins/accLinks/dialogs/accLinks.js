﻿/*
 Copyright (c) 2015, Dcode & Niko. All rights reserved.
 For licensing, send email to niko@dcode.no
*/
CKEDITOR.dialog.add("accLinksDialog",function(a){return{name:"accLinks",id:"accLinks",title:"Account links",minWidth:350,minHeight:200,contents:[{id:"tab-basic",label:"Basic Settings",elements:[{type:"text",id:"linkName",label:"Link name",validate:CKEDITOR.dialog.validate.notEmpty("Link name field cannot be empty."),setup:function(a){this.isChangedLocal=!1,this.setValue(a.getText(),!0)},isChanged:function(){return!(!this.isChangedLocal||this.getValue()==this.getInitValue())},onChange:function(){this.isChangedLocal=!0},commit:function(a){a.setText(this.getValue()),a.setAttributes({href:"",class:"ck_accLinks ck_addedLink"})}},{type:"select",id:"module",label:"Select module",items:[["Please Choose","0"]],default:"None",setup:function(a){this.isChangedLocal=!1,this.clear(),this.add("Please Choose","0"),this.isLocked=0;var b=this;$.ajax({url:"../ckeditor/plugins/accLinks/dialogs/ajax.getData.php?cat=1",cache:!1,dataType:"json",success:function(c){for(var d in c)b.add(c[d],d);a.getAttribute("data-module-id")&&b.setValue(a.getAttribute("data-module-id"),!0)},error:function(a,b,c){alert(c+"\r\n"+a.statusText+"\r\n"+a.responseText)}})},isChanged:function(){return!(!this.isChangedLocal||this.getValue()==this.getInitValue())},onChange:function(){this.isChangedLocal=!0;var a=this.getDialog().getContentElement("tab-basic","moduleContent");0!=this.getValue()?(a.enable(),$.ajax({url:"../ckeditor/plugins/accLinks/dialogs/ajax.getData.php?cat=2&val="+this.getValue(),cache:!1,dataType:"json",success:function(b){a.clear();for(var c in b)a.add(b[c],c)},error:function(a,b,c){alert(c+"\r\n"+a.statusText+"\r\n"+a.responseText)}})):a.disable()},commit:function(a){a.setAttribute("data-module-id",this.getValue())}},{type:"select",id:"moduleContent",label:"Select content",items:[["None","0"]],default:"None",setup:function(a){this.isChangedLocal=!1,this.clear(),this.add("Select content","0");var b=this;a.getAttribute("data-module-id")?(b.enable(),$.ajax({url:"../ckeditor/plugins/accLinks/dialogs/ajax.getData.php?cat=2&val="+a.getAttribute("data-module-id"),cache:!1,dataType:"json",success:function(c){b.clear();for(var d in c)b.add(c[d],d);a.getAttribute("data-page-id")&&b.setValue(a.getAttribute("data-page-id"),!0)},error:function(a,b,c){alert(c+"\r\n"+a.statusText+"\r\n"+a.responseText)}})):b.disable()},isChanged:function(){return!(!this.isChangedLocal||this.getValue()==this.getInitValue())},onChange:function(){this.isChangedLocal=!0},commit:function(a){a.setAttribute("data-page-id",this.getValue())}}]}],onShow:function(){var b=a.getSelection(),c=b.getSelectedText(),d=b.getStartElement();if(d&&(d=d.getAscendant("a",!0)),d&&"a"==d.getName()){if(this.insertMode=!1,d.getAttribute("data-pageid")){var e=d.getAttribute("data-pageid").split("#");d.setAttributes({"data-module-id":e[0],"data-page-id":e[1]})}}else d=a.document.createElement("a"),""!=c&&d.setText(c),this.insertMode=!0;this.element=d,this.setupContent(this.element)},onOk:function(){var b=this,c=b.element;b.commitContent(c),c.setAttribute("data-pageid",c.getAttribute("data-module-id")+"#"+c.getAttribute("data-page-id")),c.removeAttributes(["data-module-id","data-page-id"]),b.insertMode&&a.insertElement(c)}}});