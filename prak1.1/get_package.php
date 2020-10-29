<?php 
set_time_limit(5000);
function load($url) {
    $context = stream_context_create(
        array(
            "http" => array(
                "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36"
            )
        )
    );
    $data = file_get_contents($url, false, $context);
    return $data;
}

function get_package_download_url($package_name) {
    $data = load("https://packagist.org/packages/". $package_name .".json");
    $data = json_decode($data, true);

    foreach($data["package"]["versions"] as $key => $value) {
        if(strpos($key, "dev") === FALSE) {
            $version = $key;
            break;
        }
    }

    return $data["package"]["versions"][$version]["dist"]["url"];
}

function get_deps($package_name) {
    $url = get_package_download_url($package_name);
    $zip_content = load($url); 
    $fp = tmpfile();

    fwrite($fp, $zip_content);

    $stream = stream_get_meta_data($fp);
    $filename = $stream['uri'];
    $zip = new ZipArchive;
    $zip->open($filename);

    $stat = $zip->statIndex(0)["name"] . "composer.json";
    $metadata_package = $zip->getFromName($stat);

    $metadata = json_decode($metadata_package, true);
    $deps = array();

    foreach($metadata["require"] as $key => $value) {
        if (strpos($key, "/") === FALSE) continue;
        array_push($deps, $key);
    }
    return $deps;
}

$all_deps = array();
function rec($package_name) {
    $deps = get_deps($package_name);

    if (count($deps) == 0) {
        $GLOBALS["all_deps"][$package_name][] = array();
    }
    else {
        foreach($deps as $key => $value) {
            print_r($value . "<br>");
            $GLOBALS["all_deps"][$package_name][] = $value;
            if (!array_key_exists($value, $GLOBALS["all_deps"])) {
                rec($value);
            }
        }
    }
}

function get_composer_graph($package_name) {
    rec($package_name);
    $graph = "digraph G {" . "<br>";
    
    foreach($GLOBALS["all_deps"] as $key => $value) {
        for ($i = 0; $i < count($value); ++$i) {
            if (gettype($value[$i]) == "string") {
                $graph = $graph . '"' . $key . '" -> "' . $value[$i] . '"; <br>';
            }
        }
    }

    $graph = $graph . "}";
    return $graph;
}


$package_name = "laravel/framework";
$graphs = get_composer_graph($package_name);

print($graphs);

?>