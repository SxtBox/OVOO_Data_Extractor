<?php

/*
MIT License
Copyright (c) 2025 Albdroid.AL
*/

/*
How It Works:
Fetching HTML Content: The get_data function uses cURL to retrieve the HTML content of the specified website.
Extracting Data: The extract_html_data function uses a regex pattern to match a tags with href attributes, img tags with src attributes, and alt attributes for titles.
Output Formats:
json: Outputs the data as a JSON object.
raw: Outputs plain text with titles, links, and thumbnails.
m3u: Outputs data in M3U format.
Dynamic Output Format: The output format is determined by the format query parameter (?format=json, ?format=raw, or ?format=m3u).

Usage:
Save the script as scrape_live_tv.php.
Place the script on a PHP-enabled server.
Access it via a web browser or tool, e.g.:
http://yourserver/Live_TV_Extractor.php?format=json
http://yourserver/Live_TV_Extractor.php?format=raw
http://yourserver/Live_TV_Extractor.php?format=m3u

Live_TV_Extractor.php?url=https://ovoo.spagreen.net/demo/v33/live-tv/category/english.html&format=m3u
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
function extract_html_data($html) {
    $pattern = '/<figure[\w\W]*?<a[^>]*href=["\'](.*?)["\'][^>]*>\s*<img[^>]*src=["\'](.*?)["\'][^>]*alt=["\'](.*?)["\']/i';
    preg_match_all($pattern, $html, $matches, PREG_SET_ORDER);
    $data = [];
    $strm_ids = 1;
    foreach ($matches as $match) {
        $data[] = [
	"id" =>   $strm_ids++,
	"title" => trim($match[3]),
        "link" => trim($match[1]),
        "thumbnail" => trim($match[2]),
        ];
    }
    return $data;
}

// Function to output data in the desired format
function output_data($data, $format) {
    if ($format === 'json') {
        header('Content-Type: application/json');
	$json_data = str_replace('\\/', '/', json_encode($data,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
	echo $json_data;
    } elseif ($format === 'raw') {
        header('Content-Type: text/plain');
	$strm_ids = 1;
        foreach ($data as $item) {
	    echo "ID: " . $strm_ids++ . "\n";
            echo "Title: {$item['title']}\n";
            echo "Link: {$item['link']}\n";
            echo "Thumbnail: {$item['thumbnail']}\n\n";
        }
    } elseif ($format === 'm3u') {
        header('Content-Type: text/plain');
        echo "#EXTM3U\n";
        foreach ($data as $item) {
            echo "#EXTINF:-1,{$item['title']}\n";
            echo "{$item['link']}\n";
        }
    } else {
        echo "Invalid format specified.";
    }
}

// Call the function to extract data
$url = isset($_GET["url"]) && !empty($_GET["url"]) ? $_GET["url"] : "https://ovoo.spagreen.net/demo/v33/live-tv.html";
$html = get_data($url);
$data = extract_html_data($html);

// Specify the output format: "json", "raw", or "m3u"
$outputFormat = isset($_GET["format"]) ? strtolower($_GET["format"]) : "json";
output_data($data, $outputFormat);
?>
