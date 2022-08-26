<?php
require_once("./php-openttd-admin/OttdAdmin.php");
require_once("./system.php");
session_start();

const CONFIG_DIR = "./config.ini";

$configuration = parse_ini_file(CONFIG_DIR, true, INI_SCANNER_RAW);
if (!$configuration) {
    print_r("Could not find config.ini");
    if (file_exists('./config.example.ini'))
        copy('./config.example.ini', './config.ini');
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

$memberList = array();
foreach (array_slice($clientInfo, 1) as $member) {
    $memberComment = "${member['CLIENT_NAME']} (#${member['CLIENT_ID']})";
    $memberList[] = $memberComment;
}


$systemClient = new SystemInformation(PHP_OS);

try {
    $cpu_usage = round($systemClient->cpu_usage(), 2);
} catch (Error $e) {
    $cpu_usage = "Unknown";
}

try {
    $total_memory = $systemClient->total_memory_usage();
    $available_memory = $systemClient->available_memory_usage();
    $memory_percent = round($available_memory / $total_memory * 100, 2);
    $total_memory_s = round($total_memory / (1024 ** 2), 2);
    $available_memory_s = round($available_memory / (1024 ** 2), 2);
} catch (Error $e) {
    $total_memory_s = $available_memory_s = $memory_percent = 0;
}
?>
<html>
<head>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="static/css/index.css">
</head>
<body>
<section class="full_screen" id="main">
    <div class="main_group">
        <h1 class="title"><?=
            $serverInfo['SERVER_NAME']
            ?></h1>
        <div class="main_flex_group container">
            <div class="main_flex_item">
                <span class="main_flex_title">Version</span>
                <?= $serverInfo['SERVER_VERSION'] ?>
            </div>
            <div class="main_flex_item tooltip-text-out">
                <span class="main_flex_title">Member</span>
                <?= sizeof($clientInfo) - 1 ?> 명 /
                15 명
                <span class="tooltip-text">멤버 목록: <br/><?= implode("<br/>", $memberList) ?></span>
            </div>
            <div class="main_flex_item">
                <span class="main_flex_title">CPU Usage</span>
                <?= $cpu_usage ?> %
            </div>
            <div class="main_flex_item">
                <span class="main_flex_title">Memory Usage</span>
                <?= $available_memory_s ?> GB / <?= $total_memory_s ?> GB (<?= $memory_percent ?> %)
            </div>
        </div>
    </div>
</section>
<!-- <section class="full_screen" id="company_stats">
    <div class="container">

    </div>
</section> -->
</body>
</html>