<?php
require_once __DIR__ . '/../vendor/autoload.php';
use Intervention\Image\ImageManager;

function generateCardImage($message, $background, $design) {
    // Create a new image with specified dimensions
    $width = 800;
    $height = 600;
    
    // Initialize Intervention Image
    $manager = new ImageManager(['driver' => 'gd']);
    $img = $manager->canvas($width, $height);
    
    // Set background gradient based on template
    switch ($background) {
        case 'bg-primary':
            $startColor = [78, 84, 200];
            $endColor = [143, 148, 251];
            break;
        case 'bg-success':
            $startColor = [67, 160, 71];
            $endColor = [102, 187, 106];
            break;
        case 'bg-info':
            $startColor = [2, 136, 209];
            $endColor = [79, 195, 247];
            break;
        case 'bg-warning':
            $startColor = [251, 140, 0];
            $endColor = [255, 167, 38];
            break;
        default:
            $startColor = [229, 57, 53];
            $endColor = [239, 83, 80];
    }
    
    // Draw gradient background
    for ($i = 0; $i < $height; $i++) {
        $ratio = $i / $height;
        $r = $startColor[0] + ($endColor[0] - $startColor[0]) * $ratio;
        $g = $startColor[1] + ($endColor[1] - $startColor[1]) * $ratio;
        $b = $startColor[2] + ($endColor[2] - $startColor[2]) * $ratio;
        $img->line(0, $i, $width, $i, function ($draw) use ($r, $g, $b) {
            $draw->color([$r, $g, $b, 1.0]);
        });
    }
    
    // Add design icon
    $iconMap = [
        'stars' => '✨',
        'balloons' => '🎈',
        'confetti' => '🎉',
        'cake' => '🎂',
        'gifts' => '🎁'
    ];
    
    // Add the icon as a watermark
    $icon = $iconMap[$design] ?? '✨';
    $img->text($icon, $width/2, $height/2, function($font) {
        $font->size(120);
        $font->color([255, 255, 255, 0.2]);
        $font->align('center');
        $font->valign('center');
    });
    
    // Add the message
    $lines = explode("\n", wordwrap($message, 40));
    $y = $height/2 - ((count($lines) * 40) / 2);
    foreach ($lines as $line) {
        $img->text($line, $width/2, $y, function($font) {
            $font->size(40);
            $font->color([255, 255, 255, 1.0]);
            $font->align('center');
            $font->valign('center');
        });
        $y += 40;
    }
    
    // Create cards directory if it doesn't exist
    $cardsDir = __DIR__ . '/../uploads/cards';
    if (!file_exists($cardsDir)) {
        mkdir($cardsDir, 0777, true);
    }
    
    // Save the image
    $filename = 'card_' . time() . '.png';
    $filepath = $cardsDir . '/' . $filename;
    $img->save($filepath);
    
    return $filepath;
}
