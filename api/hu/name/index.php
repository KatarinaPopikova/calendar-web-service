<?php
require_once "/home/xstasova/public_html/strankaZ6/controllers/Controller.php";
header('Content-Type: application/json');
$controller = new Controller();
echo json_encode($controller->getDateFromCountry($_GET["name"], "HU"));