<?php

/**
 * //////////////////
 * // PicBy сlass //
 * ////////////////
 * 
 * A PicBy сlass for selecting images by colors or pictures.
 * 
 * Key features:
 * - Selects pictures by color
 * - Selects pictures by pictures (by most repeating colors in the image)
 * 
 * Some examples:
 * PicBy::setDefaultImagesFolderPath(__DIR__ . "/assets"); // return: void
 * PicBy::color('cyan', __DIR__ . "/assets", 1); // return: array (of pictures with MaxColor in 'cyan' range)
 * PicBy::image(__DIR__ . "/assets/cyan_20_20.jpg", __DIR__ . "/assets", 1); // return: array (of pictures with MaxColor 'cyan' like as in the picture)
 * PicBy::getMaxColor(__DIR__ . "/assets/cyan_20_20.jpg", 5); // return: string ('cyan')
 * PicBy::rgbToHex(0, 255, 255); // return: string ('#00ffff')
 * PicBy::rgbtoColorCode(0, 255, 255); // return: string ('00FFFF')
 * PicBy::colorCodeToColorName('00FFFF'); // return: string ('cyan')
 * 
 * @author jzavorohina@yandex.ru
 * 
 */


class PicBy
{

    public static $imagesFolderPath = "";
    /**
     * Array of color samples
     */
    const colorCode = array(
        'red' => array('FFCCCC', 'FF9999', 'CC9999', 'FF6666', 'CC6666', '996666', 'FF3333', 'CC3333', '993333', '663333', 'FF0000', 'CC0000', '990000', '660000', '330000'),
        'yellow' => array('FFFFCC', 'FFFF99', 'CCCC99', 'FFFF66', 'CCCC66', '999966', 'FFFF33', 'CCCC33', '999933', '666633', 'FFFF00', 'CCCC00', '999900', '666600', '333300'),
        'green' => array('CCFFCC', '99FF99', '99CC99', '66FF66', '66CC66', '669966', '33FF33', '33CC33', '339933', '336633', '00FF00', '00CC00', '009900', '006600', '003300'),
        'cyan' => array('CCFFFF', '99FFFF', '99CCCC', '66FFFF', '66CCCC', '669999', '33FFFF', '33CCCC', '339999', '336666', '00FFFF', '00CCCC', '009999', '006666', '003333'),
        'blue' => array('CCCCFF', '9999FF', '9999CC', '6666FF', '6666CC', '666699', '3333FF', '3333CC', '333399', '333366', '0000FF', '0000CC', '000099', '000066', '000033'),
        'magenta' => array('FFCCFF', 'FF99FF', 'CC99CC', 'FF66FF', 'CC66CC', '996699', 'FF33FF', 'CC33CC', '993399', '663366', 'FF00FF', 'CC00CC', '990099', '660066', '330033')
    );

    /**
     * Setup default path to search images folder
     *
     * @param string $imagesFolderPath - path to images folder
     * @return void
     */
    static function setDefaultImagesFolderPath($imagesFolderPath)
    {
        self::$imagesFolderPath = $imagesFolderPath;
    }

    /**
     * Searches pictures that contains input color name
     *
     * @param string $colorName - color name from color range
     * @param string $imagesFolderPath - path to images folder
     * @param integer $granularity - pixel iteration granularity
     * @return array - array of path to found pictures
     */
    static function color($colorName, $imagesFolderPath = null, $granularity = 5)
    {

        if (!$imagesFolderPath) {
            $imagesFolderPath = self::$imagesFolderPath;
        }

        $result = array();

        if (is_dir($imagesFolderPath)) {
            if ($dh = opendir($imagesFolderPath)) {
                while (($fileName = readdir($dh)) !== false) {
                    $color = self::getMaxColor($imagesFolderPath . "\\" . $fileName, $granularity);
                    if ($colorName === $color) {
                        array_push($result, $fileName);
                    }
                }
                closedir($dh);
            }
        }

        return $result;
    }

    /**
     * Searches pictures that contains most repeating color from input image
     *
     * @param string $filePath - the path to the files folder
     * @param string $imagesFolderPath - path to images folder
     * @param integer $granularity - pixel iteration granularity
     * @return array - array of path to found pictures
     */
    static function image($filePath, $imagesFolderPath = null, $granularity = 5)
    {
        $originalColor = self::getMaxColor($filePath, $granularity);
        $result = self::color($originalColor, $imagesFolderPath);
        return $result;
    }

    /**
     * Finds a color that most repeating in the image
     *
     * @param string $filePath - the path to the files folder
     * @param integer $granularity - color analyze granularity 
     * @return string - most repeating color name
     */
    static function getMaxColor($filePath, $granularity = 5)
    {
        $ext = pathinfo($filePath, PATHINFO_EXTENSION);
        $img = null;
        switch ($ext) {
            case "jpg":
            case "jpeg":
                $img = imagecreatefromjpeg($filePath);
                break;
            case "bmp":
                $img = imagecreatefrombmp($filePath);
                break;
            case "gif":
                $img = imagecreatefromgif($filePath);
                break;
            case "png":
                $img = imagecreatefrompng($filePath);
                break;
            default:
                $img = null;
        }

        if (!$img) {
            return;
        }

        $granularity = max(1, abs((int)$granularity));
        $size = @getimagesize($filePath);

        $colorCounts = array(
            'red' => 0,
            'yellow' => 0,
            'green' => 0,
            'cyan' => 0,
            'blue' => 0,
            'magenta' => 0
        );

        for ($x = 0; $x < $size[0]; $x += $granularity) {
            for ($y = 0; $y < $size[1]; $y += $granularity) {
                $thisColor = imagecolorat($img, $x, $y);
                $rgb = imagecolorsforindex($img, $thisColor);
                $colorCode = self::rgbtoColorCode($rgb['red'], $rgb['green'], $rgb['blue']);
                $name = self::colorCodeToColorName($colorCode);

                foreach ($colorCounts as $key => $value) {
                    if ($key === $name) {
                        $colorCounts[$key] = $value + 1;
                    }
                }

                //echo implode("|", [$y, $x, $rgb['red'], $rgb['green'], $rgb['blue'], $thisRGB, $name ]) . "\n";
            }
        }

        $maxValue = max($colorCounts);
        $maxColor = array_search($maxValue, $colorCounts);
        // var_dump($colorCounts);
        // echo "\nmax =" . $maxValue . "|" . $maxColor;
        return $maxColor;
    }

    /**
     * Convert RGB color format into HEX color format
     * (A hex triplet is a six-digit, three-byte hexadecimal number used in
     *  HTML, CSS, SVG, and other computing applications to represent colors.)
     *
     * @param integer $red - 0...255 - red value (color type red)
     * @param integer $green - 0...255 - green value (color type green)
     * @param integer $blue - 0...255 - blue value (color type blue)
     * @param integer $alpha - 0...127 - alpha channel, opacity/semi-transparent
     * 
     * @return string - color code in HEX format
     */
    static function rgbToHex($red, $green, $blue, $alpha = null)
    {
        $result = '#';
        foreach (array($red, $green, $blue) as $row) {
            $result .= str_pad(dechex($row), 2, '0', STR_PAD_LEFT);
        }

        if (!is_null($alpha)) {
            $alpha = floor(255 - (255 * ($alpha / 127)));
            $result .= str_pad(dechex($alpha), 2, '0', STR_PAD_LEFT);
        }

        return $result;
    }

    /**
     * Convert RGB color format into internal color code
     *
     * @param integer $rgb - RGB color format
     * @return string - internal color code in HEX format
     *
     */
    static function rgbtoColorCode($red, $green, $blue)
    {
        $redCode = round(round(($red / 0x33)) * 0x33);
        $greenCode = round(round(($green / 0x33)) * 0x33);
        $blueCode = round(round(($blue / 0x33)) * 0x33);

        $thisRGB = sprintf('%02X%02X%02X', $redCode, $greenCode, $blueCode);

        return $thisRGB;
    }

    /**
     * Convert RGB color format into internal color code
     *
     * @param string $colorCode - color code in internal HEX format
     * @return string - color name
     * @todo 
     */
    static function colorCodeToColorName($colorCode)
    {
        foreach (self::colorCode as $name => $codes) {
            if (in_array(mb_strtoupper($colorCode, "utf-8"), $codes)) {
                return $name;
            }
        }
        return null;
    }
}
