<?php

/**
 * Layout for consent-aware YouTube embed
 *
 * @package     Weltspiegel\Component\Weltspiegel
 * @copyright   Weltspiegel Cottbus
 * @license     MIT
 */

\defined('_JEXEC') or die;

/**
 * Layout variables
 * ----------------
 * @var string $videoId     YouTube video ID
 * @var bool   $responsive  Use responsive 16:9 container (default: true)
 * @var string $placeholder Placeholder text (default: "YouTube-Trailer verfügbar")
 * @var string $hint        Hint text (default: "Bitte aktiviere YouTube...")
 */

$videoId = $displayData['videoId'] ?? '';
$responsive = $displayData['responsive'] ?? true;
$placeholder = $displayData['placeholder'] ?? 'YouTube-Trailer verfügbar';
$hint = $displayData['hint'] ?? 'Bitte aktiviere YouTube in den Cookie-Einstellungen';

if (empty($videoId)) {
    return;
}

$embedUrl = "https://www.youtube-nocookie.com/embed/{$videoId}";
$uniqueId = 'yt-' . $videoId . '-' . uniqid();

?>
<?php if ($responsive): ?>
<div style="max-width: 640px; margin: 1.5rem auto;">
    <div style="padding-top: 56.25%; position: relative; overflow: hidden;">
<?php else: ?>
<div style="position: relative; margin: 1.5rem auto;">
<?php endif; ?>

        <!-- Placeholder -->
        <div id="<?= $uniqueId ?>-placeholder" class="position-absolute top-0 start-0 w-100 h-100 bg-dark d-flex align-items-center justify-content-center" style="display: none;">
            <div class="text-center text-white p-4">
                <svg width="64" height="64" fill="currentColor" class="mb-3" viewBox="0 0 16 16">
                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                    <path d="M6.271 5.055a.5.5 0 0 1 .52.038l3.5 2.5a.5.5 0 0 1 0 .814l-3.5 2.5A.5.5 0 0 1 6 10.5v-5a.5.5 0 0 1 .271-.445z"/>
                </svg>
                <p class="mb-0"><?= htmlspecialchars($placeholder) ?></p>
                <small><?= htmlspecialchars($hint) ?></small>
            </div>
        </div>

        <!-- YouTube iframe -->
        <iframe id="<?= $uniqueId ?>-iframe"
                src=""
                data-src="<?= htmlspecialchars($embedUrl) ?>"
                frameborder="0"
                allowfullscreen
                referrerpolicy="strict-origin-when-cross-origin"
                style="<?= $responsive ? 'position: absolute; top: 0; left: 0; width: 100%; height: 100%;' : 'width: 640px; height: 360px;' ?> display: none;">
        </iframe>

<?php if ($responsive): ?>
    </div>
</div>
<?php else: ?>
</div>
<?php endif; ?>

<script>
(function() {
    var placeholder = document.getElementById('<?= $uniqueId ?>-placeholder');
    var iframe = document.getElementById('<?= $uniqueId ?>-iframe');

    function checkConsent() {
        try {
            var consent = localStorage.getItem('cookie_consent');
            if (consent === 'granted') {
                // Only show iframe after it has loaded
                iframe.addEventListener('load', function() {
                    iframe.style.display = 'block';
                    placeholder.style.display = 'none';
                }, { once: true });
                iframe.src = iframe.dataset.src;
            } else {
                placeholder.style.display = 'flex';
                iframe.style.display = 'none';
            }
        } catch (e) {
            placeholder.style.display = 'flex';
            iframe.style.display = 'none';
        }
    }

    checkConsent();
    window.addEventListener('cookieConsentChanged', checkConsent);
})();
</script>
