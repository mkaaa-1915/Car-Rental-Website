<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn() || !isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Accept JSON body or form POST
$raw = file_get_contents('php://input');
$input = json_decode($raw, true);
if (!is_array($input)) {
    // fallback to $_POST (forms)
    $input = $_POST;
}

$csrf = $input['csrf_token'] ?? $input['csrf'] ?? '';
if (empty($csrf) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

$name = trim((string)($input['name'] ?? ''));
$car_id = isset($input['car_id']) ? (int)$input['car_id'] : 0;

// If car_id provided but name empty, load from DB
if ($car_id > 0 && $name === '') {
    $stmt = mysqli_prepare($conn, "SELECT name FROM cars WHERE id = ? LIMIT 1");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $car_id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $row = $res ? mysqli_fetch_assoc($res) : null;
        mysqli_stmt_close($stmt);
        if ($row) $name = trim((string)$row['name']);
    }
}

if ($name === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Vehicle name is required']);
    exit;
}

// Prompt for a short rental listing description
$prompt = "Write a concise, friendly, 1-2 sentence description for this vehicle: \"{$name}\". "
    . "Focus on rental appeal (comfort, typical use, why someone should rent it). Keep it under 30 words.";

// If no remote API configured, return a local fallback
if (empty(GEMINI_API_KEY) || empty(GEMINI_API_URL)) {
    $fallback = "{$name} is a reliable, comfortable choice for city and longer trips — ideal for drivers seeking performance and convenience on their rental.";
    echo json_encode(['success' => true, 'description' => $fallback, 'source' => 'local-fallback']);
    exit;
}

// Build payload - many providers expect different shapes.
// We send a small, common payload but you may need to adjust to your provider's required shape.
$payload = [
    // Provider-specific keys may vary; 'prompt' is common fallback for many proxies
    'prompt' => $prompt,
    'max_output_tokens' => 60,
    'temperature' => 0.7,
];

// Setup cURL
$ch = curl_init(GEMINI_API_URL);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
$headers = [
    'Content-Type: application/json',
];

// Use Authorization Bearer token if provided
$apiKey = GEMINI_API_KEY;
if ($apiKey) {
    // If you stored a raw API key, use "Bearer <key>".
    // If your provider requires a different header (e.g. x-api-key), change this accordingly.
    $headers[] = 'Authorization: Bearer ' . $apiKey;
}
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_TIMEOUT, 15);

// Execute
$response = curl_exec($ch);
$curlErr = curl_error($ch);
$httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($response === false || $curlErr) {
    http_response_code(502);
    echo json_encode(['success' => false, 'message' => 'LLM request failed: ' . ($curlErr ?: 'unknown')]);
    exit;
}

// Try parse JSON (some providers return JSON, some may return plain text)
$respJson = json_decode($response, true);
$desc = null;

// 1) If parsed JSON, try common shapes
if (is_array($respJson)) {
    // SDK-like result: { "text": "..." } or { "output": [{"content":[{"text":"..."}]}] }
    if (isset($respJson['text']) && is_string($respJson['text'])) {
        $desc = trim($respJson['text']);
    } elseif (isset($respJson['output'][0]['content'][0]['text'])) {
        $desc = trim($respJson['output'][0]['content'][0]['text']);
    } elseif (isset($respJson['choices'][0]['message']['content'])) {
        // OpenAI-like chat completion object
        $desc = trim($respJson['choices'][0]['message']['content']);
    } elseif (isset($respJson['choices'][0]['text'])) {
        $desc = trim($respJson['choices'][0]['text']);
    } elseif (isset($respJson['candidates'][0]['content'])) {
        // Google's older GenAI shapes sometimes use candidates[].content
        $cand = $respJson['candidates'][0]['content'];
        if (is_string($cand)) $desc = trim($cand);
        elseif (is_array($cand)) {
            // content could be array of objects with 'text'
            foreach ($cand as $item) {
                if (is_string($item) && strlen($item) > 0) { $desc = trim($item); break; }
                if (is_array($item) && isset($item['text'])) { $desc = trim($item['text']); break; }
            }
        }
    } elseif (isset($respJson['output_text']) && is_string($respJson['output_text'])) {
        $desc = trim($respJson['output_text']);
    } else {
        // As last resort, search for a top-level string field with meaningful length
        foreach ($respJson as $k => $v) {
            if (is_string($v) && strlen($v) > 10) { $desc = trim($v); break; }
        }
    }
} else {
    // If response is not JSON, treat raw response as description
    $plain = trim($response);
    if ($plain !== '') $desc = $plain;
}

// If still empty, return error with a short debug sample (trimmed)
if (empty($desc)) {
    http_response_code(502);
    echo json_encode(['success' => false, 'message' => 'Could not parse LLM response', 'debug' => substr($response, 0, 1000)]);
    exit;
}

// Return success
echo json_encode(['success' => true, 'description' => $desc, 'source' => 'llm', 'http_status' => $httpStatus]);
exit;
?>