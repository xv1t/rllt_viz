<?php

function debug($var, $html = false){
    echo $html ? '<pre>' : null;
    var_dump($var);
    echo $html ? "</pre>\n" : null;
}

class RLLT2300Parser {
    var $filename = false;
    var $current_time;
    var $data, $txtdata;
    var $settings = array();
    var $cols = array();
    var $txt_filetime_str, $txt_filetime_int;
    
    private function str_clear($str){
        return rtrim(ltrim($str));
    }
    
    public function txt2date($str){
        return implode('-',array_reverse( explode('.', $str ) ));
    }
    
    /**
     * Drop double spaces from string
     * @param type $str
     * @return type
     */
    private function trim2($str){
        while(strpos($str, '  '))
                $str = str_replace ('  ', ' ', $str);
        
        return $str;
    }
    
    public function parse($filename = null){
        $content = file_get_contents($filename);
        
        /*
         * Check empty table
         */
        
        $this->data  = $data = array();
        
        if (strpos($content, '| Liste enth')){
            return false;
        }
        
	$lines = explode("\n", $content);
        
        $start_table = false;
        foreach ($lines as $line){
            
            if ($start_table) {
                if (strpos($line, '|') === 0){
                    $row0 = explode('|', $line);
                    array_shift($row0);
                    array_pop($row0);
                    
                    $row = array();
                    
                    for ($i=0; $i < count($row0); $i++)
                        $row[ $this->cols[$i] ] = $this->str_clear($row0[$i]);
                    
                    $txtdata[] = $row;                  
                }
            }  else {
                if (strpos($line, 'Datum') !== false && strpos($line, 'Uhrzeit') ){
                    $line = rtrim( ltrim( $this->trim2($line)) );
                    
                    $a = explode(' ', $line);
                    
                    $this->txt_filetime_str = $this->txt2date($a[2]) . ' ' . $a[4];
                    $this->txt_filetime_int = strtotime($this->txt_filetime_str);
                }
            }
            
            if (strpos($line, '|TA-Nummer') !== false){
                $start_table = true;
                
                $this->cols = $this->fields($line);

                //debug($this->cols);
            }
        }
        
        $this->data = $this->analyze($txtdata);
    }
        
    public function analyze($data){
        $_data = array();
        
        $local_time_values = explode(',', $this->settings['time']['local_time_values']);
        $timezone_diff_hours = empty($this->settings['time']['timezone_diff_hours']) ? 2 : $this->settings['time']['timezone_diff_hours'];
        $success_max_hours = $this->settings['time']['success_max_hours'];
        
        if ($this->settings['time']['current'] == 'os')
        {
            $compare_datetime_str = date('Y-m-d H:i:s');
            $compare_datetime_int = strtotime($compare_datetime_str);
        }
        
        if ($this->settings['time']['current'] == 'file')
        {
            $compare_datetime_int = $this->txt_filetime_int + 3600 * $timezone_diff_hours;
            $compare_datetime_str = date('Y-m-d H:i:s', $compare_datetime_int);
            //$this->current_time = $compare_datetime_str;
        }
        
        $this->current_time = $compare_datetime_str;
        
        foreach ($data as $txtdatum){
            
            $txtdatum['datetime_str'] = $this->txt2date( $txtdatum['Erst.dat'])
					. ' ' .$txtdatum['Uhrzeit'];
            
            $txtdatum['datetime_int'] = strtotime($txtdatum['datetime_str']);
            
            
            
            $rllt_item = array(
                'compare_datetime_str' => $compare_datetime_str,
                'compare_datetime_int' => $compare_datetime_int,
                'local_time_zone' => in_array($txtdatum['Typ2'], $local_time_values) ? 1: 0,
            );
            
            $hours = $timezone_diff_hours;
            
            if ($rllt_item['local_time_zone'])
                $hours = 0;
            
            
            
            $rllt_item['datetime_int'] = $txtdatum['datetime_int'] + 3600 * $hours;
            $rllt_item['datetime_str'] = date('Y-m-d H:i:s', $rllt_item['datetime_int']);
            
            $rllt_item['success'] = 
                    $compare_datetime_int - $success_max_hours * 3600 < $rllt_item['datetime_int']
                        ? 1
                        : 0;
            
            foreach ($this->settings['fields'] as $field => $txt_field){
                $rllt_item[$field] = $txtdatum[$txt_field];
            }
            
            $item = array(
                'TxtDatum' => $txtdatum,
                'RlltDatum' => $rllt_item
            );
            
            $_data[] = $item;
            
        }
        return $_data;
    }    
    
    private function fields($line){
        $c0 = explode('|', $line);
        array_shift($c0);
        array_pop($c0);

        $cols = array();
        
        $Typ = false;
        for ($i=0; $i<count($c0); $i++)
        {
            $_col = $this->str_clear($c0[$i]);
            
            if ($_col == 'Typ' && $Typ)
                $_col = 'Typ2';
            
            if ($_col == 'Typ' && !$Typ)
            {
                $_col = 'Typ1';
                $Typ = true;
            }
            
            $cols[] = $_col;
        }
        
        return $cols;
    }


    public function read($filename = null){
        
        if (!$filename)
            $filename = $this->filename;
        
        if (!$filename)
            return false;
        
        $content = file_get_contents($filename);
		$lines = explode("\n", $content);
		//if (strpos('Liste ent', $content) === false){
	//		$lines = array();
	//	} else {
			
			$lines = explode("\n", $content);
	//	}
        
        
		
        
        $start_table = false;
  
        $cols = array();
        $txtdata = array();
        
        foreach ($lines as $line){
            
            if ($start_table){
                if (strpos($line, '|') === 0){
                    $row0 = explode('|', $line);
                    array_shift($row0);
                    array_pop($row0);
                    
                    $row = array();
                    
                    for ($i=0; $i < count($row0); $i++)
                        $row[ $cols[$i] ] = $this->str_clear($row0[$i]);
                    
                    $txtdata[] = $row;                  
                }
            } else {
                if (strpos($line, 'Datum') !== false && strpos($line, 'Uhrzeit') ){
                    $line = rtrim( ltrim( $this->trim2($line)) );
                    
                    $a = explode(' ', $line);
                    
                    $this->current_time = $this->txt2date($a[2]) . ' ' . $a[4];
                }
            }
            
            if (strpos($line, '|TA-Nummer') !== false){
                $start_table = true;
                
                $c0 = explode('|', $line);
                array_shift($c0);
                array_pop($c0);
                
                for ($i=0; $i<count($c0); $i++)
                    $cols[] = $this->str_clear($c0[$i]);

            }
        }
        
        $data = array();
        
				if ($this->settings['time']['current'] == 'file')
					$current = strtotime($this->current_time);
				
				if ($this->settings['time']['current'] == 'os')
					$current = strtotime(date("Y-m-d H:i:s"));        
        		
        if ($txtdata){
            foreach ($txtdata as $txtdatum){
                
                
				$ctime_str = $this->txt2date( $txtdatum['Erst.dat'])
					. ' ' .$txtdatum['Uhrzeit'];
				
				
				if ($txtdatum['Typ'] == $this->settings['time']['smr_filter_typ']){
					//$success = ($current <= strtotime($ctime));
					$ctime = strtotime($ctime_str);
					//debug('SMR');
				} else {
					$ctime = strtotime($ctime_str) + 7200;
				}
				
				//$ctime = $this->txt2date( $txtdatum['Erst.dat'])
                 //  . ' ' .$txtdatum['Uhrzeit'];
                
                $success = ($current <= strtotime($ctime) + 3600);
				
                
                $data[] = array(
                 //   'ctime' => $ctime,
                   // 'current' => $current,
                   // 'time' => date('Y-m-d H:i:s', $ctime),
                    'TO number'         => $txtdatum['TA-Nummer'],
                    'TO position'       => $txtdatum['Pos.'],
                    //'Source Stype'      => $txtdatum['Typ'],
	            'Destination Stype'  => $txtdatum['Typ'],
                    'Source Sbin'       => $txtdatum['Vonplatz'],
                    'Destination Sbin'  => $txtdatum['Nachplatz'],
                    'Quantity'          => $txtdatum['Sollmenge Nach'],
                    'Unit of mesure'    => $txtdatum['BME'],
                    'Material' => $txtdatum['Material'],
                    'Status' => $success
                            ? 'on time'
                            : 'delay'
                );
            }
        }
        
        $data = $this->analyze($data);
        
        $data = $this->sort($data, 'time');
        
        $this->data = $data;
        $this->txtdata = $txtdata;
        
        //debug(compact('txtdata', 'data'));
        //debug($this->current_time);
        
       // print_r($lines);
    }

    
    public function sort($data, $field){
        /*
         * create list array
         */
        
        $list = array();
        foreach ($data as $key => $row)
        {
            $list[$key] = $row[$field];
        }
        array_multisort($list, SORT_DESC, $data);
        return $data;
    }
}
