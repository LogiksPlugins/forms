//Camera functionality

var FORM_CAMERA = {
	CAMERA_LIST: [],
	initialize: function(cameraClass) {
		if(cameraClass==null) cameraClass = ".camera_div";

		$(cameraClass).each(function() {
			FORM_CAMERA.start(this);
		});
	},

	start: function(cameraBox, params) {
		if($(cameraBox).find(".camera_box").length>0) return;

		params = $.extend({
				"width": 640,
				"height": 480,
				"single": true
			}, params);

		var randKey = "CAM"+Math.ceil(Math.random()*10000000);
		
		$(cameraBox).attr("data-cameraid", randKey);
		$(cameraBox).html(`<div class='camera_box' data-refid='CAMBOX${randKey}'>
					<div class='errorMsg hidden'></div>
					<video id='video${randKey}' playsinline autoplay width='${params.width}' height='${params.height}'></video>
					<canvas id='canvase${randKey}' class='camera_canvas hidden' width='${params.width}' height='${params.height}'></canvas>
					<div align=center class='text-center' style='margin-top: 5px;'>
						<button class='btn btn-default btn-reset hidden' onclick='FORM_CAMERA.reset_image(this)'><i class='fa fa-refresh'></i> Reset</button>
						<button class='btn btn-default btn-capture' onclick='FORM_CAMERA.capture_image(this)'><i class='fa fa-camera'></i> Capture</button>
					</div></div>`);

		this.CAMERA_LIST.push(cameraBox);

		if (navigator.mediaDevices!=null && navigator.mediaDevices.getUserMedia) {
		  navigator.mediaDevices.getUserMedia({ video: true })
		    .then(function (stream) {
		      $(cameraBox).find(`video`)[0].srcObject = stream;
		    })
		    .catch(function (error) {
		    	console.log(error);
		      	console.log("Something went wrong!");
		    });
		} else {
			$(cameraBox).html("<h4 align=center><br>Error Starting Camera, Media not available</h4>");
		}
	},

	stop: function(btn) {
		if(btn==null) return false;
		var currentCameraDiv = $(btn).closest(".camera_box").parent();

		var video = $(currentCameraDiv).find("video")[0];
		var stream = video.srcObject;
		var tracks = stream.getTracks();

		for (var i = 0; i < tracks.length; i++) {
		    var track = tracks[i];
		    track.stop();
		}

		video.srcObject = null;
	},

	reset_image: function(btn) {
		if(btn==null) return false;
		var currentCameraDiv = $(btn).closest(".camera_box").parent();
		$(currentCameraDiv).html("");
		FORM_CAMERA.start(currentCameraDiv);
	},

	capture_image: function(btn) {
		if(btn==null) return false;
		var currentCameraDiv = $(btn).closest(".camera_box").parent();

		var video = $(currentCameraDiv).find("video")[0];
		var canvas = $(currentCameraDiv).find("canvas")[0];
		var context = canvas.getContext('2d');
		context.drawImage(video, 0, 0, 640, 480);

		this.stop(btn);
		$(currentCameraDiv).find("video").detach();
		$(currentCameraDiv).find("canvas").removeClass("hidden");

		$(currentCameraDiv).find(".btn-capture").addClass("hidden");
		$(currentCameraDiv).find(".btn-reset").removeClass("hidden");
	}
};