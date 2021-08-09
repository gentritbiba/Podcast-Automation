<?php
$string = file_get_contents("./data.json");
$json_a = json_decode($string, true);

$keys = array_keys($json_a);

foreach ($keys as $key) {
    $arr_size = count($json_a[$key]);
    for ($i = 0; $i < $arr_size; $i++) {
        if(!strpos($json_a[$key][$arr_size -1 - $i], ".mp3")){
            continue;
            var_dump($json_a[$key][$i]);
        }
        // Check if the post exists
        if ($json_a[$key][$arr_size - 1 - $i] || !$json_a[$key][0]) {
            break;
        }
        else{
            // Create the post type
        }

    }
}

// var_dump($keys);
