#!/usr/bin/php
<?php

$source = $argv[1];
$dest = $argv[2];

// recurse through source directory
$directory = new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS);
$iterator = new RecursiveIteratorIterator($directory);

foreach ($iterator as $file)
{
    if (in_array($file->getFilename(), ['.DS_Store', 'Thumbs.db'])) {
        continue;
    }

    $tags = parseTags($file, $directory);

    $timestamp = getTimestamp($file);

    if ($timestamp) {
        $newPath = $dest . '/' . $timestamp . '/';
    }
    else {
        $newPath = $dest . '/unsorted/';
    }

    if (!file_exists($newPath)) {
        mkdir($newPath);
    }

    $newPath .= $file->getFilename();

    rename($file->getPathname(), $newPath);

    // first line is full path to the source image
    // second line is extracted tags from the said path
    // this is done to keep all information from data structure
    file_put_contents($newPath . '.txt', $file->getPathname() . PHP_EOL . $tags);

    echo $file->getPathname() . ' >> ' . $newPath . PHP_EOL;
}

function getTimestamp($file)
{
    if ($exif = @exif_read_data($file) && isset($exif['DateTimeOriginal'])) {
        // keeping the 'Y-m-d' format
        return str_replace(':', '-', explode(' ', $exif['DateTimeOriginal'])[0]);
    }

    // fallback
    try {
        $timestamp = $file->getMTime();
    }
    catch (Exception $e) {
        return null;
    }

    return date('Y-m-d', $timestamp);
}

function parseTags($file, $source)
{
    // filename is not part of the data since we're keeping it anyway
    $string = $file->getPath();

    // remove source path and extension
    $string = str_replace([$source->getPath(), $file->getExtension()], '', $string);

    // lowercase
    $string = mb_strtolower($string);

    // remove standard bs
    $string = str_replace(['dscn', 'dsc', 'img', 'scan'], ' ', $string);

    // remove non-words
    $string = preg_replace('/[^a-z \/_-]+/', '', $string);

    // unify spacing
    $string = trim(str_replace(['/', '  ', '-', '_',], ' ', $string));

    // unique words
    $string = implode(' ', array_unique(explode(' ', $string)));

    return $string;
}
