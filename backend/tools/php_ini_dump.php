<?php
$keys = [
    'file_uploads',
    'upload_max_filesize',
    'post_max_size',
    'max_file_uploads',
    'max_input_time',
    'max_execution_time',
    'memory_limit',
    'upload_tmp_dir',
];
foreach ($keys as $k) {
    $v = ini_get($k);
    echo $k . '=' . (($v === false) ? '(false)' : $v) . PHP_EOL;
}
