<?php 
$current = $settings['time']['current'] == 'os'
        ? date('Y-m-d H:i:s')
        :$file_date;

echo empty($settings['vars']['header'])
    ? null
    : $this->tag(
            'h2', 
            $settings['vars']['header'] . ' ' . $this->tag('small', "$file_name: $current")
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
                $datum['Status'] == 'delay'
                    ? 'danger'
                    : 'success'
            )
        ));
        
        foreach ($fields as $field)
            echo $this->tag('td', $datum[ $field ], array(
                'class' => array(
                    in_array($field, $fields_text_right)
                    ? 'text-right'
                    : ''
                )
            ));
        
        
        echo $this->closeTag();
    }
    ?>        
    </tbody>
</table>


<?php
   // debug($data);
  //  print_r($this->globalVars)
?>

