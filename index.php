<?php
    require_once ("php-openttd-admin/OttdAdmin.php");
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
?>
<html>
    <head>

    </head>
    <body>
        <?php print_r(
                $client->getServerInfo()
        ) ?>
    </body>
</html>
