#!/usr/bin/php
<?php
require 'RLLT2300Parser.php';

$parser = new RLLT2300Parser();

if (!empty($_SERVER['argv'][1]))
    $parser->filename = $_SERVER['argv'][1];

if (!$parser->filename)
    die("File not set!\n");

if (!file_exists($parser->filename))
    die("File {$parser->filename} not exists!\n");

$parser->read();

file_put_contents('tmp/data.json', json_encode($parser->data));

