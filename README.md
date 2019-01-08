# Image manager

A bunch of in-progress scripts to manage a messy photo collection. It's intended for images, but some scripts (like sort.php) can be used for any kind of files.

Written in PHP and can be run through CLI on any reasonably modern version of PHP.

## sort.php
Use it:

```
./sort.php ~/Pictures ~/Sorted
```

It will recursively move all files from the source directory to a destination directory subfolder based on EXIF `DateTimeOriginal` or modified date. New file structure will be as follows: `~/Sorted/yyyy-mm-dd/original-filename`.

If previous folder name contains words, they are transformed to tags and stored right next to the original image file. Example:

```
~/Pictures/Family/Family reunions/Paris 2000/IMG_0123.JPG
```

Becomes two files:
```
~/Sorted/2000-01-01/IMG_0123.JPG
~/Sorted/2000-01-01/IMG_0123.JPG.txt > family reunions paris
```

Please note that duplicated words, numbers, spacing symbols (-_) are removed and all words are lowercased to keep the noise data down.

This is done so that no data would be lost in the process of reorganizing file structure - you can still use it further down the line.

I chose not to encode tags as part of JPEG binary data to keep compatibility across all kinds of files. Also, modifying files would introduce additional complexity and ruin modified dates.
