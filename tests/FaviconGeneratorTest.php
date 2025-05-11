<?php

use Blockpoint\LaravelFaviconGenerator\LaravelFaviconGenerator;
use Illuminate\Support\Facades\File;

it('can generate favicons', function () {
    // Create a test image
    $testImagePath = sys_get_temp_dir().'/test-favicon-source.png';

    // Create a simple 100x100 test image
    $image = imagecreatetruecolor(100, 100);
    imagefill($image, 0, 0, imagecolorallocate($image, 255, 0, 0));
    imagepng($image, $testImagePath);
    imagedestroy($image);

    // Make sure the test image exists
    expect(File::exists($testImagePath))->toBeTrue();

    // Set up the output path for testing
    config(['favicon-generator.output_path' => 'favicon-test']);

    // Clean up any existing test files
    $outputDir = public_path('favicon-test');
    if (File::exists($outputDir)) {
        File::deleteDirectory($outputDir);
    }

    // Generate the favicons
    $generator = new LaravelFaviconGenerator;
    $generatedFiles = $generator->generate($testImagePath);

    // Check that files were generated
    expect($generatedFiles)->not->toBeEmpty();

    // Check that the output directory exists
    expect(File::exists($outputDir))->toBeTrue();

    // Check that the expected files exist
    expect(File::exists(public_path('favicon-test/favicon.ico')))->toBeTrue();
    expect(File::exists(public_path('favicon-test/favicon-96x96.png')))->toBeTrue();
    expect(File::exists(public_path('favicon-test/apple-touch-icon.png')))->toBeTrue();
    expect(File::exists(public_path('favicon-test/site.webmanifest')))->toBeTrue();

    // Clean up
    File::deleteDirectory($outputDir);
    if (File::exists($testImagePath)) {
        File::delete($testImagePath);
    }
});
