<?php

/*
MIT License
Copyright (c) 2025 Albdroid.AL
*/

/*
How to Use:
Save the File: Save the script as Genres_Extractor_Multi_Format.php.

Run the Script: Place the script on your PHP-enabled server and access it via a browser or API client.

Specify the Output Format: Append the format query parameter to the URL to specify the output format:

?format=json: Outputs the data in JSON format.
?format=xml: Outputs the data in XML format.
?format=m3u: Outputs the data in M3U playlist format.
?format=raw: Outputs the data in plain Raw Text format.
Example Requests:

JSON: http://yourserver/Genres_Extractor_Multi_Format.php?format=json
XML: http://yourserver/Genres_Extractor_Multi_Format.php?format=xml
M3U: http://yourserver/Genres_Extractor_Multi_Format.php?format=m3u
Raw: http://yourserver/Genres_Extractor_Multi_Format.php?format=raw
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
		"title" => $match[4], // Movie title text
        "iframe_url" => $match[1], // iframe src value
        "background_image" => $match[2], // Background image URL
        "movie_img_link" => $match[3], // <a> href value (movie-img link)
        ];
    }
    return $data;
}

// Function to output data in JSON format
function outputAsJson($data) {
    header('Content-Type: application/json');
    //echo json_encode($data, JSON_PRETTY_PRINT);
	$json_data = str_replace('\\/', '/', json_encode($data,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    echo $json_data;
}

// Function to output data in XML format
function outputAsXml($data) {
    header('Content-Type: application/xml');
    $xml = new SimpleXMLElement('<item/>');

    foreach ($data as $movie) {
        $movieNode = $xml->addChild('movie');
        $movieNode->addChild('iframe_url', htmlspecialchars($movie['iframe_url'])) . PHP_EOL;
        $movieNode->addChild('background_image', htmlspecialchars($movie['background_image'])) . PHP_EOL;
        $movieNode->addChild('movie_img_link', htmlspecialchars($movie['movie_img_link'])) . PHP_EOL;
        $movieNode->addChild('title', htmlspecialchars($movie['title'])) . PHP_EOL;
    }
//header('Content-Type: application/xml');
    echo $xml->asXML();
}

// Function to output data in M3U format
function outputAsM3U($data) {
    header('Content-Type: text/plain');
    echo "#EXTM3U\n";
    foreach ($data as $movie) {
        echo "#EXTINF:-1,{$movie['title']}\n";
        echo "{$movie['movie_img_link']}\n";
    }
}

// Function to output data in Raw format
function outputAsRaw($data) {
    header('Content-Type: text/plain');
    foreach ($data as $movie) {
        echo "Title: {$movie['title']}\n";
        echo "Iframe Src: {$movie['iframe_url']}\n";
        echo "Background Image: {$movie['background_image']}\n";
        echo "Movie Link: {$movie['movie_img_link']}\n\n";
    }
}

// Call the function to extract data
$url = isset($_GET["url"]) && !empty($_GET["url"]) ? $_GET["url"] : "https://ovoo.spagreen.net/demo/v33/genre/adventure.html";
$html = get_data($url);
// Extract data from the HTML
$data = extractData($html);

// Get the desired format from the query parameter (default to JSON)
$format = isset($_GET['format']) ? strtolower($_GET['format']) : 'json';

// Output data in the requested format
switch ($format) {
    case 'json':
        outputAsJson($data);
        break;
    case 'xml':
        outputAsXml($data);
        break;
    case 'm3u':
        outputAsM3U($data);
        break;
    case 'raw':
        outputAsRaw($data);
        break;
    default:
        echo "Invalid format specified. Use 'json', 'xml', 'm3u', or 'raw'.";
}
?>