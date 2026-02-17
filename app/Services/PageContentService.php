<?php

namespace App\Services;

use Purifier;

class PageContentService
{
    /**
     * Process page content: clean HTML, convert Markdown, add responsive classes.
     *
     * @param string $content The raw content (HTML or Markdown)
     * @param string $format The content format ('html' or 'markdown')
     * @return string The processed content
     */
    public static function process($content, $format = 'html')
    {
        // Convert Markdown to HTML if needed
        if ($format === 'markdown') {
            $content = self::markdownToHtml($content);
        }

        // Clean HTML content
        $content = self::cleanHtml($content);

        // Add responsive classes to images
        $content = self::makeImagesResponsive($content);

        // Format code blocks and tables
        $content = self::formatCodeBlocks($content);
        $content = self::formatTables($content);

        return $content;
    }

    /**
     * Convert Markdown to HTML.
     *
     * @param string $markdown
     * @return string
     */
    private static function markdownToHtml($markdown)
    {
        $parsedown = new \Parsedown();
        return $parsedown->text($markdown);
    }

    /**
     * Clean HTML content using HTML Purifier.
     *
     * @param string $html
     * @return string
     */
    private static function cleanHtml($html)
    {
        return Purifier::clean($html, 'default');
    }

    /**
     * Add responsive classes to images.
     *
     * @param string $html
     * @return string
     */
    private static function makeImagesResponsive($html)
    {
        // Add responsive classes to img tags
        $html = preg_replace_callback(
            '/<img\s+([^>]*?)>/i',
            function ($matches) {
                $attrs = $matches[1];

                // Check if class attribute already exists
                if (preg_match('/class\s*=\s*["\']([^"\']*)["\']/', $attrs)) {
                    // Add to existing class
                    $attrs = preg_replace(
                        '/class\s*=\s*["\']([^"\']*)["\']/',
                        'class="$1 img-fluid"',
                        $attrs
                    );
                } else {
                    // Add new class attribute
                    $attrs .= ' class="img-fluid"';
                }

                // Add style for max-width if not already present
                if (!preg_match('/style\s*=/', $attrs)) {
                    $attrs .= ' style="max-width: 100%; height: auto; border-radius: 5px; margin: 1rem 0;"';
                }

                return '<img ' . $attrs . '>';
            },
            $html
        );

        return $html;
    }

    /**
     * Format code blocks with proper styling.
     *
     * @param string $html
     * @return string
     */
    private static function formatCodeBlocks($html)
    {
        // Add classes to pre and code tags
        $html = preg_replace_callback(
            '/<pre\s*([^>]*)>/i',
            function ($matches) {
                $attrs = $matches[1];
                if (preg_match('/class\s*=\s*["\']([^"\']*)["\']/', $attrs)) {
                    $attrs = preg_replace(
                        '/class\s*=\s*["\']([^"\']*)["\']/',
                        'class="$1 code-block"',
                        $attrs
                    );
                } else {
                    $attrs .= ' class="code-block"';
                }
                return '<pre ' . $attrs . '>';
            },
            $html
        );

        return $html;
    }

    /**
     * Format tables with Bootstrap classes.
     *
     * @param string $html
     * @return string
     */
    private static function formatTables($html)
    {
        // Add Bootstrap table classes
        $html = preg_replace_callback(
            '/<table\s*([^>]*)>/i',
            function ($matches) {
                $attrs = $matches[1];
                if (preg_match('/class\s*=\s*["\']([^"\']*)["\']/', $attrs)) {
                    $attrs = preg_replace(
                        '/class\s*=\s*["\']([^"\']*)["\']/',
                        'class="$1 table table-striped table-bordered"',
                        $attrs
                    );
                } else {
                    $attrs .= ' class="table table-striped table-bordered"';
                }
                return '<table ' . $attrs . '>';
            },
            $html
        );

        return $html;
    }
}
