<?php

class RLLTApplication {
    var $globalVars = array();
    var $useTags = array();
    
    public function set($vars){
        $this->globalVars += $vars;
    }
    
    public function closeTag($tagName = null){
        if ($tagName)
            return "</$tagName>";
        
        if ($this->useTags)
            return "</" .  array_pop($this->useTags) . ">";
        
    }
    
    public function tag($tagName, $content = null, $options = array()){
        $html = '<' . $tagName;
        
        if ($options){
            
            foreach ($options as $attr => $attr_val){
                if (is_string($attr_val))
                    $html .= " $attr=\"$attr_val\"";
                
                if (is_array($attr_val)){
                    $html .= " $attr=\"" . implode(' ', $attr_val) . "\"";
                }
                
                if ($attr_val === true)
                    $html .= " $attr";
            }
        }
        
        if ($content){
            $html .= '>' . $content . "</$tagName>";
        } else {
            $this->useTags[] = $tagName;
            $html .= '>';
        }
        return $html;
    }
    
    public function element($element_name, $vars = array(), $options = array()){
        $element_file_name = 'php' . DIRECTORY_SEPARATOR .  'elements' . DIRECTORY_SEPARATOR . $element_name . '.ctp';
        if (file_exists($element_file_name)){
            extract($this->globalVars + $vars);

            ob_start();
            include $element_file_name;
            return ob_get_clean();
        }
    }
    
    public function page($vars = array()){
        $layout_file =  'php' . DIRECTORY_SEPARATOR . 'elements' . DIRECTORY_SEPARATOR . 'layout.ctp';
        if (file_exists($layout_file)){
            return $this->element('layout', $vars);
        } else echo "File: $layout_file not exists";
    }
}
