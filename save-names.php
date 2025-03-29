<?php
function log_message($message) {
    $timestamp = date("[Y-m-d H:i:s]");
    file_put_contents("log.txt", "$timestamp $message\n", FILE_APPEND);
}

log_message("Incoming request");

$data = file_get_contents("php://input");

if ($data) {

    $input = json_decode($data, true);
    $name = trim($input["name"] ?? "");
    $name = preg_replace('/\s+/', ' ', $name); // collapse multiple spaces

    if ($name) {
        $logFile = 'downloads.json'; // use a proper data file, not your log
        $usersFile = 'names.json';
        $downloads = [];
        $users = [];

        // Load users (for assigning/reusing ID)
        if (file_exists($usersFile)) {
            $json = file_get_contents($usersFile);
            $users = json_decode($json, true) ?? [];
        }

        // Load all downloads
        if (file_exists($logFile)) {
            $json = file_get_contents($logFile);
            $downloads = json_decode($json, true) ?? [];
        }

        // Check if user already exists
        $userId = null;
        foreach ($users as $user) {
            $entryName = isset($user['name']) ? trim(preg_replace('/\s+/', ' ', $user['name'])) : '';
            if (mb_strtolower($entryName, 'UTF-8') === mb_strtolower($name, 'UTF-8')) {
                $userId = $user['id'];
                break;
            }
        }

        // New user
        if (!$userId) {
            $userId = count($users) + 1;
            $users[] = ["id" => $userId, "name" => $name];
            log_message("New user: $name (ID: $userId)");
        }

        // Save updated users list (in case a new user was added)
        file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        // Log this download
        $downloads[] = [
            "id" => $userId,
            "name" => $name,
            "timestamp" => date('Y-m-d H:i:s')
        ];
        file_put_contents($logFile, json_encode($downloads, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        log_message("Download logged for: $name (ID: $userId)");

        echo json_encode(["status" => "success"]);
    } else {
        log_message("Name missing in request");
        echo json_encode(["status" => "no name"]);
    }
} else {
    log_message("No data received");
    echo json_encode(["status" => "no data"]);
}
?>
