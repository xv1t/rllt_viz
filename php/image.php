#!/usr/bin/php
<?php

require 'RLLT2300Parser.php';
require 'RLLTApplication.php';
require 'RLLTImage.php';


$rllt_image = new RLLTImage();

$settings = parse_ini_file('settings.ini', true);
    $rllt_image->settings = $settings;

$rllt_image->testdraw("/home/vt/tmp/test.jpg");
//color(1, 2);

