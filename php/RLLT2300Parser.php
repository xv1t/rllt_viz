<?php

function debug($var){
    var_dump($var);
    echo "\n";
}

class RLLT2300Parser {
    var $filename = false;
    var $current_time;
    
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
    
    public function read($filename = null){
        
        if (!$filename)
            $filename = $this->filename;
        
        if (!$filename)
            return false;
        
        $content = file_get_contents($filename);
        
        $lines = explode("\n", $content);
        
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
        
        $current = strtotime($this->current_time);
        if ($txtdata){
            foreach ($txtdata as $txtdatum){
                
                $ctime = $this->txt2date( $txtdatum['Erst.dat'])
                    . ' ' .$txtdatum['Uhrzeit'];
                
                $success = $current <= strtotime($ctime) + 3600;
                
                $data[] = array(
                    'ctime' =>$ctime,                    
                    'TO number'    => $txtdatum['TA-Nummer'],
                    'TO position'  => $txtdatum['Pos.'],
                    'Source Stype' => $txtdatum['Typ'],
                    'Source Sbin'  => $txtdatum['Vonplatz'],
                    'Destination Sbin'  => $txtdatum['Nachplatz'],
                    'Quantity'  => $txtdatum['Sollmenge Nach'],
                    'Unit of mesure'  => $txtdatum['BME'],
                    'Status' => $success
                            ? 'on time'
                            : 'delay'
                );
            }
        }
        
        
        debug(compact('txtdata', 'data'));
        debug($this->current_time);
        
       // print_r($lines);
    }
}
