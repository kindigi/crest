<?php

require_once './cli/Crest/Drivers/CrestDriver.php';

foreach (scandir('./cli/Crest/Drivers') as $file) {
    $path = './cli/Crest/Drivers/'.$file;
    if (substr($file, 0, 1) !== '.' && ! is_dir($path)) {
        require_once $path;
    }
}
