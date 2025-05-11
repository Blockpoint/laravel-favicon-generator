<?php

namespace Blockpoint\LaravelFaviconGenerator;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\ImageInterface;

class LaravelFaviconGenerator
{
    protected ImageManager $imageManager;
    protected string $outputPath;
    protected array $generatedFiles = [];

    public function __construct()
    {
        // Use Imagick driver if available, otherwise fall back to GD
        if (extension_loaded('imagick')) {
            $driver = new ImagickDriver();
        } else {
            $driver = new Driver();
        }

        // Create image manager with configured driver
        $this->imageManager = new ImageManager($driver);
        $this->outputPath = config('favicon-generator.output_path', 'favicon');
    }

    /**
     * Generate all favicons from a source image
     *
     * @param string $sourceImagePath Path to the source image
     * @param array $manifestOptions Optional manifest options (name, short_name, theme_color, background_color)
     * @return array List of generated files
     */
    public function generate(string $sourceImagePath, array $manifestOptions = []): array
    {
        if (!File::exists($sourceImagePath)) {
            throw new \InvalidArgumentException("Source image not found: {$sourceImagePath}");
        }

        $this->generatedFiles = [];
        $this->ensureOutputDirectoryExists();

        $sourceImage = $this->imageManager->read($sourceImagePath);

        // Generate each favicon type
        $this->generateIcoFavicon($sourceImage);
        $this->generatePngFavicon($sourceImage);
        $this->generateSvgFavicon($sourceImage, $sourceImagePath);
        $this->generateAppleTouchIcon($sourceImage);
        $this->generateWebAppManifestIcons($sourceImage);
        $this->generateWebManifest($manifestOptions);

        return $this->generatedFiles;
    }

    /**
     * Generate ICO favicon
     */
    protected function generateIcoFavicon(ImageInterface $sourceImage): void
    {
        $config = config('favicon-generator.favicon_types.ico');
        $filename = $config['filename'] ?? 'favicon.ico';
        $sizes = $config['sizes'] ?? [16, 32, 48];
        $quality = $config['quality'] ?? 100;

        // For ICO files, we need to create multiple sizes and combine them
        // Since Intervention Image v3 doesn't support direct ICO creation,
        // we'll create individual PNGs and use GD to create the ICO
        $tempImages = [];

        foreach ($sizes as $size) {
            $tempPath = sys_get_temp_dir() . "/favicon_{$size}.png";

            // Use direct GD functions for maximum quality
            $this->createHighQualityPng($sourceImage, $size, $tempPath);
            $tempImages[] = $tempPath;
        }

        // Use GD to create the ICO file
        $outputPath = public_path("{$this->outputPath}/{$filename}");
        $this->createIcoFromPngs($tempImages, $outputPath);

        // Clean up temp files
        foreach ($tempImages as $tempPath) {
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
        }

        $this->generatedFiles[] = "{$this->outputPath}/{$filename}";
    }

    /**
     * Generate PNG favicon
     */
    protected function generatePngFavicon(ImageInterface $sourceImage): void
    {
        $config = config('favicon-generator.favicon_types.png');
        $filename = $config['filename'] ?? 'favicon-96x96.png';
        $size = $config['size'] ?? 96;
        $quality = $config['quality'] ?? 100;

        $outputPath = public_path("{$this->outputPath}/{$filename}");

        // Use direct GD functions for maximum quality
        $this->createHighQualityPng($sourceImage, $size, $outputPath);

        $this->generatedFiles[] = "{$this->outputPath}/{$filename}";
    }

    /**
     * Generate SVG favicon (copy if source is SVG, otherwise convert)
     *
     * @param ImageInterface $sourceImage The source image object
     * @param string $sourceImagePath Path to the source image file
     * @return void
     */
    protected function generateSvgFavicon(ImageInterface $sourceImage, string $sourceImagePath): void
    {
        $config = config('favicon-generator.favicon_types.svg');
        $filename = $config['filename'] ?? 'favicon.svg';
        $outputPath = public_path("{$this->outputPath}/{$filename}");

        // If source is already SVG, just copy it
        if (pathinfo($sourceImagePath, PATHINFO_EXTENSION) === 'svg') {
            File::copy($sourceImagePath, $outputPath);
            $this->generatedFiles[] = "{$this->outputPath}/{$filename}";
            return;
        }

        // Try to convert to SVG using Imagick if available
        if (extension_loaded('imagick') && class_exists('\Imagick')) {
            try {
                // Create SVG using Imagick
                $this->createSvgFromImage($sourceImagePath, $outputPath);
                $this->generatedFiles[] = "{$this->outputPath}/{$filename}";
                return;
            } catch (\Exception $e) {
                // If Imagick conversion fails, we'll try the fallback method
            }
        }

        // Fallback: Create a simple SVG wrapper around a PNG data URI
        try {
            // Create a temporary PNG file
            $tempPngPath = sys_get_temp_dir() . '/temp_favicon_' . uniqid() . '.png';
            $this->createHighQualityPng($sourceImage, 512, $tempPngPath); // Use a large size for quality

            // Convert PNG to base64 data URI
            $pngData = base64_encode(file_get_contents($tempPngPath));
            $dataUri = 'data:image/png;base64,' . $pngData;

            // Create a simple SVG that embeds the PNG as an image with a square viewBox
            $svgContent = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="512" height="512" viewBox="0 0 512 512">
  <image width="512" height="512" xlink:href="{$dataUri}"/>
</svg>
SVG;

            // Write the SVG file
            File::put($outputPath, $svgContent);

            // Clean up
            if (file_exists($tempPngPath)) {
                unlink($tempPngPath);
            }

            $this->generatedFiles[] = "{$this->outputPath}/{$filename}";
        } catch (\Exception $e) {
            // If all conversion methods fail, log the error but continue with other icons
            error_log("Failed to generate SVG favicon: {$e->getMessage()}");
        }
    }

    /**
     * Generate Apple Touch Icon
     */
    protected function generateAppleTouchIcon(ImageInterface $sourceImage): void
    {
        $config = config('favicon-generator.favicon_types.apple_touch_icon');
        $filename = $config['filename'] ?? 'apple-touch-icon.png';
        $size = $config['size'] ?? 180;
        $quality = $config['quality'] ?? 100;

        $outputPath = public_path("{$this->outputPath}/{$filename}");

        // Use direct GD functions for maximum quality
        $this->createHighQualityPng($sourceImage, $size, $outputPath);

        $this->generatedFiles[] = "{$this->outputPath}/{$filename}";
    }

    /**
     * Generate Web App Manifest Icons
     */
    protected function generateWebAppManifestIcons(ImageInterface $sourceImage): void
    {
        $config = config('favicon-generator.favicon_types.web_app_manifest_icons');
        $sizes = $config['sizes'] ?? [192, 512];
        $filenamePattern = $config['filename_pattern'] ?? 'web-app-manifest-{size}x{size}.png';
        $quality = $config['quality'] ?? 100;

        foreach ($sizes as $size) {
            $filename = str_replace('{size}', $size, $filenamePattern);
            $outputPath = public_path("{$this->outputPath}/{$filename}");

            // For larger icons (512px), use direct GD functions for maximum quality
            if ($size >= 512) {
                $this->createHighQualityPng($sourceImage, $size, $outputPath);
            } else {
                // For smaller icons, use the standard approach
                $resizedImage = $this->createExactSizeImage($sourceImage, $size, $size);
                $resizedImage->toPng(interlaced: false, indexed: false)->save($outputPath);
            }

            $this->generatedFiles[] = "{$this->outputPath}/{$filename}";
        }
    }

    /**
     * Generate Web Manifest file (site.webmanifest)
     *
     * @param array $options Optional manifest options (name, short_name, theme_color, background_color)
     */
    protected function generateWebManifest(array $options = []): void
    {
        $config = config('favicon-generator.web_manifest');
        $filename = $config['filename'] ?? 'site.webmanifest';
        $content = $config['content'] ?? [];

        // Apply custom options if provided
        if (!empty($options['name'])) {
            $content['name'] = $options['name'];
        }

        if (!empty($options['short_name'])) {
            $content['short_name'] = $options['short_name'];
        }

        if (!empty($options['theme_color'])) {
            $content['theme_color'] = $options['theme_color'];
        }

        if (!empty($options['background_color'])) {
            $content['background_color'] = $options['background_color'];
        }

        // Add icons to manifest
        $manifestIcons = [];
        $iconConfig = config('favicon-generator.favicon_types.web_app_manifest_icons');
        $sizes = $iconConfig['sizes'] ?? [192, 512];
        $filenamePattern = $iconConfig['filename_pattern'] ?? 'web-app-manifest-{size}x{size}.png';

        foreach ($sizes as $size) {
            $iconFilename = str_replace('{size}', $size, $filenamePattern);
            $manifestIcons[] = [
                'src' => "/{$this->outputPath}/{$iconFilename}",
                'sizes' => "{$size}x{$size}",
                'type' => 'image/png',
            ];
        }

        $content['icons'] = $manifestIcons;

        // Write manifest file
        $manifestPath = public_path("{$this->outputPath}/{$filename}");
        File::put($manifestPath, json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $this->generatedFiles[] = "{$this->outputPath}/{$filename}";
    }

    /**
     * Ensure the output directory exists
     */
    protected function ensureOutputDirectoryExists(): void
    {
        $outputDir = public_path($this->outputPath);
        if (!File::exists($outputDir)) {
            File::makeDirectory($outputDir, 0755, true);
        }
    }

    /**
     * Create an ICO file from multiple PNG files
     * Improved implementation to create a proper ICO file
     */
    protected function createIcoFromPngs(array $pngPaths, string $outputPath): void
    {
        // Ensure output directory exists
        $outputDir = dirname($outputPath);
        if (!file_exists($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        // If we have multiple PNG files, create a proper ICO file
        if (!empty($pngPaths)) {
            // Create a new ICO file
            $fp = fopen($outputPath, 'wb');
            if (!$fp) {
                throw new \RuntimeException("Could not open file {$outputPath} for writing");
            }

            // ICO header (6 bytes)
            // 0-1: Reserved (0)
            // 2-3: Type (1 for ICO)
            // 4-5: Number of images
            $numImages = count($pngPaths);
            fwrite($fp, pack('vvv', 0, 1, $numImages));

            // Calculate header size for offset calculation
            $headerSize = 6 + (16 * $numImages);
            $imageDataOffset = $headerSize;
            $imageData = '';
            $iconDirEntries = '';

            // Process each PNG and create directory entries
            foreach ($pngPaths as $index => $pngPath) {
                $pngData = file_get_contents($pngPath);
                if (!$pngData) {
                    continue;
                }

                // Get image dimensions
                $imageInfo = getimagesize($pngPath);
                if (!$imageInfo) {
                    continue;
                }

                $width = $imageInfo[0];
                $height = $imageInfo[1];
                $bpp = 32; // Bits per pixel (usually 32 for PNGs with alpha)

                // Create directory entry (16 bytes)
                // 0: Width (0 means 256)
                // 1: Height (0 means 256)
                // 2: Color palette size (0 for no palette)
                // 3: Reserved (0)
                // 4-5: Color planes (1 for ICO)
                // 6-7: Bits per pixel
                // 8-11: Size of image data in bytes
                // 12-15: Offset of image data from start of file
                $width = $width >= 256 ? 0 : $width;
                $height = $height >= 256 ? 0 : $height;

                $size = strlen($pngData);
                $iconDirEntries .= pack('CCCCvvVV',
                    $width, $height, 0, 0, 1, $bpp, $size, $imageDataOffset
                );

                $imageData .= $pngData;
                $imageDataOffset += $size;
            }

            // Write directory entries
            fwrite($fp, $iconDirEntries);

            // Write image data
            fwrite($fp, $imageData);

            fclose($fp);
        } elseif (isset($pngPaths[0])) {
            // Fallback: just use the first PNG if we can't create a proper ICO
            copy($pngPaths[0], $outputPath);
        }
    }

    /**
     * Create an image with exact dimensions while maintaining aspect ratio
     * This method ensures the output image has the exact width and height specified
     * by creating a canvas of the target size and placing the resized image centered on it
     *
     * @param ImageInterface $sourceImage The source image
     * @param int $width Target width
     * @param int $height Target height
     * @return ImageInterface The resized image with exact dimensions
     */
    protected function createExactSizeImage(ImageInterface $sourceImage, int $width, int $height): ImageInterface
    {
        // First, create a blank canvas with the exact dimensions (transparent background)
        $canvas = $this->imageManager->create($width, $height);

        // Get the source image dimensions
        $sourceWidth = $sourceImage->width();
        $sourceHeight = $sourceImage->height();

        // If the source image is already square and we need a square output,
        // use a direct resize for better quality
        if ($sourceWidth === $sourceHeight && $width === $height) {
            return $sourceImage->resize($width, $height);
        }

        // Calculate the resize dimensions while maintaining aspect ratio
        $ratio = min($width / $sourceWidth, $height / $sourceHeight);

        // Use ceiling to ensure we don't get dimensions smaller than needed
        $newWidth = (int) ceil($sourceWidth * $ratio);
        $newHeight = (int) ceil($sourceHeight * $ratio);

        // Make sure we don't exceed target dimensions
        $newWidth = min($newWidth, $width);
        $newHeight = min($newHeight, $height);

        // Resize the source image with high quality settings
        $resizedImage = $sourceImage->resize($newWidth, $newHeight);

        // Calculate the position to center the resized image on the canvas
        $posX = (int) (($width - $newWidth) / 2);
        $posY = (int) (($height - $newHeight) / 2);

        // Place the resized image on the canvas
        return $canvas->place($resizedImage, 'top-left', $posX, $posY);
    }

    /**
     * Create a high-quality PNG image using direct Imagick functions
     * This bypasses Intervention Image for maximum quality on large icons
     *
     * @param ImageInterface $sourceImage The source image
     * @param int $size Target size (width and height)
     * @param string $outputPath Path where the PNG should be saved
     * @return void
     */
    protected function createHighQualityPng(ImageInterface $sourceImage, int $size, string $outputPath): void
    {
        // Check if Imagick is available
        if (!extension_loaded('imagick')) {
            // Fallback to Intervention Image if Imagick is not available
            $resizedImage = $this->createExactSizeImage($sourceImage, $size, $size);
            $resizedImage->toPng(interlaced: false, indexed: false)->save($outputPath);
            return;
        }

        try {
            // Get the source image as a temporary file
            $tempSourcePath = sys_get_temp_dir() . '/source_image_' . uniqid() . '.png';
            $sourceImage->toPng()->save($tempSourcePath);

            // Create a new Imagick instance
            $imagick = new \Imagick($tempSourcePath);

            // Set the best quality settings
            $imagick->setImageCompressionQuality(100);
            $imagick->setOption('png:compression-level', '0'); // No compression
            $imagick->setOption('png:compression-strategy', '0');
            $imagick->setOption('png:exclude-chunk', 'all');
            $imagick->setInterlaceScheme(\Imagick::INTERLACE_NO);

            // Set the best filter for resizing
            $imagick->setImageResolution(300, 300); // High DPI
            $imagick->resampleImage(300, 300, \Imagick::FILTER_LANCZOS, 1);

            // Get source dimensions
            $sourceWidth = $imagick->getImageWidth();
            $sourceHeight = $imagick->getImageHeight();

            // Calculate dimensions to maintain aspect ratio
            $ratio = min($size / $sourceWidth, $size / $sourceHeight);
            $newWidth = (int) round($sourceWidth * $ratio);
            $newHeight = (int) round($sourceHeight * $ratio);

            // Resize with high quality
            $imagick->resizeImage($newWidth, $newHeight, \Imagick::FILTER_LANCZOS, 1);

            // Create a new canvas with transparent background
            $canvas = new \Imagick();
            $canvas->newImage($size, $size, new \ImagickPixel('transparent'));
            $canvas->setImageFormat('png');

            // Calculate position to center the image
            $posX = (int) (($size - $newWidth) / 2);
            $posY = (int) (($size - $newHeight) / 2);

            // Composite the resized image onto the canvas
            $canvas->compositeImage($imagick, \Imagick::COMPOSITE_OVER, $posX, $posY);

            // Ensure the output directory exists
            $outputDir = dirname($outputPath);
            if (!file_exists($outputDir)) {
                mkdir($outputDir, 0755, true);
            }

            // Save the image with maximum quality
            $canvas->writeImage($outputPath);

            // Clean up
            $imagick->clear();
            $canvas->clear();
            if (file_exists($tempSourcePath)) {
                unlink($tempSourcePath);
            }
        } catch (\Exception $e) {
            // Fallback to Intervention Image if Imagick fails
            $resizedImage = $this->createExactSizeImage($sourceImage, $size, $size);
            $resizedImage->toPng(interlaced: false, indexed: false)->save($outputPath);
        }
    }

    /**
     * Create an SVG file from an image using Imagick
     *
     * @param string $sourceImagePath Path to the source image
     * @param string $outputPath Path where the SVG should be saved
     * @return void
     * @throws \Exception If conversion fails
     */
    protected function createSvgFromImage(string $sourceImagePath, string $outputPath): void
    {
        // Create a new Imagick instance
        $imagick = new \Imagick($sourceImagePath);

        // Set high quality settings
        $imagick->setImageResolution(300, 300);
        $imagick->resampleImage(300, 300, \Imagick::FILTER_LANCZOS, 1);

        // Ensure the output directory exists
        $outputDir = dirname($outputPath);
        if (!file_exists($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        try {
            // Try to convert directly to SVG
            $imagick->setImageFormat('svg');
            $imagick->writeImage($outputPath);
        } catch (\Exception $e) {
            // If direct conversion fails, create a PNG and embed it in an SVG
            $tempPngPath = sys_get_temp_dir() . '/temp_svg_source_' . uniqid() . '.png';

            // Save as high-quality PNG
            $imagick->setImageFormat('png');
            $imagick->setImageCompressionQuality(100);
            $imagick->writeImage($tempPngPath);

            // Get image dimensions
            $width = $imagick->getImageWidth();
            $height = $imagick->getImageHeight();

            // Calculate the size for a square viewBox (use the larger dimension)
            $size = max($width, $height);

            // Convert PNG to base64 data URI
            $pngData = base64_encode(file_get_contents($tempPngPath));
            $dataUri = 'data:image/png;base64,' . $pngData;

            // Calculate centering position
            $posX = (int)(($size - $width) / 2);
            $posY = (int)(($size - $height) / 2);

            // Create an SVG that embeds the PNG as an image with a square viewBox
            $svgContent = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="{$size}" height="{$size}" viewBox="0 0 {$size} {$size}">
  <image width="{$width}" height="{$height}" x="{$posX}" y="{$posY}" xlink:href="{$dataUri}"/>
</svg>
SVG;

            // Write the SVG file
            File::put($outputPath, $svgContent);

            // Clean up
            if (file_exists($tempPngPath)) {
                unlink($tempPngPath);
            }
        }

        // Clean up
        $imagick->clear();
    }
}
