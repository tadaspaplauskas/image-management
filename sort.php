#!/usr/bin/php
<?php

$source = $argv[1];
$dest = $argv[2];

// recurse through source directory
$directory = new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS);
$iterator = new RecursiveIteratorIterator($directory);

foreach ($iterator as $file)
{
    $tags = parseTags($file, $directory);

    $timestamp = getTimestamp($file);

    $newPath = $dest . '/' . $timestamp . '/';

    if (!file_exists($newPath)) {
        mkdir($newPath);
    }

    $newPath .= $file->getFilename();

    rename($file->getPathname(), $newPath);

    if (!empty($tags)) {
        // we're writing additional tags to a separate file to avoid tempering modified date
        file_put_contents($newPath . '.txt', $tags);
    }

    echo $file->getPathname() . ' >> ' . $newPath . PHP_EOL;
}

function getTimestamp($file)
{
    if ($exif = @exif_read_data($file) && isset($exif['DateTimeOriginal'])) {
        // keeping the 'Y-m-d' format
        return str_replace(':', '-', explode(' ', $exif['DateTimeOriginal'])[0]);
    }

    // fallback
    return date('Y-m-d', $file->getMTime());
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
