<?php

/**
 * FocusPoint Image extension.
 * Extends Image to allow automatic cropping from a selected focus point.
 *
 * @extends DataExtension
 */
class FocusPointCropImage extends FocusPointImage
{
    /**
     * Field to hold cropdata
     */
    private static $db = array(
        'CropData' => 'Varchar(255)', //stores json
        // these need to be re-added because of being private in the parent class (which extension got removed).
        'FocusX' => 'Double',
        'FocusY' => 'Double',
    );

    /**
     * Preserve default behaviour of cropping from center (re-define because of being private to parent)
     */
    private static $defaults = array(
        'FocusX' => '0',
        'FocusY' => '0',
    );


    /**
     * Add FocusPoint field for selecting focus.
     */
    public function updateCMSFields(FieldList $fields)
    {
        $f = new FocusPointCropField($this->owner);
        if ($fields->hasTabSet()) {
            $fields->addFieldToTab('Root.Main', $f);
        } else {
            $fields->add($f);
        }
    }

    public function onBeforeWrite()
    {
        if ( Config::inst()->get(get_parent_class(), 'flush_on_change') && $this->owner->isChanged('CropData') )
        {
            $this->owner->deleteFormattedImages();
        }
        parent::onBeforeWrite();
    }
    
    /**
     * Generate a resized copy of this image with the given width & height,
     * cropping to maintain aspect ratio and focus point. Use in templates with
     * $CroppedFocusedImage.
     *
     * @param int  $width   Width to crop to
     * @param int  $height  Height to crop to
     * @param bool $upscale Will prevent upscaling if set to false
     *
     * @return Image|null
     */
    public function CroppedFocusedImage($width, $height, $upscale = true)
    {
        // get reference to original image
        $img = $this->owner;

        // Crop first (once, on following actions CropData will be non-existant)
        $cropData = json_decode($img->CropData);
        // json - {"left":31,"top":31,"width":169,"height":169}
        if ($cropData && $cropData->width != $img->width && $cropData->height != $img->height)
        {
            $cropped_img = $this->owner->CroppedOffsetImage(
                $cropData->left, $cropData->top,
                $cropData->width, $cropData->height
            );
            //var_dump($img->width, $cropData->width);
            // Update FocusPoint (FocusX/Y = relative to original image, make relative to new image)
            $cropped_img->FocusX = $img->FocusX * ($cropData->width / $img->width);
            $cropped_img->FocusY = $img->FocusY * ($cropData->height / $img->height);
            // and recurse
            return $cropped_img->CroppedFocusedImage($width, $height, $upscale);
        }

        // delegate to FocusPointImage class
        return parent::CroppedFocusedImage($width, $height, $upscale);
        
    }

    public function CroppedOffsetImage($offsetX, $offsetY, $width, $height)
    {
        return $this->owner->getFormattedImage(__FUNCTION__, $offsetX, $offsetY, $width, $height);
    }

    public function generateCroppedOffsetImage(Image_Backend $backend, $offsetX, $offsetY, $width, $height)
    {
        return $backend->crop($offsetX, $offsetY, $width, $height);
    }
    
}
