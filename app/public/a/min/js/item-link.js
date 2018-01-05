var itemLink=new function(){if(!document.getElementById||!document.getElementsByTagName){return;}
this.init=function(){itemLink.fldName=null;itemLink.fldLink=null;$('input[data-js-item-link-src]').each(function(){itemLink.fldLink=this;itemLink.fldName=$('#'+$(this).data('js-item-link-src')).get(0);});if(!itemLink.fldName){console.log('itemLink.js: Could not find the "name" field');return;}
if(!itemLink.fldLink){console.log('itemLink.js: Could not find the "url" field');return;}
itemLink.updateGeneratedLink();itemLink.editable=true;itemLink.linkChange();itemLink.fldLink.onkeyup=itemLink.linkChange;itemLink.fldName.onkeyup=itemLink.nameChange;itemLink.fldLink.onblur=itemLink.nameChange;itemLink.nameChange();}
this.updateGeneratedLink=function(){var text=itemLink.fldName.value;text=text.toLowerCase();text=text.replace('\'','');text=text.replace(/[^a-z0-9]/gi,'-');text=text.replace(/--+/,'-');text=text.replace(/-+$/,'');text=text.replace(/^-+/,'');text=text.substr(0,itemLink.fldLink.maxLength);itemLink.generatedLink=text;}
this.nameChange=function(){if(itemLink.editable){itemLink.updateGeneratedLink();itemLink.fldLink.value=itemLink.generatedLink;}else{}}
this.linkChange=function(){var old=itemLink.editable;itemLink.editable=((itemLink.fldLink.value=='')||(itemLink.editable&&itemLink.fldLink.value==itemLink.generatedLink));if(old!=itemLink.editable){}}
$(this.init);};