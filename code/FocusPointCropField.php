<?php

/**
 * FocusPointCropField class.
 * Facilitates the selection of a crop area of an image.
 *
 * @extends FocusPointField
 */
class FocusPointCropField extends FocusPointField
{
    /**
     * @Config
     * These options are fed directly to the js cropper (options: https://github.com/fengyuanchen/cropper/blob/v2.3.0/README.md#options)
     */
    private static $cropconfig = array(
        'autoCropArea' => 1,
        //'aspectRatio' => 1,
    );

    public function __construct(Image $image)
    {
        // call FocusPointField
        parent::__construct($image);
        
        // Cropper
        Requirements::css(FOCUSPOINTCROP_DIR.'/bower_components/cropper/dist/cropper.css');
        Requirements::javascript(FOCUSPOINTCROP_DIR.'/bower_components/cropper/dist/cropper.js');
        Requirements::css(FOCUSPOINTCROP_DIR.'/css/CropperField.css');
        Requirements::javascript(FOCUSPOINTCROP_DIR.'/javascript/CropperField.js');

        // Add CropData field
//        $this->setOptions(array()); // sets default options
        $this->push( $field = TextField::create('CropData') );

        // Update field title & provide instructions
        $this->setTitle(_t('FC_Crop.FieldTitle', 'Crop & Focus Point'));
        $this->push( LiteralField::create( 'CropDescr', '<p class="cropper_instruction">'.
            _t('FC_Crop.Descr','Drag & resize the Cropping Area (blue rectangle) and click to select the Focus Point 
                                (main subject) of the image to ensure it is not lost during cropping') .'</p>' ) );

        // feed config to js
        $field->setAttribute('data-cropconfig', json_encode( $this->config()->cropconfig ));

        // feed some more info to js
        $sizes = array(
            'originalWidth' => $image->width,
            'originalHeight' => $image->height,
            'previewWidth' => $image->FocusPointFieldImage()->width,
            'previewHeight' => $image->FocusPointFieldImage()->height,
        );
        $field->setAttribute('data-cropsizing', json_encode($sizes));

    }
}
