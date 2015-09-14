<?php


/*
bool imagestring ( resource $image , int $font , int $x , int $y , string $string , int $color )
array imagettftext ( resource $image , float $size , float $angle , int $x , int $y , int $color , string $fontfile , string $text )
bool imagerectangle ( resource $image , int $x1 , int $y1 , int $x2 , int $y2 , int $color )
 * 
 * array imagettfbbox ( float $size , float $angle , string $fontfile , string $text )
 */

putenv('GDFONTPATH=' . realpath('.' . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'fonts'));

function color($image, $color_name = 'black'){
    $colors = array(
        'black' => '0,0,0',
        'blue' => '0,0,255',
        'gray' => '128,128,128',
        'green' => '0,128,0',
        'lawn_green' => '124,252,0',
        'red' => '255,0,0',
        'white' => '255,255,255',
        'yellow' => '255,255,0',
        'silver' => '192,192,192'
    );
    
    if (empty($colors[$color_name]))
        $color_name = 'black';
        
    list($r, $g ,$b) = explode(',', $colors[$color_name]);
    
    return imagecolorallocate($image, $r, $g ,$b);
}

define('DEFAULT_FONT_NAME', 'DejaVuSansCondensed');
        

class RLLTImage {
    //put your code here
    
    var $image;
    var $size = array(
        'width' => 1920,
        'height' => 1080
    );
    var $settings = array();
    var $data = array();
    
    private function text($string, $font_size, $x, $y, $color = 'white'){
        
        
        $_color = color($this->image, $color);
        
        return imagettftext($this->image, $font_size, 0, $x, $y, $_color, DEFAULT_FONT_NAME, $string);
    }
    
    
    public function table($data, $options){
        /*
         * Draw table
         */
        
        /*
         * Calc col widths
         */
        
        $cols = array();
        $font_size = empty($options['font_size']) ? 20 :$options['font_size'];
        $max_height = 0;
        
        
        foreach ($options['cols'] as $col_name){
            $col = array(
                'name' => $col_name
            );
            
            //array imagettfbbox ( float $size , float $angle , string $fontfile , string $text )
            /*
             * 0	нижний левый угол, X координата
1	нижний левый угол, Y координата
2	нижний правый угол, X координата
3	нижний правый угол, Y координата
4	верхний правый угол, X координата
5	верхний правый угол, Y координата
6	верхний левый угол, X координата
7	верхний левый угол, Y координата
             */
            
            list($model, $field) = explode('.', $col_name, 2);
            $textbox = array();
            list(
                    $textbox['bottom_left_x'],
                    $textbox['bottom_left_y'],
                    $textbox['bottom_right_x'],
                    $textbox['bottom_right_y'],
                    $textbox['top_left_x'],
                    $textbox['top_left_y'],
                    $textbox['top_right_x'],
                    $textbox['top_right_y'],
                    
                    )= imagettfbbox($font_size, 0,   DEFAULT_FONT_NAME, strtoupper($field));
            //print_r($textbox);
            $col['text_width']  = $textbox['bottom_right_x'] - $textbox['bottom_left_x'];
            $col['text_height'] = $textbox['bottom_right_y'] - $textbox['top_right_y'];
            
            list($model, $field) = explode('.', $col_name, 2);
            $col += compact('model', 'field');
            
            foreach ($data as $datum){
                if (!empty($datum[$model][$field])){
                    $value =$datum[$model][$field];
                    $textbox = array();
                    list(
                        $textbox['bottom_left_x'],
                        $textbox['bottom_left_y'],
                        $textbox['bottom_right_x'],
                        $textbox['bottom_right_y'],
                        $textbox['top_left_x'],
                        $textbox['top_left_y'],
                        $textbox['top_right_x'],
                        $textbox['top_right_y'],                    
                        )= imagettfbbox($font_size, 0, DEFAULT_FONT_NAME, $value);

                    $datum_width = $textbox['bottom_right_x'] - $textbox['bottom_left_x'];
                    $datum_height = $textbox['bottom_right_y'] - $textbox['top_right_y'];

                    if ($col['text_width'] < $datum_width)
                        $col['text_width'] = $datum_width;

                    if ($col['text_height'] < $datum_height)
                        $col['text_height'] = $datum_height;
                }
            }
            
            if ($max_height < $col['text_height'])
                $max_height = $col['text_height'];
            
            $cols[] = $col;
            
        }
        
        
        
        list($table_x0, $table_y0, $table_x1, $table_y1) = $options['coords'];
        
        $table_width = $table_x1 - $table_x0;
        
        /*
         * Calculate columns percents
         */
        
        $total_col_width = 0;
        $max_row_text_height = $max_height;
        foreach ($cols as $col){
            $total_col_width += $col['text_width'];
            if ($col['text_height'] > $max_row_text_height)
                $max_row_text_height = $col['text_height'];
        }
        
        $ix = 0;
        foreach ($cols as $col){
            $cols[$ix]['perc'] = $col['text_width'] / $total_col_width;
            $cols[$ix]['width'] = (int) floor( $cols[$ix]['perc'] * $table_width );
            
             $ix++;
            
        }
        
        $table_height = $table_y1 - $table_y0;
        
        $table_row_count = count($data) + 1; // + thead row
        
        $row_caclulate_height = floor( $table_height / $table_row_count );
        
        $cell_padding = array(
            'left'  => empty($options['padding']['left']) ? 5 : $options['padding']['left'],
            'right'  => empty($options['padding']['right']) ? 5 : $options['padding']['right'],
            'top'  => empty($options['padding']['top']) ? 5 : $options['padding']['top'],
            'bottom'  => empty($options['padding']['bottom']) ? 5 : $options['padding']['bottom'],
        );
        
        
        $row_fixed_height = $max_row_text_height + $cell_padding['top'] + $cell_padding['bottom'];
        
        //debug($cols);
       // print_r(compact('max_row_text_height', 'max_height'));
        
        $top = $table_y0;
        
        $this->table_row(true, $cols, array(
            $table_x0, $top, $table_x1, $top + $row_fixed_height
        ), $options + compact('font_size', 'cell_padding') + array(
            'background-color' => 'silver',
            'color' => 'black',
            'border-color' => 'black'
        ));
        
        $top += $row_fixed_height;
        
        foreach ($data as $datum){
            $this->table_row($datum, $cols, array(
                    $table_x0, $top, $table_x1, $top + $row_fixed_height
                ), $options + compact('font_size', 'cell_padding') + array(
                    'background-color' => empty($datum['background-color']) ? 'white' : $datum['background-color'],
                    'color' => empty($datum['color']) ?  'black' : $datum['color'],
                    'border-color' => empty($datum['border-color']) ?  'black' : $datum['border-color'],
                ));
            
            $top += $row_fixed_height;
        } /**/
        
        /*print_r(compact(
                'table_width',
                'table_height',
                'row_height'
                )); * /
        /*
         * Thead
         */
        
        /*
         * TBody
         */
    }
    
    public function table_row($datum, $cols, $pos, $options){

            //*col headers
            //
        
        if (empty($options['border-color']))
            $options['border-color'] = 'black';
        
            $xpos = $pos[0];
            foreach($cols as $col){
                
                if (!empty($options['background-color'])){
                    imagefilledrectangle(
                        $this->image, 
                        $xpos,                 $pos[1], 
                        $xpos + $col['width'], $pos[3], 
                        color($this->image, $options['background-color']));
                }
                
                imagerectangle(
                        $this->image, 
                        $xpos, 
                        $pos[1], 
                        $xpos + $col['width'], 
                        $pos[3], 
                        color($this->image, $options['border-color']));
                
                //drow text
                
                list($model, $field) = explode('.', $col['name'], 2);
                
                $value = '';
                if ($datum === true)
                {
                    $value = $col['field'];
                } else {
                if (isset($datum[$model][$field])) 
                    $value = $datum[$model][$field];
                }
                                        
                $textbox = array();
                
                //$value = "[$value/{$col['field']}]";
                
                list(
                    $textbox['bottom_left_x'],
                    $textbox['bottom_left_y'],
                    $textbox['bottom_right_x'],
                    $textbox['bottom_right_y'],
                    $textbox['top_left_x'],
                    $textbox['top_left_y'],
                    $textbox['top_right_x'],
                    $textbox['top_right_y'],                    
                    )= imagettfbbox($options['font_size'], 0, DEFAULT_FONT_NAME, $value);

                    /*
                     * center alignment
                     */
                    
                    $text_width = $textbox['bottom_right_x'] - $textbox['bottom_left_x'];
                    
                    $this->text($value, $options['font_size'], 
                            $xpos + ($col['width'] - $text_width) /2, 
                            $pos[3] - $options['cell_padding']['bottom'], 
                            empty($options['color']) ? 'red' : $options['color']);
                
                $xpos += $col['width'];
            }
      
    }
    
    public function draw_report1($data, $options){

        $this->image = imagecreatetruecolor($this->size['width'], $this->size['height']);
        imagefill($this->image, 0, 0, color($this->image, 'white'));
        
        $header_box = $this->text($options['settings']['vars']['header'], 25, 10, 40, 'black');
       // print_r($header_box);
        $this->text($options['current_time'], 20, $header_box[4] + 10, 40, 'gray');
        
        $cols = array();
        foreach (explode(',', $options['settings']['table']['fields']) as $f){
            $cols[] = "RlltDatum.$f";
        }
        
        $ix =0;
        foreach ($data as $datum){
            
            if ($datum['RlltDatum']['success'] == 1){
                $data[$ix] += array(
                    'background-color' => 'green',
                    'color' => 'white'
                );
            }
            
            if ($datum['RlltDatum']['success'] == 0){
                $data[$ix] += array(
                    'background-color' => 'red',
                    'color' => 'white'
                );
            }
            $ix++;
        }
        
        $this->table($data, array(
            'font_size' => 24,

            'cols' => $cols,
            'padding' => array(
                'bottom' => 10
            ),
            'coords' => array(
                10, 50, $this->size['width'] - 10, 1000
            )
        ));
        
      //  print_r($data);
        
        /*
         * Header of the table
         */
        
        foreach ($options['save_to_files'] as $filename){
            imagejpeg($this->image, $filename);
        }
        
        // Free up memory
        imagedestroy($this->image);
    }

    public function testdraw($filename = null){
        
        $this->image = imagecreatetruecolor($this->size['width'], $this->size['height']);
        imagefill($this->image, 0, 0, color($this->image, 'white'));

      //  imagefilledrectangle($this->image, 100, 100, 300, 500, color($this->image, 'red'));
      //  $this->text(date('Русский текст'), 40, 200, 200, 'white');

        $this->table(array(
            array(
                'Model1' => array(
                    'id' => 2000,
                    'name' => 'Aaaaa'                       
                )
            ),
            array(
                'Model1' => array(
                    'id' => 3000,
                    'name' => 'Bbbbb'                       
                ),
                'color' => 'white',                
                'background-color' => 'green'
            ),
            array(
                'Model1' => array(
                    'id' => 4678,
                    'name' => 'Ccfg ggg ',
                    'time' => '2015-09-12 12:00',
                    'Time.is.now' => 'TRUE'
                ),
                 'color' => 'white',                
                'background-color' => 'red'
            ),
        ), array(
            'font_size' => 40,
            'cols' => array(
                'Model1.id',
                'Model1.name',
                'Model1.time',
                'Model1.Time.is.now',
            ),
            'padding' => array(
                'bottom' => 10
            ),
            'coords' => array(
                10, 10, 1910, 1000
            )
            
        ));

        // Output the image
        imagejpeg($this->image, $filename);
        
       

        // Free up memory
        imagedestroy($this->image);
    }
}
/*
 * 
 * Color table http://www.w3schools.com/tags/ref_color_tryit.asp?hex=FAEBD7
 * 
 Colorname	HEX	RGB
AliceBlue	F0F8FF	240,248,255
AntiqueWhite	FAEBD7	250,235,215
Aqua	00FFFF	0,255,255
Aquamarine	7FFFD4	127,255,212
Azure	F0FFFF	240,255,255
Beige	F5F5DC	245,245,220
Bisque	FFE4C4	255,228,196
Black	000000	0,0,0
BlanchedAlmond	FFEBCD	255,235,205
Blue	0000FF	0,0,255
BlueViolet	8A2BE2	138,43,226
Brown	A52A2A	165,42,42
BurlyWood	DEB887	222,184,135
CadetBlue	5F9EA0	95,158,160
Chartreuse	7FFF00	127,255,0
Chocolate	D2691E	210,105,30
Coral	FF7F50	255,127,80
CornflowerBlue	6495ED	100,149,237
Cornsilk	FFF8DC	255,248,220
Crimson	DC143C	220,20,60
Cyan	00FFFF	0,255,255
DarkBlue	00008B	0,0,139
DarkCyan	008B8B	0,139,139
DarkGoldenRod	B8860B	184,134,11
DarkGray	A9A9A9	169,169,169
DarkGreen	006400	0,100,0
DarkKhaki	BDB76B	189,183,107
DarkMagenta	8B008B	139,0,139
DarkOliveGreen	556B2F	85,107,47
DarkOrange	FF8C00	255,140,0
DarkOrchid	9932CC	153,50,204
DarkRed	8B0000	139,0,0
DarkSalmon	E9967A	233,150,122
DarkSeaGreen	8FBC8F	143,188,143
DarkSlateBlue	483D8B	72,61,139
DarkSlateGray	2F4F4F	47,79,79
DarkTurquoise	00CED1	0,206,209
DarkViolet	9400D3	148,0,211
DeepPink	FF1493	255,20,147
DeepSkyBlue	00BFFF	0,191,255
DimGray	696969	105,105,105
DodgerBlue	1E90FF	30,144,255
FireBrick	B22222	178,34,34
FloralWhite	FFFAF0	255,250,240
ForestGreen	228B22	34,139,34
Fuchsia	FF00FF	255,0,255
Gainsboro	DCDCDC	220,220,220
GhostWhite	F8F8FF	248,248,255
Gold	FFD700	255,215,0
GoldenRod	DAA520	218,165,32
Gray	808080	128,128,128
Green	008000	0,128,0
GreenYellow	ADFF2F	173,255,47
HoneyDew	F0FFF0	240,255,240
HotPink	FF69B4	255,105,180
IndianRed	CD5C5C	205,92,92
Indigo	4B0082	75,0,130
Ivory	FFFFF0	255,255,240
Khaki	F0E68C	240,230,140
Lavender	E6E6FA	230,230,250
LavenderBlush	FFF0F5	255,240,245
LawnGreen	7CFC00	124,252,0
LemonChiffon	FFFACD	255,250,205
LightBlue	ADD8E6	173,216,230
LightCoral	F08080	240,128,128
LightCyan	E0FFFF	224,255,255
LightGoldenRodYellow	FAFAD2	250,250,210
LightGray	D3D3D3	211,211,211
LightGreen	90EE90	144,238,144
LightPink	FFB6C1	255,182,193
LightSalmon	FFA07A	255,160,122
LightSeaGreen	20B2AA	32,178,170
LightSkyBlue	87CEFA	135,206,250
LightSlateGray	778899	119,136,153
LightSteelBlue	B0C4DE	176,196,222
LightYellow	FFFFE0	255,255,224
Lime	00FF00	0,255,0
LimeGreen	32CD32	50,205,50
Linen	FAF0E6	250,240,230
Magenta	FF00FF	255,0,255
Maroon	800000	128,0,0
MediumAquaMarine	66CDAA	102,205,170
MediumBlue	0000CD	0,0,205
MediumOrchid	BA55D3	186,85,211
MediumPurple	9370DB	147,112,219
MediumSeaGreen	3CB371	60,179,113
MediumSlateBlue	7B68EE	123,104,238
MediumSpringGreen	00FA9A	0,250,154
MediumTurquoise	48D1CC	72,209,204
MediumVioletRed	C71585	199,21,133
MidnightBlue	191970	25,25,112
MintCream	F5FFFA	245,255,250
MistyRose	FFE4E1	255,228,225
Moccasin	FFE4B5	255,228,181
NavajoWhite	FFDEAD	255,222,173
Navy	000080	0,0,128
OldLace	FDF5E6	253,245,230
Olive	808000	128,128,0
OliveDrab	6B8E23	107,142,35
Orange	FFA500	255,165,0
OrangeRed	FF4500	255,69,0
Orchid	DA70D6	218,112,214
PaleGoldenRod	EEE8AA	238,232,170
PaleGreen	98FB98	152,251,152
PaleTurquoise	AFEEEE	175,238,238
PaleVioletRed	DB7093	219,112,147
PapayaWhip	FFEFD5	255,239,213
PeachPuff	FFDAB9	255,218,185
Peru	CD853F	205,133,63
Pink	FFC0CB	255,192,203
Plum	DDA0DD	221,160,221
PowderBlue	B0E0E6	176,224,230
Purple	800080	128,0,128
RebeccaPurple	663399	102,51,153
Red	FF0000	255,0,0
RosyBrown	BC8F8F	188,143,143
RoyalBlue	4169E1	65,105,225
SaddleBrown	8B4513	139,69,19
Salmon	FA8072	250,128,114
SandyBrown	F4A460	244,164,96
SeaGreen	2E8B57	46,139,87
SeaShell	FFF5EE	255,245,238
Sienna	A0522D	160,82,45
Silver	C0C0C0	192,192,192
SkyBlue	87CEEB	135,206,235
SlateBlue	6A5ACD	106,90,205
SlateGray	708090	112,128,144
Snow	FFFAFA	255,250,250
SpringGreen	00FF7F	0,255,127
SteelBlue	4682B4	70,130,180
Tan	D2B48C	210,180,140
Teal	008080	0,128,128
Thistle	D8BFD8	216,191,216
Tomato	FF6347	255,99,71
Turquoise	40E0D0	64,224,208
Violet	EE82EE	238,130,238
Wheat	F5DEB3	245,222,179
White	FFFFFF	255,255,255
WhiteSmoke	F5F5F5	245,245,245
Yellow	FFFF00	255,255,0
YellowGreen	9ACD32	154,205,50
 * 
 */