<?php
header('Content-Type: application/json');

// Configuração da conexão com o banco de dados
$host = 'localhost';
$dbname = 'datacenter';
$username = 'root';
$password = 'teste'; // Senha correta

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Obtenção dos dados do POST
    $data = json_decode(file_get_contents('php://input'), true);
    $cellId = $data['cellId'];
    $isClickable = $data['clickable'] === 'availableClickable';  // Verificação do status correto

    // Definir o grupo de clicabilidade com base no valor do POST
    $clickableGroup = $isClickable ? 'availableClickable' : 'nonAvailableClickable';

    // Verifica se a célula já existe no banco de dados
    $stmt = $pdo->prepare("SELECT clickable FROM grid_items WHERE cell_id = ?");
    $stmt->execute([$cellId]);
    $celulaExistente = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($celulaExistente) {
        // Verifica se o estado é diferente para atualizar
        if ($celulaExistente['clickable'] !== $clickableGroup) {
            // Atualiza o estado da célula
            $stmt = $pdo->prepare("UPDATE grid_items SET clickable = ? WHERE cell_id = ?");
            $stmt->execute([$clickableGroup, $cellId]);
            echo json_encode(['success' => true, 'message' => "Região atualizada para $clickableGroup com sucesso!"]);
        } else {
            echo json_encode(['success' => false, 'message' => 'A região já está com o status desejado.']);
        }
    } else {
        // Se a célula não existe, insere nova célula no banco de dados
        $stmt = $pdo->prepare("INSERT INTO grid_items (cell_id, status, clickable) VALUES (?, 'none', ?)");
        $stmt->execute([$cellId, $clickableGroup]);

        echo json_encode(['success' => true, 'message' => 'Região clicável criada e status atualizado com sucesso!']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Falha ao atualizar a região: ' . $e->getMessage()]);
}

