# Image manager

A bunch of in-progress scripts to manage a messy photo collection. It's intended for images, but some scripts (like sort.php) can be used for any kind of files.

Written in PHP and can be run through CLI on any reasonably modern version of PHP.

## sort.php
Use it:

```
./sort.php ~/Pictures ~/Sorted
```

It will recursively move all files from the source directory to one destination directory. EXIF `DateTimeOriginal` OR modified date (as a fallback) becomes filename prefix. The rest of the filename is tags extracted from old file path.

```
~/Pictures/Family/Family reunions/Paris 2000/IMG_0123.JPG
```

Becomes:
```
~/Sorted/2000-01-01 16.05.45 family reunions paris.JPG
```

Please note that duplicated words, numbers, spacing symbols (-_) are removed from tags and all words are lowercased to keep the noise down and search-ability up.
