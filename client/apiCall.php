<?php
// Initialize variables with defaults to prevent undefined variable warnings
$content = null;
$anime_name = null;
$character_name = null;

$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, "https://api.animechan.io/v1/quotes/random");
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($curl);

if (curl_error($curl)) {
    // Leave variables as null; index.php will fall back to session data
} else {
    $data = json_decode($response, true);

    if (isset($data["data"])) {
        $content = $data["data"]["content"];
        $anime_name = $data["data"]["anime"]["name"];
        $character_name = $data["data"]["character"]["name"];
    }
    // If $data["data"] is not set, variables remain null and session fallback applies
}

curl_close($curl);
?>
