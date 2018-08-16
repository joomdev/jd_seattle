(function($){
	$.G2.signature_pad = {};
	
	$.G2.signature_pad.ready = function(){
		$("canvas[data-signature]").each(function(pi, pad){
			var wrapper = $(pad).closest(".m-signature-pad"),
				clearButton = wrapper.find("[data-action=clear]"),
				saveButton = wrapper.find("[data-action=save]"),
				canvas = wrapper.find("canvas").get(0),
				signaturePad;
			
			function resizeCanvas() {
				//var ratio =  Math.max(window.devicePixelRatio || 1, 1);
				
				var parentWidth = $(canvas).parent().innerWidth();
				if(parentWidth){
					canvas.width = $(canvas).parent().innerWidth();
				}
				//canvas.width = canvas.offsetWidth * ratio;
				//canvas.height = canvas.offsetHeight * ratio;
				//canvas.getContext("2d").scale(ratio, ratio);
				canvas.getContext("2d").scale(1, 1);
			}
			
			window.onresize = resizeCanvas;
			resizeCanvas();
			
			signaturePad = new SignaturePad(canvas, {
				"onEnd": function(){
					wrapper.find("input[type=hidden]").val(signaturePad.toDataURL());
				},
			});
			
			clearButton.on("click", function (event) {
				signaturePad.clear();
				wrapper.find("input[type=hidden]").val('');
			});
			
			if(wrapper.find("input[type=hidden]").val()){
				signaturePad.fromDataURL(wrapper.find("input[type=hidden]").val());
			}
		});
	};
	
}(jQuery));