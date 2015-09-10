#!/usr/bin/php
<?php
#
#	Main init script
#
#
#

require 'RLLT2300Parser.php';
require 'RLLTApplication.php';

function init($argv = array()){

    $parser = new RLLT2300Parser();
    
    $settings = parse_ini_file('settings.ini', true);
    $parser->settings = $settings;

    if (!empty($argv[1]))
        $parser->filename = $argv[1];

    if (!$parser->filename)
        die("File not set!\n");

    if (!file_exists($parser->filename))
        die("File {$parser->filename} not exists!\n");

    $parser->parse($parser->filename);

    $src_file_name = end(explode(DIRECTORY_SEPARATOR, $parser->filename));
    $app = new RLLTApplication();
    $app->set(array(
        'md5sum' => md5_file($parser->filename),
        'data' => $parser->data,
        'settings' => $settings,
        'file_name' => $src_file_name,
        'file_date' => $parser->current_time
        ));
    
    $page =  $app->page();
    
    file_put_contents('html' . DIRECTORY_SEPARATOR . $src_file_name . '.html', $page);
 
}


init($_SERVER['argv']);