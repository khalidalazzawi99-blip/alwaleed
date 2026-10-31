<?php

// Outline the licensed display type so browser and DomPDF use identical geometry.
require dirname(__DIR__).'/vendor/autoload.php';

$root = dirname(__DIR__);
foreach ([
    ['Anton', 'KUDIA', 'wordmark', '#002d43'],
    ['Anton', 'STATEMENT OF', 'statement-title', '#00ade7'],
    ['Anton', 'ACCOUNT', 'account-title', '#002d43'],
    ['Anton', 'INVOICE', 'invoice-title', '#00ade7'],
    ['Allura', 'Thank you for your business!', 'thanks', '#00ade7'],
] as [$family, $text, $name, $color]) {
    $font = \FontLib\Font::load($root.'/public/fonts/'.$family.'-Regular.ttf');
    $font->parse();
    $map = $font->getUnicodeCharMap();
    $glyphs = $font->getData('glyf');
    $metrics = $font->getData('hmtx');
    $advance = 0;
    $minimum = PHP_INT_MAX;
    $maximum = PHP_INT_MIN;
    $paths = '';
    foreach (str_split($text) as $character) {
        $id = $map[ord($character)];
        if ($character !== ' ') {
            $glyph = $glyphs[$id];
            $contours = $glyph->getSVGContours();
            if (! is_string($contours)) {
                throw new RuntimeException('Unsupported composite display glyph.');
            }
            $minimum = min($minimum, $glyph->yMin);
            $maximum = max($maximum, $glyph->yMax);
            $paths .= '<path transform="translate('.$advance.' 0)" d="'.$contours.'"/>';
        }
        $advance += $metrics[$id][0];
    }
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 '.$advance.' '.($maximum - $minimum).'" preserveAspectRatio="none"><title>'.htmlspecialchars($text, ENT_XML1).'</title><g fill="'.$color.'" transform="translate(0 '.$maximum.') scale(1 -1)">'.$paths.'</g></svg>';
    file_put_contents($root.'/public/images/kudia-'.$name.'.svg', $svg);
    echo $name.PHP_EOL;
}
