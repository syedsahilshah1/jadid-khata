<?php

if (! function_exists('country_code_number')) {
    /**
     * Change 0 to country code.
     *
     * @param int|string $value
     * @return string
     */
    function country_code(int|string $value): string
    {
        $ptn = "/^0/";  // Regex
        $str = $value; //Your input, perhaps $_POST['textbox'] or whatever
        $rpltxt = "91";  // Replacement string
        $results = preg_replace($ptn, $rpltxt, $str);
        return $results;
    }
}

function whatsapp_date($value): string
{
    return \Carbon\Carbon::parse($value)->isoFormat('D MMMM Y');
}

function whatsapp_date_human($value): string
{
    return \Carbon\Carbon::parse($value)->diffForHumans();
}

function whatsapp_date_local($value): string
{
   return \Carbon\Carbon::parse($value)->isoFormat('D MMMM Y');
}

function whatsapp_date_human_localize($value): string
{
    return \Carbon\Carbon::parse($value)->diffForHumans();
}
