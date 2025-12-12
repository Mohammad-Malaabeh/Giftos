<?php

namespace App\Support;

class Media
{
    public static function picture(string $relativePath, string $alt = '', array $sizes = [320, 480, 640, 768, 1024, 1280], array $attrs = []): string
    {
        $src = asset('storage/' . $relativePath);
        $ext = pathinfo($relativePath, PATHINFO_EXTENSION);
        $base = preg_replace('/\.' . preg_quote($ext, '/') . '$/i', '', $relativePath);

        $webpSet = [];
        $avifSet = [];
        $origSet = [];

        foreach ($sizes as $w) {
            $webpSet[] = asset('storage/' . $base . "-{$w}w.webp") . " {$w}w";
            $avifSet[] = asset('storage/' . $base . "-{$w}w.avif") . " {$w}w";
            $origSet[] = asset('storage/' . $base . "-{$w}w." . strtolower($ext)) . " {$w}w";
        }

        $attrStr = '';
        foreach ($attrs as $k => $v) {
            $attrStr .= ' ' . htmlspecialchars($k) . '="' . htmlspecialchars((string)$v) . '"';
        }

        $html = '<picture>';
        $html .= '<source type="image/avif" srcset="' . e(implode(', ', $avifSet)) . '">';
        $html .= '<source type="image/webp" srcset="' . e(implode(', ', $webpSet)) . '">';
        $html .= '<img src="' . e($src) . '" srcset="' . e(implode(', ', $origSet)) . '" alt="' . e($alt) . '"' . $attrStr . '>';
        $html .= '</picture>';

        return $html;
    }
}
