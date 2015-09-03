#!/usr/bin/php
<?php
require 'RLLT2300Parser.php';

function init($argv = array()){

    $parser = new RLLT2300Parser();

    if (!empty($argv[1]))
        $parser->filename = $argv[1];

    if (!$parser->filename)
        die("File not set!\n");

    if (!file_exists($parser->filename))
        die("File {$parser->filename} not exists!\n");

    $parser->read();

    debug($parser->data);
    
    //file_put_contents('tmp/data.json', json_encode($parser->data));    
}


init($_SERVER['argv']);