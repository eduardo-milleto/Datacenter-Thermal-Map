<?php
$cellId = $_GET['cellId'];
$file = "notes/$cellId.txt";

if (file_exists($file)) {
    $note = file_get_contents($file);
    echo json_encode(['note' => $note]);
} else {
    echo json_encode(['note' => '']);
}
?>

