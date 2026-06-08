<?php

declare(strict_types=1);

session_start();

date_default_timezone_set('America/Sao_Paulo');

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/Config.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Security.php';
require_once __DIR__ . '/View.php';
require_once __DIR__ . '/Repositories/PesquisaRepository.php';
require_once __DIR__ . '/Controllers/PublicController.php';
