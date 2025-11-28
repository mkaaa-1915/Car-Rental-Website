<?php
/**
 * helpers.php
 * Shared helpers used by multiple pages.
 * Place this file in includes/ and require it from config.php.
 */

/**
 * Escape HTML safely (null-safe)
 */
if (!function_exists('h')) {
    function h($s) {
        return htmlspecialchars($s ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

/**
 * Resolve an image path stored in DB into something usable in <img src="">
 * Returns a string path or null if not found.
 */
if (!function_exists('resolve_image_url')) {
    function resolve_image_url(?string $imgPath): ?string {
        $imgPath = trim((string)$imgPath);
        if ($imgPath === '') return null;

        // If it's already a full URL, return as-is
        if (preg_match('#^https?://#i', $imgPath)) {
            return $imgPath;
        }

        $candidates = [];

        // 1) relative to project root (one level up from includes/)
        $candidates[] = __DIR__ . '/../' . ltrim($imgPath, '/');

        // 2) relative to document root
        if (!empty($_SERVER['DOCUMENT_ROOT'])) {
            $candidates[] = rtrim($_SERVER['DOCUMENT_ROOT'], DIRECTORY_SEPARATOR) . '/' . ltrim($imgPath, '/');
        }

        // 3) uploads directory fallback
        $candidates[] = __DIR__ . '/../uploads/' . basename($imgPath);
        if (!empty($_SERVER['DOCUMENT_ROOT'])) {
            $candidates[] = rtrim($_SERVER['DOCUMENT_ROOT'], DIRECTORY_SEPARATOR) . '/uploads/' . basename($imgPath);
        }

        foreach ($candidates as $fs) {
            if ($fs && file_exists($fs) && is_file($fs)) {
                // Prefer returning the original stored path when possible (so web path remains same)
                if (!preg_match('#^/#', $imgPath)) {
                    return $imgPath;
                }
                return $imgPath;
            }
        }

        return null;
    }
}