<?php
require_once("php-openttd-admin/OttdAdmin.php");
session_start();

const CONFIG_DIR = "./config.ini";

$configuration = parse_ini_file(CONFIG_DIR, true, INI_SCANNER_RAW);
if (!$configuration) {
    exit();
}

$client = new OttdAdmin(
    $configuration['OpenTTD']['hostname'] ?? '127.0.0.1',
    (int)$configuration['OpenTTD']['port'] ?? 3977,
    $configuration['OpenTTD']['password'] ?? null
);
$is_connect = $client->connect();
$_join = $client->join();

$serverInfo = $client->getServerInfo();
$clientInfo = $client->getClientInfo();
?>
<html>
<head>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="static/css/index.css">
</head>
<body>
<div class="main_group">
    <h1 class="title"><?php print_r(
            $serverInfo['SERVER_NAME']
        ) ?></h1>
    <div class="main_flex_group container">
        <div class="main_flex_item">
            <span class="main_flex_title">Version</span>
            <?php print_r($serverInfo['SERVER_VERSION']); ?>
        </div>
        <div class="main_flex_item">
            <span class="main_flex_title">Member</span>
            <?php print_r(sizeof($clientInfo) - 1); ?> 명 /
            15 명
        </div>
        <div class="main_flex_item">
            <span class="main_flex_title">CPU Usage</span>
            <?php print_r(
                    round(
                            sys_getloadavg()[0] * 100, 2
                    )
            )?> %
        </div>
        <div class="main_flex_item">
            <span class="main_flex_title">Memory Usage</span>
            <?php print_r(memory_get_usage())?>
        </div>
        </div>
    </div>
</div>
<div class="container">

</div>
</body>
</html>