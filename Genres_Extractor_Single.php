<?php

/*
MIT License
Copyright (c) 2025 Albdroid.AL
*/

/*
How It Works:
Extract_Genres.php?url=https://ovoo.spagreen.net/demo/v33/genre/crime.html
*/

// Function to fetch the HTML content of the URL
function get_data($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}

// Function to extract data using regex
function extractData($html) {
    // Regex pattern to extract iframe src, background-image, <a> href, and movie-title
    $pattern = '/<iframe[^>]*src=["\'](.*?)["\'][^>]*>.*?background-image:\s*url\(\'(.*?)\'\).*?<div class=["\']movie-img["\'][^>]*>.*?<a[^>]*href=["\'](.*?)["\'][^>]*>.*?<div class=["\']movie-title["\'][^>]*>.*?<h3>\s*<a[^>]*?>(.*?)<\/a>/si';

    preg_match_all($pattern, $html, $matches, PREG_SET_ORDER);

    $data = [];
	$strm_ids = 1;
    foreach ($matches as $match) {
        $data[] = [
		"id" =>   $strm_ids++,
		"title" => trim($match[4]), // Movie title text
        "iframe_url" => trim($match[1]), // iframe src value
        "thumbnail_url" => trim($match[2]), // Background image URL
        "watch_url" => trim($match[3]), // <a> href value (movie-img link)
            
        ];
    }
    return $data;
}

// Call the function to extract data
$url = isset($_GET["url"]) && !empty($_GET["url"]) ? $_GET["url"] : "https://ovoo.spagreen.net/demo/v33/genre/musical.html";
$html = get_data($url);
$data = extractData($html);

// Output extracted data as JSON
header('Content-Type: application/json');
//echo json_encode($data, JSON_PRETTY_PRINT);
$json_data = str_replace('\\/', '/', json_encode($data,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
echo $json_data;
?>