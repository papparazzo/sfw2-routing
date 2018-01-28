<?php
$host = $_SERVER['HTTP_HOST'];

return [
    'database' => [
        'host' => 'localhost',
        'user' => '',
        'pwd'  => '',
        'db'   => ''
    ],

    'site' => [
        'offline'            => true,
        'offlineBypassToken' => '9658ed3dfef655860934878aee7c96946ea0b0d73612c5'
    ],

    'debug' => [
        'on'          => false,
        'title'       => '[DEBUG New]'
    ],

    'defEMailAddr' => [
        'addr' => 'noreply@' . $host,
        'name' => 'noreply'
    ],

    'project' => [
        'title'          => '',
        'eMailWebMaster' => 'webmaster@' . $host,
    ],

    'path' => [
        'tmp'        => 'tmp/',
        'log'        => 'weblog/',
        'data'       => 'data/',
        'gallery'    => 'public/content/',
        'template'   => 'web/templates/',

#        'templatePathSFW' => dirname(__FILE__) . '/Templates/',

        'jsPath'          => 'public/js/',
#        'jsPathSFW'       =>  dirname(__FILE__) . '/Public/js/',

        'cssPath'         => 'public/css/',
#        'cssPathSFW' => dirname(__FILE__) . '/Public/css/'
    ],

    'misc' => [
        'timeZone'    => 'Europe/Berlin',
        'locale'      => 'de_DE',
        'memoryLimit' => 256
    ],


# FIXME DC
    'dublinCoreMetaData' => [
        "keywords"       => "Tennis, Bogenschießen, Fußball, Turnen, Sportverein, Alvesrode, Springe",
        "DC.title"       => "VfV Concordia, Alvesrode",
        "DC.creator"     => "Stefan Paproth",
        "DC.description" => "Sportverein in Alvesrode",
        "DC.publisher"   => "VfV Concordia Alvesrode",
        "DC.description" => "Rund um den VfV Concordia, Alvesrode",
        "DC.language"    => "de",
        "DC.coverage"    => "Springe, Alvesrode",
        "DC.rights"      => "Alle Rechte liegen beim Autor",
    ]
];
