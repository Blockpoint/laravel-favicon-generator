# Changelog

All notable changes to `laravel-favicon-generator` will be documented in this file.

## 1.1.0 - 2024-XX-XX

### Added
- Added support for high-quality PNG generation using Imagick when available
- Added fallback to GD when Imagick is not available
- Added SVG favicon generation for any source image format

### Changed
- Significantly improved the quality of generated PNG icons, especially for larger sizes
- Enhanced resizing logic to maintain aspect ratio while ensuring exact dimensions
- Optimized PNG compression settings for maximum quality
- Improved SVG generation with multiple fallback methods
- Removed unnecessary ExampleTest file

## 1.0.0 - 2024-XX-XX

- Initial release
