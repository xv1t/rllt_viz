<?php 

echo empty($settings['vars']['header'])
    ? null
    : $this->tag(
            'h2', 
            $settings['vars']['header'] . ' ' . $this->tag('small', "$file_date")
            )
?>
<table class="table table-bordered table-hover table-striped">
    <thead>
        <tr>
        <?php 
        $fields = explode(',', $settings['table']['fields']);
        $fields_text_right = explode(',', $settings['table']['fields_text_right']);
        
        foreach ($fields as $field)
            echo $this->tag('th', $field);
        
        ?>
        </tr>
    </thead>
    <tbody>
    <?php 
    foreach ($data as $datum){
        echo $this->tag('tr', null, array(
            'class' => array(
                $datum['RlltDatum']['success'] == 1
                    ? 'success'
                    : 'danger'
                    
            )
        ));
        
        foreach ($fields as $field){
            echo $this->tag('td', 
                    isset($datum['RlltDatum'][ $field ])
                        ? $datum['RlltDatum'][ $field ]
                        : 'N/A'
                    , 
                    
                    array(
                'class' => array(
                    in_array($field, $fields_text_right)
                    ? 'text-right'
                    : ''
                )
            ));
		}
        
        
        echo $this->closeTag();
    }
    ?>        
    </tbody>
</table>


<?php
  // debug($data, true);
   
  //  debug($this->globalVars)
?>

