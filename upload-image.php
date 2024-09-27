<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verifica se o arquivo foi enviado sem erros
    if (isset($_FILES['uploadedImage']) && $_FILES['uploadedImage']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = './images/';
        $cellId = basename($_POST['cellId']); // Nome da célula (AB00, etc.)
        $fileTmpPath = $_FILES['uploadedImage']['tmp_name'];
        $fileExtension = pathinfo($_FILES['uploadedImage']['name'], PATHINFO_EXTENSION);
        
        // Força o nome do arquivo para ser 'cellId.jpg'
        $fileName = $cellId . '.jpg'; 

        // Caminho do destino final
        $destPath = $uploadDir . $fileName;

        // Mover o arquivo para o local correto e forçar a conversão para JPG
        if (move_uploaded_file($fileTmpPath, $destPath)) {
            echo json_encode(['success' => true, 'message' => 'Imagem carregada com sucesso.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao mover o arquivo de imagem.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao fazer upload da imagem.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método de requisição inválido.']);
}
?>

