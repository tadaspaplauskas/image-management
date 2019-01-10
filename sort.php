#!/usr/bin/php
<?php

// CONFIGURATION
define('SOURCE', $argv[1]);
define('DESTINATION', $argv[2]);

// recurse through source directory
$directory = new RecursiveDirectoryIterator(SOURCE, RecursiveDirectoryIterator::SKIP_DOTS);
$iterator = new RecursiveIteratorIterator($directory);

$logs = '';

foreach ($iterator as $file)
{
    if ($file->getFilename()[0] === '.' || in_array($file->getFilename(), ['Thumbs.db'])) {
        continue;
    }

    // tags will become part of filename
    $tags = parseTags($file, $directory);

    $newPath = DESTINATION
        . trim(getDateTimeFromFile($file) . ' ' . parseTags($file, $directory))
        . '.' . $file->getExtension();

    rename($file->getPathname(), $newPath);

    $output = $file->getPathname() . ' >> ' . $newPath . PHP_EOL;

    $logs .= $output;

    echo $output;
}

file_put_contents(DESTINATION . '/logs.log', $output, FILE_APPEND);


function getDateTimeFromFile($file)
{
    if ($exif = @exif_read_data($file) && isset($exif['DateTimeOriginal'])) {
        // keeping the 'Y-m-d' format
        $timestamp = strtotime($exif['DateTimeOriginal']);
    }

    // fallback
    try {
        $timestamp = $file->getMTime();
    }
    catch (Exception $e) {
        return null;
    }

    return date('Y-m-d H.i.s', $timestamp);
}

function getDateFromFile($file)
{
    return date('Y-m-d', strtotime(getDateTuneFromFile($file)));
}

function parseTags($file, $source)
{
    // filename is not part of the data since we're keeping it anyway
    $string = $file->getPathname();

    // remove source path and extension
    $string = str_replace([$source->getPath(), $file->getExtension()], '', $string);

    // lowercase
    $string = mb_strtolower($string);

    // remove standard bs
    $string = str_replace(['dscn', 'dsc', 'img', 'scan'], ' ', $string);

    // remove non-words
    $string = preg_replace('/[^a-z \/_-]+/', '', $string);

    // unify spacing
    $string = trim(str_replace(['/', '-', '_',], ' ', $string));

    // remove doubled spaces
    $string = preg_replace('/\s+/', ' ',$string);

    // unique words
    $string = implode(' ', array_unique(explode(' ', $string)));

    return $string;
}
