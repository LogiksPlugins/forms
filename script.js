$(function() {
	$("form select.selectAJAX").each(function() {

	});

	$("form select.multiple").each(function() {
		
	});

	//Chain selectors
	$("form select.chainSelector").each(function() {
		
	});
	

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
});
function formsSubmitStatus(formid,msgObj,msgType) {
	if(msgType==null) msgType="SUCCESS";
	else msgType=msgType.toUpperCase();

	//console.warn(msgObj);

	if($("form[data-formkey='"+formid+"']").length>0) {
		formBox=$("form[data-formkey='"+formid+"']");
		switch(msgType) {
			case "ERROR":
				lgksToast("<i class='glyphicon glyphicon-ban-circle'></i>&nbsp;"+msgObj);
			break;
			case "INFO":
				lgksToast("<i class='glyphicon glyphicon-comment'></i>&nbsp;"+msgObj);
			break;
			case "SUCCESS":
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
				lgksToast("<i class='glyphicon glyphicon-comment'></i>&nbsp;Successfully update database.");
			break;
			default:
				lgksToast("<i class='glyphicon glyphicon-info-sign'></i>&nbsp;"+msgObj);
		}
		formBox.parent().find(".ajaxloading").detach();
		formBox.show();

		postsubmit=formBox.data('postsubmit');
		if(postsubmit!=null && typeof window['postsubmit']=="function") {
			window['postsubmit'](formid,msgObj,msgType);
		}
	} else {
		console.warn(formid+">>"+msgType+">>"+msgObj);
	}
}