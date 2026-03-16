<?php
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, "https://api.animechan.io/v1/quotes/random");

curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($curl);
if (curl_error($curl)) {
    echo "Wait.... for 15 min ";
} else {
    $data = json_decode($response, true);

    $content = $data["data"]["content"];
    $anime_name = $data["data"]["anime"]["name"];

    if (isset($data["data"])) {
        $content = $data["data"]["content"];
        $anime_name = $data["data"]["anime"]["name"];
        $character_name = $data["data"]["character"]["name"];
    } else {
        echo "You are requesting too many times.... ";
    }
}
curl_close($curl);

// echo "Quote: " . $content . "\n";
// echo "Anime: " . $anime_name . "\n";
// echo "Character: " . $character_name . "\n";

?>
