#!/usr/bin/php
<?php
#
#	Main init script
#
#
#

require 'RLLT2300Parser.php';
require 'RLLTApplication.php';
require 'RLLTImage.php';

function init($argv = array()){



    $parser = new RLLT2300Parser();
    
    $settings = parse_ini_file('settings.ini', true);
    $parser->settings = $settings;

    if (!empty($argv[1]))
        $parser->filename = $argv[1];

    if (!$parser->filename)
	{ 
		print_r(compact('argv'));
		die("File not set!\n");
	}

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
    
    /*
     * Make HTML page file
     */
    
    if (isset($settings['visualization']['html_out']) && $settings['visualization']['html_out'] == 1) {
        $page =  $app->page();    
        file_put_contents('html' . DIRECTORY_SEPARATOR . $src_file_name . '.html', $page);
    }
    
    /*
     * Make JPG Image file
     */
    
   // debug($settings);
    if (isset($settings['visualization']['jpg_out']) && $settings['visualization']['jpg_out'] == 1) {
        
       
        
        $rllt_image = new RLLTImage();
        $rllt_image->size['width'] = $settings['image']['width'];
        $rllt_image->size['height'] = $settings['image']['height'];
        
        $rllt_image->draw_report1($parser->data, array(
            'current_time' => $parser->current_time,
            'settings' => $settings,
            'save_to_files' => array(
              //  "tmp/$src_file_name.jpg",
              //  "tmp/$src_file_name-1.jpg",
			  "C:\\Windows\\System32\\oobe\\info\\backgrounds\\$src_file_name.jpg",
			  "C:\\Windows\\System32\\oobe\\info\\backgrounds\\backgroundDefault.jpg",
            )
        ));
    }
 
}


init($_SERVER['argv']);