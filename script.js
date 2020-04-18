var reloadAfterSubmit=false;
function initFormUI() {
	$("form select[data-value]").each(function() {this.value=$(this).data('value');});

	$("form select.multiple").each(function() {
		
	});
	if(typeof $.fn.tagsinput == "function") {
		$('input[type=tags]').tagsinput({
	  
		});
	}
	
	$(".select-group select:not(.multiple).search").each(function() {
		loadDropSearch(this);
	});
	
	//Chain selectors
	$("form .ajaxchain").each(function() {
		$(this).change(function(e) {
			loadAjaxChain(this);
		});
		if($(this).closest("form").hasClass("edit")) {
			if($(this).val()!=null && $(this).val().length>0) {
				loadAjaxChain(this);
			}
		} else {
			if($(this).val()!=null && $(this).val().length>0) {
				loadAjaxChain(this);
			}
		}
	});
	
	$("form .autocomplete").each(function() {
		$(this).change(function(e) {
			loadAutocomplete(this);
		});
		if($(this).val()!=null && $(this).val().length>0) {
			loadAutocomplete(this);
		}
	});

	$("form .nodb").each(function() {
		$(this).attr("name","");
	});

	initDateFields();
	initAdvFields();
	initFileFields();
	initJSONFields();

	$("form input.field-slug[src]").each(function() {
		src=$(this).attr('src');
		$(this).closest("form").find("input[name='"+src+"'],select[name='"+src+"']").change(function() {
			val=this.value;
			nm=$(this).attr('name');
			ele=$(this).closest("form").find("input.field-slug[src='"+nm+"']");
			if(ele.attr("disabled")!=null) return;
			ele.val(slugify(val));
		});
	});

	$("form.form").delegate("button[cmd=cancel]","click",function() {
		clink=$(this).closest("form.form").data("clink");
		if(clink!=null && clink.length>0) {
			window.location=clink;
		}
	});
	
	$("form.form").delegate("button[cmd=submitnew]","click",function() {
		reloadAfterSubmit=true;
	});
  
	$("form.form").delegate("button[cmd],a[cmd]","click",function(e) {
		cmd=$(this).attr('cmd');
		if(cmd==null || cmd=="submit" || cmd=="submitnew" || cmd=="update" || cmd=="cancel" || cmd=="escape") {
			return;
		}
		e.preventDefault();
		runFormCommand(cmd, this);
	});

	if($("form.form .form-actions .form-simplicity input").length>0) {
		if($("input[name].required,select[name].required,textarea[name].required",".formbox fieldset").length>0) {
			$("form.form .form-actions .form-simplicity input").change(function() {
				$.cookie('FORMS_SHOW_ALL_FIELDS',$(this).is(":checked"));
				if($(this).is(":checked")) {
					$("input[name]:not(.required),select[name]:not(.required),textarea[name]:not(.required)",".formbox fieldset").each(function() {
						if(!$(this).closest(".field-container").hasClass("field-hidden")) {
							$(this).closest(".field-container").removeClass("hidden");
						}
					});
				} else {
					$("input[name]:not(.required),select[name]:not(.required),textarea[name]:not(.required)",".formbox fieldset").each(function() {
						if(!$(this).closest(".field-container").hasClass("field-hidden")) {
							$(this).closest(".field-container").addClass("hidden");
						}
					});
				}
			});
				
			if($("form.form").hasClass("simple-form")) {
				$("input[name]:not(.required),select[name]:not(.required),textarea[name]:not(.required)",".formbox fieldset").each(function() {
						if(!$(this).closest(".field-container").hasClass("field-hidden")) {
							$(this).closest(".field-container").addClass("hidden");
						}
					});
			} else {
				if($.cookie('FORMS_SHOW_ALL_FIELDS')=="true") {
					$("form.form .form-actions .form-simplicity input")[0].checked=true;
				} else {
					$("input[name]:not(.required),select[name]:not(.required),textarea[name]:not(.required)",".formbox fieldset").each(function() {
						if(!$(this).closest(".field-container").hasClass("field-hidden")) {
							$(this).closest(".field-container").addClass("hidden");
						}
					});
				}
			}
		}
	}

	//$("form.validate").valid();

	$("form.validate").validate({
		  //debug:true,
		  ignore: ".ignore",
		  errorClass: "error",
		  validClass: "success",
		  //wrapper: "li",
		  //errorContainer: "#messageBox1, #messageBox2",
		  //errorLabelContainer: "#messageBox1 ul",
		  //wrapper: "li",
		  //ignoreTitle: false,
		  //onsubmit: false,
		  //onfocusout: false,
		  //onkeyup: false,
		  //focusCleanup: true,
		  submitHandler: function(form) {
				formKey=$(form).data('formkey');
		  	formFrameID="FORMFRAME"+Math.ceil(Math.random()*10000000);

				$("body").find("iframe.formFrame#"+formFrameID).detach();

		  	$("body").append("<iframe id='"+formFrameID+"' name='"+formFrameID+"' class='formFrame hidden' style='display:none !important;' ></iframe>");
		  	$(form).attr("target",formFrameID);
		  	$(form).attr("action",_service("forms","submit")+"&formid="+formKey);
		    //$(form).ajaxSubmit();
		    form.submit();

		    $("form[data-formkey='"+formKey+"']").hide();
		    $("form[data-formkey='"+formKey+"']").parent().find(".alert").detach();
		    $("form[data-formkey='"+formKey+"']").parent().append("<div class='ajaxloading ajaxloading3'></div>");

		  },
		  invalidHandler: function(event, validator) {
		  		if(typeof lgksToast=="function") lgksToast("Some required fields are invalid. They have been marked.<br>Please fix them to submit.");
		  		else if(typeof lgksAlert=="function") lgksAlert("Some required fields are invalid. They have been marked.<br>Please fix them to submit.");
		  		else {
		  			alert("Some required fields are invalid. They have been marked.<br>Please fix them to submit.");
		  		}
		  		//console.log(event);
		  		// 'this' refers to the form
			    // var errors = validator.numberOfInvalids();
			    // if (errors) {
			    //   var message = errors == 1
			    //     ? 'You missed 1 field. It has been highlighted'
			    //     : 'You missed ' + errors + ' fields. They have been highlighted';
			    //   $("div.error span").html(message);
			    //   $("div.error").show();
			    // } else {
			    //   $("div.error").hide();
			    // }
		  }
			// ,rules: {
			//     // simple rule, converted to {required:true}
			//     name: "required",
			//     // compound rule
			//     email: {
			//       required: true,
			//       email: true
			//     }
			// }
			// ,messages: {
			//     name: "Please specify your name",
			//     email: {
			//       required: "We need your email address to contact you",
			//       email: "Your email address must be in the format of name@domain.com"
			//     }
			// }
		});
}
function formsSubmitStatus(formid,msgObj,msgType,gotoLink) {
	if(msgType==null) msgType="SUCCESS";
	else msgType=msgType.toUpperCase();
	//console.warn(msgObj);
	
	if($("form[data-formkey='"+formid+"']").length>0) {
		formBox=$("form[data-formkey='"+formid+"']");
		switch(msgType) {
			case "ERROR":
				lgksToast("<i class='glyphicon glyphicon-ban-circle'></i>&nbsp;"+msgObj);
				formBox.parent().find(".ajaxloading").detach();
				formBox.show();
				return;
			break;
			case "INFO":
				lgksToast("<i class='glyphicon glyphicon-comment'></i>&nbsp;"+msgObj);
			break;
			case "SUCCESS":
				if(typeof msgObj == "string") {
					lgksToast("<i class='glyphicon glyphicon-comment'></i>&nbsp;"+msgObj);
				} else {
					lgksToast("<i class='glyphicon glyphicon-comment'></i>&nbsp;Successfully update database.");
					$.each(msgObj,function(k,v) {
						try {
							if(formBox.find("input[name='"+k+"'],textarea[name='"+k+"'],select[name='"+k+"']").attr("type")=="file") {
								formBox.find("input[name='"+k+"'],textarea[name='"+k+"'],select[name='"+k+"']").val('');
							} else {
								formBox.find("input[name='"+k+"'],textarea[name='"+k+"'],select[name='"+k+"']").val(v);
							}
						} catch($e) {
						}
					});
				}
			break;
			default:
				lgksToast("<i class='glyphicon glyphicon-info-sign'></i>&nbsp;"+msgObj);
		}

		postsubmit=formBox.data('postsubmit');
		if(postsubmit!=null && typeof window['postsubmit']=="function") {
			window['postsubmit'](formid,msgObj,msgType);
		}
		if(gotoLink==null || gotoLink.length<=0) {
			gotoLink=formBox.data('glink');
		}
		if(reloadAfterSubmit) {
			gotoLink=formBox.data('relink');
		}
		reloadAfterSubmit=false;

		title = "...";
		
		if(gotoLink!=null && gotoLink.length>0) {
			if(gotoLink=="closepopup") {
				if(formBox.closest(".modal-dialog").length==1) {
					$(".modal").modal("hide");
				}
			} else if(gotoLink=="closewindow") {
				window.close();
			} else if(gotoLink.substr(0,7)=="http://" || gotoLink.substr(0,8)=="https://") {
				gotoLink=gotoLink;
				
				if(formBox.closest(".modal-dialog").length==1) {
					$(".modal").modal("hide");
					showLoader();
					lgksOverlayURL(gotoLink,title,function() {
							hideLoader();
						});
				} else {
					window.location=gotoLink;
				}
			} else {
				if(formBox.closest(".modal-dialog").length==1) {
					if(gotoLink.substr(0,1)=="@") {
						gotoLink=_link(gotoLink.substr(1));
					} else {
						gotoLink=_link("popup/"+gotoLink);
					}
					$(".modal").modal("hide");
					showLoader();
					lgksOverlayURL(gotoLink,title,function() {
							hideLoader();
						});
				} else {
					if(gotoLink.substr(0,1)=="@") {
						gotoLink=_link(gotoLink.substr(1));
					} else {
						gotoLink=_link("modules/"+gotoLink);
					}
					window.location=gotoLink;
				}
			}
			gotoLink=null;
		} else {
			formBox.parent().find(".ajaxloading").detach();
			formBox.show();
		}
	} else {
		//console.warn(formid+">>"+msgType+">>"+msgObj);
		lgksToast("<i class='glyphicon glyphicon-info-sign'></i>Error with form, try reloading");
	}
}
function initJSONFields() {
	$("form").delegate(".jsonField .cmdAction[cmd]","click",function() {
		cmd=$(this).attr("cmd");
		switch(cmd) {
			case "addJSONKeyField":
				nm=$(this).closest("table").attr("name");
				q=[];
				$(this).closest("table").find("thead th[name]").each(function(k,v) {
						k=$(this).attr("name");
						q.push("<td><input name='"+nm+"["+k+"][]' class='form-control' placeholder='"+k+"' /></td>");
				});
				html="<tr><td width=25px><i class='fa fa-bars reorderRow'></i></td>"+q.join("")+"<td width=25px><i class='fa fa-times cmdAction' cmd='removeJSONKeyField'></i></td></tr>";

				$(this).closest("table").find("tbody").append(html);
				break;
			case "removeJSONKeyField":
				$(this).closest("tr").detach();
				break;
		}
	});

	if(typeof $.fn.sortable == "function") {
		$( "form .jsonField tbody" ).sortable({
			appendTo:"form .jsonField tbody",
			axis: "y",
			handle: ".reorderRow"
		});
		$( "form .jsonField tbody" ).disableSelection();
	}
}
function initAdvFields() {
	if(typeof nicEditor == "function") {
		$("textarea.field-richtextarea").each(function() {
			$(this).css("width","100%");$(this).attr("id",$(this).attr("name"));
			new nicEditor({iconsPath : nicIconPath,fullPanel : true,maxHeight : 101}).panelInstance(this);
		});
		$('.nicEdit-panelContain').parent().width('100%');
		$('.nicEdit-panelContain').parent().next().width('98%');
		$('.nicEdit-main').width('98%').css("min-height", "90px");
	} else {
		$("textarea.field-richtextarea").css("width","100%");
	}
	
	$("textarea.field-markup").each(function() {
		rid=$(this).attr("name");
		$(this).css("width","100%");
		$(this).attr("id",rid);
		if(typeof SimpleMDE == "function") {
			new SimpleMDE({
					element: document.getElementById(rid),
					autoDownloadFontAwesome: false,
					promptURLs: true,
					spellChecker: true,
					status: false,
					hideIcons: ["guide","side-by-side","fullscreen"],
					showIcons: ["code", "table", "italic", "strikethrough", "horizontal-rule", "clean-block"],
					insertTexts: {
						horizontalRule: ["", "\n\n-----\n\n"],
						//image: ["![](http://", ")"],
						//link: ["[", "](http://)"],
						//table: ["", "\n\n| Column 1 | Column 2 | Column 3 |\n| -------- | -------- | -------- |\n| Text     | Text      | Text     |\n\n"],
					},
			});
		}
	});

	if(typeof $.fn.multiselect == "function") {
		$(".select-group select.field-dropdown.multiple").each(function() {
				rid=$(this).attr("name");
				$(this).css("width","100%");$(this).attr("id",rid);
				vx=$(this).data("value");
				if(vx!=null && vx.length>0) {
					vx=vx.split(",");
					$(this).val(vx);
				}
				$(this).multiselect();
			});
	}
}
function initDateFields() {
	if(typeof $.fn.datetimepicker == "function") {
		$("input.field-date").each(function() {
			$(this).datetimepicker({
					format: 'DD/MM/YYYY'
				});
			});
		$("input.field-datetime").each(function() {$(this).datetimepicker({
					format: 'DD/MM/YYYY HH:ss'
				});
			});
		$("input.field-year").each(function() {
				//$(this).val("");
				$(this).datetimepicker({
					format: 'YYYY'
				});
			});
		$("input.field-month").each(function() {
				//$(this).val("");
				$(this).datetimepicker({
					format: 'DD/MM'
				});
			});
		$("input.field-time").each(function() {
				//$(this).val("");
				$(this).datetimepicker({
					format: 'HH:ss'
				});
			});
	} else {
		$(".field-date").attr("type","date");
		$(".field-datetime").attr("type","datetime-local");
		$(".field-month").attr("type","month");
    	$(".field-time").attr("type", "time");

    	$(".field-year").each(function() {
		    if($(this).attr("required")!=null)
		        htmlYear = "<select class='form-control select field-year required' name='"+$(this).attr("name")+"' required no-options='Select Year'>";
		    else
		        htmlYear = "<select class='form-control select field-year' name='"+$(this).attr("name")+"'>";

		    for(i=1900;i<2100;i++) htmlYear += "<option>"+i+"</option>";
		    htmlYear += "</select><div class='input-group-addon'><i class='fa fa-calendar'></i></div>";
		    $(this).parent().html(htmlYear)
		});
	}
}
function initFileFields() {
	$("form").delegate(".file-input .fa-close","click",function(e) {
		divBlock=$(this).closest(".file-input");
		formBlock=$(this).closest("form");

		$(this).closest(".file-preview-thumb").detach();
		$(this).closest(".file-queue-thumb").detach();

		if(divBlock.find(".file-preview-thumbnails").children().length<=0) {
			nm=divBlock.attr("name");
			formKey=formBlock.data('formkey');

			processAJAXPostQuery(_service("forms","empty")+"&formid="+formKey,"field="+nm,function(txt) {
				console.log(txt);

			});
		}
	});

	$("form").delegate(".file-input .file-drop","click",function(e) {
		$(this).find("input[type=file]")[0].click();
	});

	if(typeof $.fn.sortable == "function") {
		$("form .file-preview-thumbnails").sortable({
	      revert: true
	    });
	}
	

	$("form").delegate(".file-input .file-drop input[type=file]","change",function(e) {
		vx=$(this).val();
		if(vx==null) return;
		vy=vx.split("\\");
		vy=vy[vy.length-1];
		faicn=getFAClass(vy);
		nm=$(e.target).closest(".file-input").attr("name");
		//if($(e.target).attr("multiple")=="true") {
		if($(e.target).closest(".file-input").hasClass("file-input-multiple")) {

		} else {
			//$(e.target).closest(".file-input").find(".file-preview-thumbnails .file-queue-thumb").detach();
			$(e.target).closest(".file-input").find(".file-preview-thumbnails").html("");
		}

		box=$(e.target).closest(".file-upload");

		html="<div class='file-queue-thumb' title='"+vy+"'><span class='pull-right fa fa-times fa-close'></span><i class='fileicon fa "+faicn+"'></i><span class='filename'><citie>(new)</citie> "+vy+"</span></div>";
		if($(e.target).closest(".file-input").hasClass("file-input-multiple")) {
			$(e.target).closest(".file-input").find(".file-preview-thumbnails").prepend(html).find(".file-queue-thumb").append($(e.target).attr("name",nm+"[]"));
		} else {
			$(e.target).closest(".file-input").find(".file-preview-thumbnails").prepend(html).find(".file-queue-thumb").append($(e.target).attr("name",nm));
		}
		box.append("<input type='file' class='form-file-field hidden' >");
	});

	$("form").delegate(".file-input .file-gallery","click",function(e) {
		lgksAlert("Form Gallery Support Not Found, use photo type instead.");
	});
}
function runFormCommand(cmd, src) {
  cmdOriginal=cmd;
  cmd=cmd.split("@");
  cmd=cmd[0];

//   hash=$(src).closest(".formbox").find("form").data('formkey');
//   gkey=$(src).closest(".formbox").find("form").data('formkey');
  formDiv=$(src).closest("form.form");
  hash=formDiv.data('formkey');
  gkey=formDiv.data('formkey');
  if(gkey==null) return;
  
  $(".modal").modal("hide");

  switch(cmd) {
    case "forms":case "reports":case "infoview":
      title=$(src).text();
      if(title==null || title.length<=0) {
        title=$(src).attr("title");
      }
      if(title==null || title.length<=0) {
        title="Dialog";
      }

      cmdX=cmdOriginal.split("@");
      if(cmdX[1]!=null) {
        cmdX[1]=cmdX[1].replace("{hashid}",hash).replace("{gkey}",gkey);

        showLoader();
        lgksOverlayURL(_link("popup/"+cmd+"/"+cmdX[1]),title,function() {
            hideLoader();
          },{"className":"overlayBox reportPopup"});
      }
    break;
    case "page":
      title=$(src).text();
      if(title==null || title.length<=0) {
        title=$(src).attr("title");
      }
      if(title==null || title.length<=0) {
        title="Dialog";
      }

      cmdX=cmdOriginal.split("@");
      if(cmdX[1]!=null) {
        cmdX[1]=cmdX[1].replace("{hashid}",hash).replace("{gkey}",gkey);
        window.location=_link("modules/"+cmdX[1]);
      }
      break;
    case "module":case "popup":
      title=$(src).text();
      if(title==null || title.length<=0) {
        title=$(src).attr("title");
      }
      if(title==null || title.length<=0) {
        title="Dialog";
      }

      cmdX=cmdOriginal.split("@");
      if(cmdX[1]!=null) {
        cmdX[1]=cmdX[1].replace("{hashid}",hash).replace("{gkey}",gkey);

        if(cmd=="module" || cmd=="modules") {
          top.openLinkFrame(title,_link("modules/"+cmdX[1]),true);
        } else {
          showLoader();
          lgksOverlayURL(_link("popup/"+cmdX[1]),title,function() {
              hideLoader();
            },{"className":"overlayBox reportPopup"});
        }
      }
    break;
    case "ui":
      cmdX=cmdOriginal.split("@");
      if(cmdX[1]!=null) {
        cmd=cmdX[1];
        gkey=$(src).closest(".reportTable").data('gkey');
        if(gkey==null) return;
        $.cookie("RPTVIEW-"+gkey,cmd,{ path: '/' });
        window.location.reload();
      }
    break;
    default:
      if(typeof window[cmd]=="function") {
        window[cmd](formDiv, src);
      } else {
        console.warn("Action CMD not found : "+cmd);
      }
  }
}
function loadAutocomplete(srcSelect) {
	ajxURL=null;
	target=$(srcSelect).attr("autocomplete-target");
	name=$(srcSelect).attr("name");
	
	if(target==null) {
		if($(srcSelect).hasClass("autocompletescmd")) {
            scmd=$(srcSelect).attr("autocomplete-scmd");

            scmd=scmd.split("/");
            if(scmd[1]==null) scmd[1]="";

            ajxURL=_service(scmd[0],scmd[1])+"&type=single&refid="+$(srcSelect).val()+"&srcname="+name;
        } else if($(srcSelect).hasClass("autocompleteself")) {
            formKey=$(srcSelect).closest("form").data('formkey');
            ajxURL=_service("forms","autocomplete")+"&type=single&refid="+$(srcSelect).val()+"&srcname="+name+"&formid="+formKey;
        }

        if(ajxURL!=null && ajxURL.length>0) {
            processAJAXQuery(ajxURL, function(data) {
                $.each(data.Data,function(k,v) {
                    $(srcSelect).closest("form").find("*[name='"+k+"']").val(v);
                });
            },"json");
        }
	} else {
		target=target.split(",");
		$.each(target, function(k,nm) {
			if($("*[name='"+nm+"']").is("select")) {
				if($(srcSelect).hasClass("autocompletescmd")) {
						scmd=$(srcSelect).attr("autocomplete-scmd");

						scmd=scmd.split("/");
						if(scmd[1]==null) scmd[1]="";

						ajxURL=_service(scmd[0],scmd[1])+"&type=raw&refid="+$(srcSelect).val()+"&srcname="+name;
				} else if($(srcSelect).hasClass("autocompleteself")) {
						formKey=$(srcSelect).closest("form").data('formkey');
						ajxURL=_service("forms","autocomplete")+"&type=raw&refid="+$(srcSelect).val()+"&srcname="+name+"&formid="+formKey;
				}
				if(ajxURL!=null && ajxURL.length>0) {
						processAJAXQuery(ajxURL, function(data) {
							html=[];
							$.each(data.Data,function(k1,v1) {
								if(v1[nm]==null) {
									if(v1['value']!=null) v1[nm]=v1['value'];
									else return;
								}
								html.push("<option value='"+v1[nm]+"'>"+v1.title+"</option>");
							});
							$(srcSelect).closest("form").find("select[name='"+nm+"']").html(html);
						},"json");
				}
			} else {
				if($(srcSelect).hasClass("autocompletescmd")) {
						scmd=$(srcSelect).attr("autocomplete-scmd");

						scmd=scmd.split("/");
						if(scmd[1]==null) scmd[1]="";

						ajxURL=_service(scmd[0],scmd[1])+"&type=single&refid="+$(srcSelect).val()+"&srcname="+name;
				} else if($(srcSelect).hasClass("autocompleteself")) {
						formKey=$(srcSelect).closest("form").data('formkey');
						ajxURL=_service("forms","autocomplete")+"&type=single&refid="+$(srcSelect).val()+"&srcname="+name+"&formid="+formKey;
				}
				if(ajxURL!=null && ajxURL.length>0) {
						processAJAXQuery(ajxURL, function(data) {
								$.each(data.Data,function(k,v) {
										if($(srcSelect).closest("form").find("*[name='"+k+"']").hasClass("form-control-static")) {
											$(srcSelect).closest("form").find("*[name='"+k+"']").html(v);
										} else {
											$(srcSelect).closest("form").find("*[name='"+k+"']").val(v);
										}
								});
						},"json");
				}
			}
		});
	}
}
function loadAjaxChain(srcSelect) {
	ajxURL=null;
	target=$(srcSelect).attr("ajaxchain-target");
	name=$(srcSelect).attr("name");

	if($(srcSelect).hasClass("ajaxchainscmd")) {
		scmd=$(srcSelect).attr("ajaxchain-scmd");

		scmd=scmd.split("/");
		if(scmd[1]==null) scmd[1]="";

		if($(srcSelect).closest("form").find("select[name='"+target+"']").length>0) {
			ajxURL=_service(scmd[0],scmd[1],"select")+"&refid="+$(srcSelect).val()+"&srcname="+name;
		} else {
			ajxURL=_service(scmd[0],scmd[1])+"&refid="+$(srcSelect).val()+"&srcname="+name;
		}
	} else if($(srcSelect).hasClass("ajaxchainself")) {
		formKey=$(srcSelect).closest("form").data('formkey');
		if($(srcSelect).closest("form").find("select[name='"+target+"']").length>0) {
			ajxURL=_service("forms","autocomplete","select")+"&refid="+$(srcSelect).val()+"&srcname="+name+"&formid="+formKey;
		} else {
			ajxURL=_service("forms","autocomplete")+"&refid="+$(srcSelect).val()+"&srcname="+name+"&formid="+formKey;
		}
	}
	if(ajxURL!=null && ajxURL.length>0) {
		if($("*[name='"+target+"']").is("select")) {
			$(srcSelect).closest("form").find("*[name='"+target+"']").load(ajxURL,function(ans) {
				noOpts=$(this).attr("no-options");
				$(this).prepend("<option value=''>"+noOpts+"</option>");
				$(this).val($(this).data("value"));
				
				if($(this).data("selectpicker")!=null) {
					try {
						$(this).selectpicker("refresh");
					} catch(e) {}
				}
				if($(this).hasClass("ajaxchain")) {
					loadAjaxChain(this);
				}
			});
		} else {
			$(srcSelect).closest("form").find("*[name='"+target+"']").load(ajxURL,function(ans) {
				//$(this).val(ans.Data);
				try {
					ans=$.parseJSON(ans);
					keys=Object.keys(ans.Data);
					$(this).val(ans.Data[keys[0]]);
					$(this).html("");
				} catch(e) {
					
				}
			});
		}
	}
}
function loadDropSearch(srcSelect) {
	$(srcSelect).data("show-subtext",true);
	$(srcSelect).data("live-search",true);

	name=$(srcSelect).attr("name");
	formKey=$(srcSelect).closest("form").data('formkey');
	
	val=$(srcSelect).data('value');
	
	var options = {
			values: "a, b, c",
			ajax: {
				url: _service("forms","dropsearch")+"&srcname="+name+"&formid="+formKey,
				type: "POST",
				dataType: "json",
				// Use "{{{q}}}" as a placeholder and Ajax Bootstrap Select will automatically replace it with the value of the search query.
				data: {
					q: "{{{q}}}"
				}
			},
			locale: {
				emptyTitle: "Select and Begin Typing"
			},
			log: 3,
			preprocessData: function(data) {
				fData=[];
				$.each(data.Data,function(k,v) {
					fData.push({
							text:v.title,
							value:v.value,
							data: {
								subtext: v.value
							}
						});
				});
				return fData;
			}
		};

	if(typeof $.fn.sortable != "selectpicker") {
		$(srcSelect).load(_service("forms","ajaxdropdown","select")+"&srcname="+name+"&formid="+formKey);
		return;
	}
	
	if(val!=null && (""+val).length>0) {
		processAJAXPostQuery(_service("forms","dropsearch")+"&srcname="+name+"&formid="+formKey,"&v="+val,function(data) {
			if(data.Data!=null && data.Data[0]!=null) {
				noOpt=$(srcSelect).attr("no-options")
				$(srcSelect).html("<option value='"+data.Data[0].value+"' selectted>"+data.Data[0].title+"</option>");
			}
			
			$(srcSelect).selectpicker({
					//style: 'btn-info'
				}).ajaxSelectPicker(options);
		   $(srcSelect).trigger("change");
			
		},"json");
	} else {
		$(srcSelect).selectpicker({
				//style: 'btn-info'
			}).ajaxSelectPicker(options);
	   $(srcSelect).trigger("change");
	}
	
}
function slugify(text) {
  return text.toString().toLowerCase()
    .replace(/\s+/g, '-')           // Replace spaces with -
    .replace(/[^\w\-]+/g, '')       // Remove all non-word chars
    .replace(/\-\-+/g, '-')         // Replace multiple - with single -
    .replace(/^-+/, '')             // Trim - from start of text
    .replace(/-+$/, '');            // Trim - from end of text
}
function getFAClass(f) {
	if(f==null || f.length<=0) return "";

	ext=f.split(".");
	ext=ext[ext.length-1];

	if(ext==null || ext.length<=0) return "";

	switch(ext.toLowerCase()) {
		case "png":case "gif":case "jpg":case "jpeg":case "bmp":
			return "fa-file-image-o";
			break;
		case "mp3":case "ogg":case "wav":case "aiff":case "wma":
			return "fa-file-audio-o";
			break;
		case "mp4":case "mpeg":case "mpg":case "avi":case "mov":case "wmv":
			return "fa-file-video-o";
			break;
		case "doc":case "txt":case "rdf":case "odt":
			return "fa-file-word-o";
			break;
		case "xls":case "ods":
			return "fa-file-excel-o";
			break;
		case "zip":case "tar":case "bz":case "bz2":case "gz":case "rar":case "zip":
			return "fa-file-zip-o";
			break;
		case "pdf":
			return "fa-file-pdf-o";
			break;
		case "php":case "html":case "js":case "css":case "java":case "py":case "c":case "cpp":case "sql":
			return "fa-file-code-o";
			break;
		default:
			return "fa-file";
	}
}
