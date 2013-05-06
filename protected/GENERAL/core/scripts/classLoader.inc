<?php

    spl_autoload_register(array(ClassLoader::getInstance(), 'loadClass'));

/**
 * Universal autoloader class.
 * @desc Universal autoloader class.
 *
 * @package Core
 * @version 1.0
 * @copyright Copyright (c) 2012 Serenity Media
 * @author  Victor Nițu
 * @license AGPLv3 {@link http://www.gnu.org/licenses/agpl-3.0.txt}
 */
class ClassLoader {

    private static $saveFile;

    /* singleton */
    private static $instance;

    /* stores a className -> filePath map */
    private $classList;
    /* tells whether working from saved file */
    private $refreshed;


    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new ClassLoader();
        }
        return self::$instance;
    }

    private function __construct() {
        self::$saveFile = varPath.'ClassLoader.save.php';
        $this->initClassList();
    }

    public function loadClass($className) {
        if ( !array_key_exists($className, $this->classList) && !$this->refreshed ) {
            $this->refreshClassList();
        }
        include_once($this->classList[$className]);
    }

    private function initClassList() {


        if (file_exists(self::$saveFile)) {
            require_once(self::$saveFile);
            $this->refreshed = FALSE;
        } else {
            $this->refreshClassList();
        }
    }

    private function refreshClassList() {
        $this->classList = $this->scanDirectory(fw_incPath);
        $this->refreshed = TRUE;

        $this->saveClassList();
    }


    private function openFile() {
        if (($handle = fopen(self::$saveFile, "w+") === FALSE))
            throw new Exception("File cannot be opened for writing!");
        chmod(self::$saveFile, 0777);
        return $handle;
    }

    private function saveClassList() {

        try {
            $handle = $this->openFile();
        }
        catch (Exception $e) {
            echo "Class loader exception: <br/>\n".$e->getMessage();
        }

        $content = "<?php \r\n";
        foreach($this->classList as $class => $path) {
            $content .= '$this->classList' . "['" . $class . "'] = '" . $path . "';\r\n";
        }

        $content .= '?>';
        file_put_contents(self::$saveFile, $content);
    }

    private function scanDirectory ($directory) {
        // strip closing '/'
        if (substr($directory, -1) == '/') {
                $directory = substr($directory, 0, -1);
        }

        if (!file_exists($directory) || !is_dir($directory) ||
                    !is_readable($directory) ||
                    (is_link($directory) && !is_dir(readlink($directory)))
        ) {
            return array();
            throw new Exception ('Cannot enter dir!');
        }

        $dirH = opendir($directory);
        $scanRes = array();

        while(($file = readdir($dirH)) !== FALSE) {

            // skip pointers
            if ( strcmp($file , '.') == 0 || strcmp($file , '..') == 0 || strcmp($file , '.svn') == 0) {
                continue;
            }

            $path = $directory . '/' . $file;

            if (!is_readable($path)) {
                continue;
            }

            // recursion
            if (is_dir($path)) {
                $scanRes = array_merge($scanRes, $this->scanDirectory($path));

            } elseif (is_file($path)) {
                $className = explode('.', $file);
                if ( $className[count($className)-1] == 'php')  {
                    $scanRes[$className[0]] = $path;
                }
            }
        }

        return $scanRes;
    }

}
?>