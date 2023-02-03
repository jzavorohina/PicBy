# PicBy class  
A PicBy сlass for selecting images by colors or pictures.

### Key features:
 - Selects pictures by color
 - Selects pictures by pictures (by most repeating colors in the image)

### Some examples:
```php
// Searches pictures that contains input color name
PicBy::color('cyan', __DIR__ . "/assets", 1); // return: array (of pictures with MaxColor in 'cyan' range)

// Searches pictures that contains most repeating color from input image
PicBy::image(__DIR__ . "/assets/cyan_20_20.jpg", __DIR__ . "/assets", 1); // return: array (of pictures with MaxColor 'cyan' like as in the picture)

// Setup default path to search images folder
PicBy::setDefaultImagesFolderPath(__DIR__ . "/assets"); // return: void

// Finds a color that most repeating in the image
PicBy::getMaxColor(__DIR__ . "/assets/cyan_20_20.jpg", 5); // return: string ('cyan')

// Convert RGB color format into HEX color format
PicBy::rgbToHex(0, 255, 255); // return: string ('#00ffff')

// Convert RGB color format into internal color code
PicBy::rgbtoColorCode(0, 255, 255); // return: string ('00FFFF')

// Convert RGB color format into internal color code
PicBy::colorCodeToColorName('00FFFF'); // return: string ('cyan')
```
