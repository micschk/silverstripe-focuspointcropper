<?php

/**
 * FocusPoint Image extension.
 * Extends Image to allow automatic cropping from a selected focus point.
 *
 * @extends DataExtension
 */
class FocusPointCropImage extends FocusPointImage {
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
    public function updateCMSFields(FieldList $fields) {
        $f = new FocusPointCropField($this->owner);
        if ($fields->hasTabSet()) {
            $fields->addFieldToTab('Root.Main', $f);
        } else {
            $fields->add($f);
        }
    }

    public function onBeforeWrite() {
        if (Config::inst()->get(get_parent_class(), 'flush_on_change') && $this->owner->isChanged('CropData')) {
            $this->owner->deleteFormattedImages();
        }
        parent::onBeforeWrite();
    }

    /**
     * Return a resized copy of this image with the given width & height,
     * cropping to maintain aspect ratio and focus point. Use in templates with
     * $CroppedFocusedImage.
     *
     * @param int $width Width to crop to
     * @param int $height Height to crop to
     * @param bool $upscale Will prevent upscaling if set to false
     *
     * @return Image|null
     */
    public function CroppedFocusedImage($width, $height, $upscale = true) {
        if ($cropped_img = $this->generateImage(true)) {
            return $cropped_img->CroppedFocusedImage($width, $height, filter_var($upscale, FILTER_VALIDATE_BOOLEAN));
        }
        // delegate to FocusPointImage class
        return parent::CroppedFocusedImage($width, $height, filter_var($upscale, FILTER_VALIDATE_BOOLEAN));
    }

    /**
     * Crop the image and possibly use focus point.
     *
     * @param bool $focused Will use focus point if true.
     * @return Image|null
     */
    private function generateImage($focused = true) {
        // get reference to original image
        $img = $this->owner;
        // Crop first (once, on following actions CropData will be non-existant)
        $cropData = json_decode($img->CropData);
        // json - {"left":31,"top":31,"width":169,"height":169}
        // json - { ["x"]=> int(89) ["y"]=> int(0) ["width"]=> int(192) ["height"]=> int(192) ["rotate"]=> int(0) ["scaleX"]=> int(1) ["scaleY"]=> int(1) ["originalX"]=> float(48.06) ["originalY"]=> int(0) ["originalWidth"]=> float(103.68) ["originalHeight"]=> float(103.68) }
//        print '<!-- ' . print_r($cropData, true) . ' -->';
        if (
            $cropData // If we have data and the properties we need are defined
            && property_exists($cropData, 'originalX') && property_exists($cropData, 'originalY')
            && property_exists($cropData, 'originalWidth') && property_exists($cropData, 'originalHeight')
            // AND at least width or height is different from original
            && ($cropData->originalWidth != $img->width || $cropData->originalHeight != $img->height)
        ) {
            $cropped_img = $this->owner->CroppedOffsetImage(
                (int)$cropData->originalX, (int)$cropData->originalY,
                (int)$cropData->originalWidth, (int)$cropData->originalHeight
            );

            if ($focused) {
                // Update FocusPoint (FocusX/Y = relative to original image, make relative to new image)
                $cropped_img->FocusX = $img->FocusX * ($cropData->originalWidth / $img->width);
                $cropped_img->FocusY = $img->FocusY * ($cropData->originalHeight / $img->height);
                $cropped_img->CropData = null; // unset so we offset-crop only once
            }

            return $cropped_img;
        }
    }

    /**
     * Return a cropped version of the image if CropData is available.
     * Without CropData the original image will be returned.
     *
     * @return Image
     */
    public function CroppedImageOnly() {
        if ($croppedImage = $this->generateImage(false)) {
            return $croppedImage;
        } else {
            return $this->owner;
        }
    }

    public function CroppedOffsetImage($offsetX, $offsetY, $width, $height) {
//        var_dump($offsetX, $offsetY, $width, $height);
        return $this->owner->getFormattedImage(__FUNCTION__, $offsetX, $offsetY, $width, $height);
    }

    public function generateCroppedOffsetImage(Image_Backend $backend, $offsetX, $offsetY, $width, $height) {
        // ATTENTION! GD_Backend::crop wants TOP/y as first argument (instead of x)
        return $backend->crop($offsetY, $offsetX, $width, $height);
    }

}
