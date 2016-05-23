<?php

if (!defined('FOCUSPOINTCROP_DIR')) {
    define('FOCUSPOINTCROP_DIR', basename(dirname(__FILE__)));
}

// remove original FocusPoint extension
Image::remove_extension('FocusPointImage');