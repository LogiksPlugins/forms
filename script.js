$(function() {
	$("form select.selectAJAX").each(function() {

	});

	$("form select.multiple").each(function() {

	});

	//Chain selectors

	

	//$("form.validate").valid();
	
	$("form.validate").validate({
		  //debug:true,
		  //ignore: ".ignore",
		  //errorClass: "error",
		  //validClass: "success",
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
		    //$(form).ajaxSubmit();
		    //form.submit();
		    console.log("FORM SUBMIT");
		  },
		  invalidHandler: function(event, validator) {
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