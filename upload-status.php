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
    $isTomada = $data['tomada'];
    $isDisponivel = $data['disponivel'];

    // Verificação se o quadrado é uma tomada ou não
    if ($isTomada) {
        // Se for uma tomada, defina o status com base em disponível ou não disponível
        $status = $isDisponivel ? 'disponivel' : 'nao_disponivel';

        // Verifica se a tomada já existe no banco de dados
        $stmt = $pdo->prepare("SELECT * FROM tomadas WHERE tomada_id = ?");
        $stmt->execute([$cellId]);
        $tomadaExistente = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($tomadaExistente) {
            // Atualiza o status da tomada existente
            $stmt = $pdo->prepare("UPDATE tomadas SET status = ?, tomada = 1 WHERE tomada_id = ?");
            $stmt->execute([$status, $cellId]);
            echo json_encode(['success' => true, 'message' => 'Status atualizado com sucesso!']);
        } else {
            // Insere nova tomada no banco de dados
            $stmt = $pdo->prepare("INSERT INTO tomadas (tomada_id, status, tomada) VALUES (?, ?, ?)");
            $stmt->execute([$cellId, $status, 1]);
            echo json_encode(['success' => true, 'message' => 'Tomada criada e status atualizado com sucesso!']);
        }
    } else {
        // Se "Tomada" estiver desmarcada, remova o quadrado do grupo de tomadas
        $stmt = $pdo->prepare("DELETE FROM tomadas WHERE tomada_id = ?");
        $stmt->execute([$cellId]);
        echo json_encode(['success' => true, 'message' => 'Tomada removida com sucesso!']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Falha ao atualizar o status: ' . $e->getMessage()]);
}

