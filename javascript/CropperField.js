(function($) {
	$.entwine('ss', function($){

		$('#FocusPoint .focuspoint-field .grid').entwine({
			
			getCropField: function(){
				return this.parents('.fieldgroup')
					.find('input[name="CropData"]')
			},

			onadd: function() {
				this._super();
				// Position focus grid on form field
				var self = this;
				var crop_img = self.prev('img');
				var config = JSON.parse(this.getCropField().attr('data-cropconfig'));

				var sizes = JSON.parse(this.getCropField().attr('data-cropsizing'));

				crop_img.cropper( config )
					.on('built.cropper',function(){
						// Set up some vars (this = image)
						var move_el = $(this).next('.cropper-container').find('.cropper-move');
						var downX, downY;

						// load existing data (if any)
						try {
							var existing_crop = JSON.parse(self.getCropField().val());
							// crop_img.cropper('setCropBoxData', existing_crop);
							crop_img.cropper('setData', existing_crop);
						} catch (e) {}

						// move in the right order to have cropper (needs drag) on top of focusfield (needs click)
						$(this).nextAll('.grid').insertAfter($(this).next('.cropper-container').find('.cropper-view-box'));

						// keep track of mousdown start x/y (to check click or drag)
						move_el.mousedown(function(e) {
							downX = e.pageX;
							downY = e.pageY;
							// console.log("DownX: "+downX+" DownY: "+downY);
						});

						$(document).mouseup(function(e){
							// Forward to focuspointfield if click (not drag)
							// console.log("PageX: "+downX+" PageY: "+downY);
							if (Math.abs(downX - e.pageX)<5 && Math.abs(downY - e.pageY)<5) {
								//console.log('Click');
								var event = $.Event('click');
								event.pageX = e.pageX;
								event.pageY = e.pageY;
								move_el.prevAll('.grid').trigger(event);
							// } else { // eg drag/update
							// 	console.log('Drag');
							}
						});
					})
					.on('crop.cropper', function(e){
						// console.log(JSON.stringify($(this).cropper('getData',true)));
						// console.log(JSON.stringify($(this).cropper('getImageData')));
						// console.log(JSON.stringify($(this).cropper('getCropBoxData', true)));

						// {"x":78,"y":0,"width":267,"height":267,"rotate":0,"scaleX":1,"scaleY":1}
						var rounddata = $(this).cropper('getData', true); // get rounded data

						// make sizes relative to original image (we're using a preview image):
						// {originalWidth: 216, originalHeight: 144, previewWidth: 400, previewHeight: 267}
						var scale = sizes.originalWidth / sizes.previewWidth;

						rounddata.originalX = Math.round(rounddata.x * scale);
						rounddata.originalY = Math.round(rounddata.y * scale);
						rounddata.originalWidth = Math.round(rounddata.width * scale);
						rounddata.originalHeight = Math.round(rounddata.height * scale);

						self.getCropField().val(JSON.stringify(rounddata));
					});
			}

		});
	});
}(jQuery));
