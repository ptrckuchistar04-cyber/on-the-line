<?php
session_start();

header('Content-Type: application/json');

$count = isset($_SESSION['compare_list']) ? count($_SESSION['compare_list']) : 0;

echo json_encode(['count' => $count]);