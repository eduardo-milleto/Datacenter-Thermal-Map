<?php
// Recebe os dados enviados
$data = json_decode(file_get_contents('php://input'), true);

$cellId = $data['cellId'];
$note = $data['note'];

// Caminho do arquivo onde a nota será salva (pode ser um banco de dados no lugar de arquivo)
$file = "notes/$cellId.txt";

// Salva a nota no arquivo correspondente ao quadrado
if (file_put_contents($file, $note)) {
    echo json_encode(['success' => true, 'message' => 'Nota salva com sucesso']);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao salvar a nota']);
}
?>

