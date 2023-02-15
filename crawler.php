<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<?php

$header_pattern = '/<title.*?>(.*?)<\/title>(?!.*Synonyms:)/';
$url_pattern = '/<a.*?href="(https:\/\/.*?)".*?>/';
$content_pattern = '/<p[^>]*>(.*?)<\/p>/';

include("simple_html_dom.php");

$start_url = "https://www.dictionary.com/browse/cat";

file_put_contents("visited_urls.txt", "");
$visited_urls = file("visited_urls.txt", FILE_IGNORE_NEW_LINES);
$to_visit_urls = array($start_url);

file_put_contents("database.txt", "", FILE_APPEND);

while (!empty($to_visit_urls)) {
  $random_index = array_rand($to_visit_urls);
  $current_url = $to_visit_urls[$random_index];
  unset($to_visit_urls[$random_index]);

  if (!in_array($current_url, $visited_urls)) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $current_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $html = curl_exec($ch);
    curl_close($ch);

    preg_match($header_pattern, $html, $header);
    preg_match($url_pattern, $html, $url);
    preg_match_all($content_pattern, $html, $content);

    $title = html_entity_decode($header[1] ?? '');
    $url = $url[1] ?? '';
    $content = implode(" ", $content[1]) ?? '';

    $content = preg_replace("/<.*?>|\.css-\w{5,}.*?{.*?}|\@media.*?\{.*?\}/", "", $content);
    $content = substr($content, 0, 500) . "...";

    if ($title && $content) {
      $new_url = "https://www." . substr($current_url, 12);
      $data = "<b>" . $title . "</b><br><a href='" . $new_url . "'><i>" . $new_url . "</i></a><br>" . $content . "\n";
    
      $file_contents = file_get_contents("database.txt");
      if (strpos($file_contents, $data) === false) {
        file_put_contents("database.txt", $data, FILE_APPEND);  
      }
    }    
   
// autosuggest_list.txt
$file = "database.txt";
$lines = file($file, FILE_IGNORE_NEW_LINES);
$titles = array();

foreach ($lines as $line) {
    preg_match('/<b>(.*?)<\/b>/', $line, $match);
    $title = $match[1];
    
    // Remove any quotes in the title
    $title = str_replace('"', '', $title);

    // Remove any text after the ":" symbol
    $title = preg_replace("/\:.*/", "", $title);
    
    $titles[] = "\"" . $title . "\",";
}

file_put_contents("autosuggest_list.txt", implode("\n", $titles));


    preg_match_all($url_pattern, $html, $new_urls);
    $new_urls = array_unique($new_urls[1]);

    foreach ($new_urls as $new_url) {
      if (!in_array($new_url, $visited_urls) && !in_array($new_url, $to_visit_urls) && substr($new_url, 0, 12) ==='https://www.') {
           $to_visit_urls[] = $new_url;
}
}
$visited_urls[] = $current_url;
file_put_contents("visited_urls.txt", implode("\n", $visited_urls), FILE_APPEND);
    }
  }

  ?>


</body>
</html>