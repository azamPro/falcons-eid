<?php
file_put_contents("log.txt", "Incoming request\n", FILE_APPEND);

$data = file_get_contents("php://input");

if ($data) {
    file_put_contents("log.txt", "Raw data: $data\n", FILE_APPEND);

    $input = json_decode($data, true);
    $name = trim($input["name"] ?? "");
    $name = preg_replace('/\s+/', ' ', $name); // collapse multiple spaces

    if ($name) {
        $namesFile = 'names.json';
        $namesData = [];

        if (file_exists($namesFile)) {
            $json = file_get_contents($namesFile);
            $namesData = json_decode($json, true) ?? [];
        }

        $found = false;
        foreach ($namesData as &$entry) {
          $entryName = isset($entry['name']) ? trim(preg_replace('/\s+/', ' ', $entry['name'])) : '';
      
          if (mb_strtolower($entryName, 'UTF-8') === mb_strtolower($name, 'UTF-8')) {
              $entry['count'] += 1;
              $found = true;
              file_put_contents("log.txt", "Updated count for: $name\n", FILE_APPEND);
              break;
          }
      }
      unset($entry); // VERY IMPORTANT: Break reference after loop
      

        if (!$found) {
            $namesData[] = ["name" => $name, "count" => 1];
            file_put_contents("log.txt", "Added new name: $name\n", FILE_APPEND);
        }

        file_put_contents($namesFile, json_encode($namesData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        file_put_contents("log.txt", "Saved: " . json_encode($namesData) . "\n", FILE_APPEND);
        echo json_encode(["status" => "success"]);
    } else {
        file_put_contents("log.txt", "Name missing in request\n", FILE_APPEND);
        echo json_encode(["status" => "no name"]);
    }
} else {
    file_put_contents("log.txt", "No data received\n", FILE_APPEND);
    echo json_encode(["status" => "no data"]);
}
?>
