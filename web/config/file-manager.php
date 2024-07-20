<?php

return [

    'get_max_upload_file_size' => '250',

    'get_allowed_file_types' => [
        'gif', 'jpg', 'png',
        'doc', 'docx', 'docm', 'dotx', 'dotm',
        'xls', 'xlsx', 'xlsm', 'xltx', 'xltm',
        'ppt', 'pptx', 'pps', 'ppsx', 'pptm',
        'potx', 'potm', 'pdf',
        'htm', 'html', 'txt', 'rtf',
        'ico', 'zip',
        'wav', 'mp3', 'mpg',
        'mpeg', 'avi', 'qt', 'mov', 'mp4',
        'js', 'css'
    ],

    'acl' => false,
    'leftDisk' => null,
    'rightDisk' => null,
    'leftPath' => null,
    'rightPath' => null,
    'windowsConfig' => 2,
    'hiddenFiles' => true,
    'driver' => 'local',
    'lang' => 'en',

    'slugify_names' => false,
];
