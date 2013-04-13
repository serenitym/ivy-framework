<?php

class Toolbox {

    use caseTools;
    use urlTools;
    use countryTools;

    static function clearSubmit(){
        unset($_POST);
        header("Location: http://".$_SERVER['REQUEST_URI']);
    }

    static function http_response_code($uri) {
        $headers = get_headers($uri);
        return substr($headers[0], 9, 3);
    }

    static function dump($var, $var_name = 'variable') {
        list(, $trace) = debug_backtrace(false);
        $file = substr(strrchr(dirname($trace['file']),'/'),1);
        $file .= '/' . basename($trace['file']);

        printf("<small>--> <b>%s</b> in <i>%s</i>:<b>%s</b> (%s::%s) \n",
                            $var_name, $file, $trace['line'], $trace['class'], $trace['function']);
        var_dump($var);
        print "</small>";
    }

}
