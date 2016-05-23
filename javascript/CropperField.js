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
				self = this;
				crop_img = self.prev('img');
				config = JSON.parse(this.getCropField().attr('data-config'));

				crop_img.cropper( config )
					.on('built.cropper',function(){
						// Set up some vars (this = image)
						var move_el = $(this).next('.cropper-container').find('.cropper-move');
						var downX, downY;

						// load existing data (if any)
						try {
							var existing_crop = JSON.parse(self.getCropField().val());
							crop_img.cropper('setCropBoxData', existing_crop);
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
						var rounddata = $(this).cropper('getCropBoxData'); // get rounded data
						// console.log(JSON.stringify($(this).cropper('getData',true)));
						// console.log(JSON.stringify($(this).cropper('getImageData')));
						// console.log(JSON.stringify($(this).cropper('getCropBoxData')));
						self.getCropField().val(JSON.stringify(rounddata));
					});
			}

		});
	});
}(jQuery));
