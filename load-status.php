<?php
// Configuração da conexão com o banco de dados
$host = 'localhost';
$dbname = 'datacenter';
$username = 'root';
$password = 'teste';  // Senha atualizada

try {
    // Conectando ao banco de dados
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Inicializando arrays para armazenar os dados de tomadas e áreas clicáveis
    $availableTomadas = [];
    $nonAvailableTomadas = [];
    $availableClickable = [];
    $nonAvailableClickable = [];

    // Query para selecionar todas as tomadas e seus status da tabela "tomadas"
    $stmt = $pdo->query("SELECT tomada_id, status FROM tomadas");

    // Processar o resultado da query da tabela "tomadas"
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if ($row['status'] === 'disponivel') {
            $availableTomadas[] = $row['tomada_id'];  // Tomadas disponíveis
        } else {
            $nonAvailableTomadas[] = $row['tomada_id'];  // Tomadas não disponíveis
        }
    }

    // Query para selecionar as áreas clicáveis da tabela "grid_items"
    $stmt2 = $pdo->query("SELECT cell_id, clickable FROM grid_items");

    // Processar o resultado da query da tabela "grid_items"
    while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {
        if ($row['clickable'] === 'availableClickable') {
            $availableClickable[] = $row['cell_id'];  // Áreas clicáveis disponíveis
        } elseif ($row['clickable'] === 'nonAvailableClickable') {
            $nonAvailableClickable[] = $row['cell_id'];  // Áreas não clicáveis
        }
    }

    // Retornando os arrays em formato JSON
    echo json_encode([
        'availableTomadas' => $availableTomadas,
        'nonAvailableTomadas' => $nonAvailableTomadas,
        'clickableGroups' => [
            'availableClickable' => $availableClickable,
            'nonAvailableClickable' => $nonAvailableClickable
        ]
    ]);

} catch (PDOException $e) {
    // Em caso de erro de conexão ou query
    echo json_encode([
        'success' => false,
        'message' => 'Erro de banco de dados: ' . $e->getMessage()
    ]);
}
?>

