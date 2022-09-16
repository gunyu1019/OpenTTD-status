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
);

function default_currency($index, $value)
{
    global $configuration;
    $t = explode(",", $configuration['CompanyInfo']['company_currency'] . trim(" "));
    $currency_key = $t[$index];
    return currency($index, $value, $currency_key);
}

function currency($index, $value, $currency_key)
{
    if ($currency_key == 0) return sprintf("£ %s", number_format($value));
    else if ($currency_key == 1) return sprintf("₩ %s", number_format($value * 1850));
    else if ($currency_key == 2) return sprintf("₽ %s", number_format($value * 50));
}

?>

<html lang="kr">
<head>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/afc467762e.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="static/css/index.css">
    <title><?= $serverInfo['SERVER_NAME'] ?></title>
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
                <th class="currency_button">자금 (기본)</th>
            </tr>
            <?php foreach ($companyInfo as $index => $info): ?>
                <tr class="company_info_title">
                    <td>
                        <i class="fas fa-circle" style="color: <?= $companyColorList[$info['COLOR']] ?>"></i>
                        <?= $info['COMPANY_NAME'] ?>
                    </td>
                    <td class="company_owner"><?= $info['MANAGER'] ?></td>
                    <td>
                        <span id="currency_default">
                            <?= default_currency($index, $companyEconomyInfo[$index]['MONEY']) ?>
                            (<?= default_currency($index, $companyEconomyInfo[$index]['INCOME']) ?>)
                        </span>
                        <span id="currency_0">
                            <?= currency($index, $companyEconomyInfo[$index]['MONEY'], 0) ?>
                            (<?= currency($index, $companyEconomyInfo[$index]['INCOME'], 0) ?>)
                        </span>
                        <span id="currency_1">
                            <?= currency($index, $companyEconomyInfo[$index]['MONEY'], 1) ?>
                            (<?= currency($index, $companyEconomyInfo[$index]['INCOME'], 1) ?>)
                        </span>
                        <span id="currency_2">
                            <?= currency($index, $companyEconomyInfo[$index]['MONEY'], 2) ?>
                            (<?= currency($index, $companyEconomyInfo[$index]['INCOME'], 2) ?>)
                        </span>
                    </td>
                </tr>
                <tr class="company_info_detail hide">
                    <td colspan="3">
                        <div class="company_info_detail_body">
                            <div class="company_info_detail_row">
                                <div>
                                    <i class="fas fa-train company_info_detail_key"></i>
                                    <span class="company_info_detail_key_hidden">열차 수: </span>
                                    <span class="company_info_detail_value">
                                        <?= $companyStats[$index]['TRAINS_COUNT'] ?>대
                                    </span>
                                </div>
                                <div>
                                    <i class="fas fa-truck company_info_detail_key"></i>
                                    <span class="company_info_detail_key_hidden">트럭 수: </span>
                                    <span class="company_info_detail_value">
                                        <?= $companyStats[$index]['LORRIES_COUNT'] ?>대
                                    </span>
                                </div>
                                <div>
                                    <i class="fas fa-bus company_info_detail_key"></i>
                                    <span class="company_info_detail_key_hidden">버스 수: </span>
                                    <span class="company_info_detail_value">
                                        <?= $companyStats[$index]['BUSSES_COUNT'] ?>대
                                    </span>
                                </div>
                                <div>
                                    <i class="fas fa-plane company_info_detail_key"></i>
                                    <span class="company_info_detail_key_hidden">항공기 수: </span>
                                    <span class="company_info_detail_value">
                                        <?= $companyStats[$index]['PLANES_COUNT'] ?>기
                                    </span>
                                </div>
                                <div>
                                    <i class="fas fa-ship company_info_detail_key"></i>
                                    <span class="company_info_detail_key_hidden">선박 수: </span>
                                    <span class="company_info_detail_value">
                                        <?= $companyStats[$index]['SHIPS_COUNT'] ?>척
                                    </span>
                                </div>
                            </div>
                            <div class="company_info_detail_row">
                                <div>
                                    <i class="fas fa-chart-line company_info_detail_key"></i>
                                    <span class="company_info_detail_key_hidden">최근 회사 가치: </span>
                                    <span class="company_info_detail_value">
                                        <span id="currency_default">
                                            <?= default_currency($index, $companyEconomyInfo[$index]['VALUE_LASTQ']) ?>
                                        </span>
                                        <span id="currency_0">
                                            <?= currency($index, $companyEconomyInfo[$index]['VALUE_LASTQ'], 0) ?>
                                        </span>
                                        <span id="currency_1">
                                            <?= currency($index, $companyEconomyInfo[$index]['VALUE_LASTQ'], 1) ?>
                                        </span>
                                        <span id="currency_2">
                                            <?= currency($index, $companyEconomyInfo[$index]['VALUE_LASTQ'], 2) ?>
                                        </span>
                                    </span>
                                </div>
                                <div>
                                    <i class="fas fa-backward company_info_detail_key"></i>
                                    <span class="company_info_detail_key_hidden">이전 회사 가치: </span>
                                    <span class="company_info_detail_value">
                                        <span id="currency_default">
                                            <?= default_currency($index, $companyEconomyInfo[$index]['VALUE_PREVQ']) ?>
                                        </span>
                                        <span id="currency_0">
                                            <?= currency($index, $companyEconomyInfo[$index]['VALUE_PREVQ'], 0) ?>
                                        </span>
                                        <span id="currency_1">
                                            <?= currency($index, $companyEconomyInfo[$index]['VALUE_PREVQ'], 1) ?>
                                        </span>
                                        <span id="currency_2">
                                            <?= currency($index, $companyEconomyInfo[$index]['VALUE_PREVQ'], 2) ?>
                                        </span>
                                    </span>
                                </div>
                            </div>
                        </div>
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
<script>
    let $currency_mode = -1;
    $("tr.company_info_detail").find('td').hide()
    $("tr.company_info_title").find('td').on('click', function (event) {
        let $item = $(this).closest("tr").next("tr.company_info_detail");
        $item.toggleClass("hide");
        if ($item.hasClass("hide")) {
            $item.children('td').slideUp(350);
        } else {
            $item.children('td').slideDown(350);
        }
    })

    let $currency_list = [
        $("span#currency_default"), $("span#currency_0"), $("span#currency_1"), $("span#currency_2")
    ];
    $currency_list.slice(1).forEach(function ($item) {
        $item.hide()
    })

    $(".currency_button").on('click', function (event) {
        if ($currency_mode === 2) $currency_mode = -1;
        else $currency_mode += 1;

        $currency_list.forEach(function ($item) {
            $item.hide();
        })

        if ($currency_mode === 0) { $currency_list[1].show(); $(this).text("자금(파운드 GBP)"); }
        else if ($currency_mode === 1) { $currency_list[2].show(); $(this).text("자금(한화 KRW)"); }
        else if ($currency_mode === 2) { $currency_list[3].show(); $(this).text("자금(루블 RUB)"); }
        else { $currency_list[0].show(); $(this).text("자금 (기본)"); }
    })
</script>
</body>
</html>