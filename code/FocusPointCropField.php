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
    * These options are fed directly to the js cropper (options: https://github.com/fengyuanchen/cropper/blob/v2.3.0/README.md#options)
     */
    protected static $default_options = array(
        'autoCropArea' => 1,
        'aspectRatio' => 1,
        'minContainerWidth' => 200,
        'minContainerHeight' => 200,
    );

    /**
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options) {
        $defaults = static::$default_options;
        $this->options = array_merge($defaults, $options);
        return $this;
    }
    /**
     * @return array
     */
    public function getOptions() {
        return $this->options;
    }
    /**
     * @return string
     */
    public function getOption($name) {
        return $this->options[$name];
    }
    /**
     * Set an option after initialisation
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function setOption($name, $value) {
        $this->options[$name] = $value;
        return $this;
    }
    
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
        $this->setOptions(array()); // sets options
        $this->push( $field = TextField::create('CropData') );

        // feed config to js
        $field->setAttribute('data-config',json_encode($this->getOptions()));

    }
}
