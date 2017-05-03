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

                // keep track of mousdown start x/y (to check click or drag)
                // these functions get triggered from cropper.js, with a proxied event object as argument
                var downX, downY;
                config.cropstart = function(proxyEvent){
                    // console.log(proxyEvent);
                    downX = proxyEvent.originalEvent.pageX;
                    downY = proxyEvent.originalEvent.pageY;
                    // console.log("DownX: " + downX + " DownY: " + downY);
                };
                config.cropend = function(proxyEvent){
                    // Forward to focuspointfield if click (not drag), account for accidental ~1px drag when clicking
                    // console.log("PageX: "+downX+" PageY: "+downY);
                    if (Math.abs(downX - proxyEvent.originalEvent.pageX)<=1 && Math.abs(downY - proxyEvent.originalEvent.pageY)<=1) {
                        var event = $.Event('click');
                        event.pageX = proxyEvent.originalEvent.pageX;
                        event.pageY = proxyEvent.originalEvent.pageY;
                        $(proxyEvent.originalEvent.target).prevAll('.grid').trigger(event);
                        // } else { // eg drag/update
                        // 	console.log('Drag');
                    }
                };

                crop_img.cropper( config )
                    .on('built.cropper',function(){

                        // load existing data (if any)
                        try {
                            var existing_crop = JSON.parse(self.getCropField().val());
                            // crop_img.cropper('setCropBoxData', existing_crop);
                            crop_img.cropper('setData', existing_crop);
                        } catch (e) {}

                        // move in the right order to have cropper (needs drag) on top of focusfield (needs click)
                        $(this).nextAll('.grid').insertAfter($(this).next('.cropper-container').find('.cropper-view-box'));

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
