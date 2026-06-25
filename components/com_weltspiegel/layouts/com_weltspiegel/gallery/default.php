<?php

/**
 * Layout for image gallery
 *
 * @package     Weltspiegel\Component\Weltspiegel
 * @copyright   Weltspiegel Cottbus
 * @license     MIT
 */

\defined('_JEXEC') or die;

/**
 * Layout variables
 * ----------------
 * @var string $folder   Relative path to the image folder (e.g. "images/vorschauen/album")
 * @var array  $images   List of image file paths relative to site root
 * @var array  $options  Parsed options from the tag (cols, preview, etc.)
 */

$folder = $displayData['folder'] ?? '';
$images = $displayData['images'] ?? [];
$options = $displayData['options'] ?? [];

if (empty($images)) {
    return;
}

$cols = (int) ($options['cols'] ?? 3);
$cols = max(1, min(6, $cols));
$galleryId = 'gallery-' . md5($folder);

?>
<div class="gallery" id="<?= htmlspecialchars($galleryId) ?>" style="display: grid; grid-template-columns: repeat(<?= $cols ?>, 1fr); gap: 0.5rem;">
<?php foreach ($images as $image): ?>
    <?php
        $filename = pathinfo($image, PATHINFO_FILENAME);
        $alt = str_replace(['-', '_'], ' ', $filename);
    ?>
    <div class="gallery__item">
        <img
            class="gallery__image"
            src="<?= htmlspecialchars($image) ?>"
            alt="<?= htmlspecialchars($alt) ?>"
            loading="lazy"
        >
    </div>
<?php endforeach; ?>
</div>
