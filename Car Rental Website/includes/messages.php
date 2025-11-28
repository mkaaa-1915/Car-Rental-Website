<?php
// Start session if not already started (avoid notices)
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

// Build the flash HTML (server-side messages are unset immediately so they won't persist across reloads)
$flashes = [];

if (isset($_SESSION['message'])) {
    $text = htmlspecialchars($_SESSION['message']);
    $flashes[] = [
        'id' => 'flash-success',
        'text' => $text,
        'bg' => '#4CAF50'
    ];
    unset($_SESSION['message']);
}

if (isset($_SESSION['error'])) {
    $text = htmlspecialchars($_SESSION['error']);
    $flashes[] = [
        'id' => 'flash-error',
        'text' => $text,
        'bg' => '#f44336'
    ];
    unset($_SESSION['error']);
}

// Output flash markup. Inline script below will hide & remove elements after 5s.
if (!empty($flashes)) {
    foreach ($flashes as $f) {
        echo '<div id="' . $f['id'] . '" class="flash-notice" data-flash="true" role="status"'
            . ' style="position: fixed; top: 80px; right: 20px; background: ' . $f['bg'] . '; color: white;'
            . ' padding: 15px 20px; border-radius: 5px; z-index: 1000; box-shadow: 0 4px 6px rgba(0,0,0,0.1);'
            . ' transition: opacity 0.45s ease, transform 0.45s ease; opacity: 1; transform: translateY(0);">'
            . $f['text']
            . '</div>';
    }

    // Inline script: runs right away on the page where flashes were printed.
    echo '<script>(function(){try{const HIDE_AFTER_MS=5000;const REMOVE_AFTER_MS=5600;'
        . 'const flashes=Array.from(document.querySelectorAll("[data-flash]"));'
        . 'if(!flashes.length) return;'
        . 'setTimeout(function(){flashes.forEach(function(el){el.style.opacity="0";el.style.transform="translateY(-10px)";});},HIDE_AFTER_MS);'
        . 'setTimeout(function(){flashes.forEach(function(el){if(el && el.parentNode) el.parentNode.removeChild(el);});},REMOVE_AFTER_MS);'
        . '}catch(e){console && console.error && console.error("Flash hide error:",e);} })();</script>';
}
?>