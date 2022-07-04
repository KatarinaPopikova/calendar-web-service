<?php
require_once "/home/xstasova/public_html/strankaZ6/controllers/Controller.php";
header('Content-Type: application/json');
$controller = new Controller();
$input = json_decode(file_get_contents("php://input"));
echo json_encode($controller->addNameDate($input->name, $input->date));