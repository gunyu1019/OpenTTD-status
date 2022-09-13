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
$companyInfo = $client->getCompanyInfo();
$companyEconomyInfo = $client->getCompanyEconomy();
$companyStats = $client->getCompanyStats();

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

$companyColorList = array(
    "#1b448c", "#4c7458", "#bc546d", "#d59c20",
    "#c40000", "#357084", "#548413", "#50683c",
    "#1878dc", "#b87050", "#505074", "#505074",
    "#fd9b01", "#7c6848", "#737573", "#b8b8b8"
)
?>
<html>
<head>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/afc467762e.js" crossorigin="anonymous"></script>
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
<section class="full_screen" id="company_stats">
    <div class="container">
        <table class="company_info">
            <tr class="table_title">
                <th>국가명</th>
                <th class="company_owner">소유주</th>
                <th>자금</th>
            </tr>
            <?php foreach ($companyInfo as $index => $info): ?>
                <tr>
                    <td>
                        <i class="fas fa-circle" style="color: <?= $companyColorList[$info['COLOR']] ?>"></i>
                        <?= $info['COMPANY_NAME'] ?>
                    </td>
                    <td class="company_owner"><?= $info['MANAGER'] ?></td>
                    <td>
                        £ <?= number_format($companyEconomyInfo[$index]['MONEY']) ?>
                        (£ <?= number_format($companyEconomyInfo[$index]['INCOME']) ?>)
                    </td>
                </tr>
                <tr>
                    <td colspan="3">
                        <div>34123</div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</section>
<?php if (isset($_GET['debugger'])): ?>
    <section class="full_screen" id="debugger">
        <div class="container">
            <?= print_r($companyInfo) ?>
            <span style="padding: 30px; display: block"></span>
            <?= print_r($companyEconomyInfo) ?>
            <span style="padding: 30px; display: block;"></span>
            <?= print_r($companyStats) ?>
        </div>
    </section>
<?php endif ?>
</body>
</html>