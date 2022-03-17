<?php

namespace App\Service;

class Slugify
{
    public function generate(string $input): string
    {
        $a = ['à', 'ç', '!', '.', '-', '_', ',',];
        $b = ['a', 'c', '', '', '', '', '',];
        $wordLower = strtolower($input);
        $cleanString = str_replace($a, $b, $wordLower);
        return str_replace(' ', '-', $cleanString);
    }
}