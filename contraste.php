<?php

$content = file_get_contents('tailwind.config.js');
$colors = array();

if (preg_match_all('/([a-z]+):\s*\{([^{}]*)\}/s', $content, $m, PREG_SET_ORDER)) {
    foreach ($m as $b) {
        if (preg_match_all('/([0-9]+):\s*[\'"]#([0-9a-fA-F]{6})[\'"]/', $b[2], $im, PREG_SET_ORDER)) {
            foreach ($im as $i) {
                $colors[$b[1] . '-' . $i[1]] = strtoupper($i[2]);
            }
        }
    }
}

if (preg_match_all('/([a-z]+):\s*[\'"]#([0-9a-fA-F]{6})[\'"]/', $content, $sm, PREG_SET_ORDER)) {
    foreach ($sm as $s) {
        if (in_array($s[1], array('papel', 'marfil'))) {
            $colors[$s[1]] = strtoupper($s[2]);
        }
    }
}

function h2r($h) {
    $r = hexdec(substr($h, 0, 2)) / 255;
    $g = hexdec(substr($h, 2, 2)) / 255;
    $b = hexdec(substr($h, 4, 2)) / 255;
    return array($r, $g, $b);
}

function lum($h) {
    list($r, $g, $b) = h2r($h);
    $arr = array($r, $g, $b);
    foreach ($arr as &$c) {
        if ($c <= 0.03928) {
            $c = $c / 12.92;
        } else {
            $c = pow(($c + 0.055) / 1.055, 2.4);
        }
    }
    unset($c);
    list($r2, $g2, $b2) = $arr;
    return 0.2126 * $r2 + 0.7152 * $g2 + 0.0722 * $b2;
}

function con($c1, $c2) {
    $l1 = lum($c1);
    $l2 = lum($c2);
    if ($l1 < $l2) {
        $t = $l1; $l1 = $l2; $l2 = $t;
    }
    return ($l1 + 0.05) / ($l2 + 0.05);
}

$colors['blanco'] = 'FFFFFF';

echo json_encode($colors, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL . PHP_EOL;

$pairs = array(
    array('oro-500','marfil'),array('oro-500','papel'),array('oro-500','blanco'),
    array('oro-600','marfil'),array('oro-600','papel'),array('oro-600','blanco'),
    array('oro-700','marfil'),array('oro-700','papel'),array('oro-700','blanco'),
    array('oro-800','marfil'),array('oro-800','papel'),array('oro-800','blanco'),
    array('oro-900','marfil'),array('oro-900','papel'),array('oro-900','blanco'),
    array('neutral-400','marfil'),array('neutral-400','papel'),array('neutral-400','blanco'),
    array('neutral-500','marfil'),array('neutral-500','papel'),array('neutral-500','blanco'),
    array('neutral-600','marfil'),array('neutral-600','papel'),array('neutral-600','blanco'),
    array('neutral-700','marfil'),array('neutral-700','papel'),array('neutral-700','blanco'),
    array('papel','verde-700'),array('marfil','verde-700'),
    array('papel','verde-800'),array('marfil','verde-800'),
    array('papel','verde-950'),array('marfil','verde-950'),
    array('verde-700','marfil'),array('verde-800','marfil'),array('verde-950','marfil'),
    array('verde-950','oro-500'),array('verde-800','oro-500'),
);

foreach ($pairs as $p) {
    list($fg, $bg) = $p;
    if (!isset($colors[$fg]) || !isset($colors[$bg])) {
        echo $fg . '/' . $bg . ': SIN COLOR' . PHP_EOL;
        continue;
    }
    $r = con($colors[$fg], $colors[$bg]);
    echo $fg . '/' . $bg . ': ' . number_format($r, 2) . ':1 | normal(4.5)=' . ($r >= 4.5 ? 'PASS' : 'FAIL') . ' | grande(3.0)=' . ($r >= 3.0 ? 'PASS' : 'FAIL') . PHP_EOL;
}
