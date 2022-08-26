<?php
// phpinfo();
require_once("./php-openttd-admin/OttdAdmin.php");
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

function get_server_memory_usage(){
    $free = shell_exec('free');
    $free = (string)trim($free);
    $free_arr = explode("\n", $free);
    $mem = explode(" ", $free_arr[1]);
    $mem = array_filter($mem);
    $mem = array_merge($mem);
    return $mem[2]/$mem[1]*100;
}

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
            <?php try {
                print_r(
                    round(
                        sys_getloadavg()[0] * 100, 2
                    )
                );
            } catch(Error $e) {
                print_r("Unknown");
            } ?> %
        </div>
        <div class="main_flex_item">
            <span class="main_flex_title">Memory Usage</span>
            <?php try {
                print_r(get_server_memory_usage());
            } catch(Error $e) {
                print_r("Unknown");
            } ?> %
        </div>
    </div>
</div>
<div class="container">

</div>
</body>
</html>