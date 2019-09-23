<?php

if (!function_exists('form_input_group_close')) {

    function form_input_group_close() {
        return '</div><!--close .input_group-->';
    }

}
if (!function_exists('form_input_group_open')) {

    function form_input_group_open($extra = []) {
        if(isset($extra['class'])){
            $extra['class'] .= ' input-group'; 
        }else{
            $extra['class'] = ' input-group'; 
        }
        return '<div ' . _attributes_to_string($extra) . " />\n";
    }

}

if (!function_exists('form_input_number')) {

    /**
     * Text Input Number <HTML5> compatible Field
     *
     * @param	mixed
     * @param	string
     * @param	mixed
     * @param	int
     * @param	mixed
     * @return	string
     */
    function form_input_number($data = '', $value = '', $extra = '', int $min = 0, $step = 1) {
        $defaults = array(
            'type' => 'number',
            'name' => is_array($data) ? '' : $data,
            'value' => $value,
            'min' => $min,
            'step' => $step
        );

        return '<input ' . _parse_form_attributes($data, $defaults) . _attributes_to_string($extra) . " />\n";
    }

}

