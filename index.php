<?php
// Verifica se o arquivo CSV existe, caso contrário, cria um novo
$csvFile = 'tarefas.csv';
if (!file_exists($csvFile)) {
    file_put_contents($csvFile, "Tarefa,Subtarefas\n"); // Cabeçalho do CSV
}

// Função para adicionar uma tarefa ao CSV
function adicionarTarefa($tarefa, $subtarefas) {
    global $csvFile;
    $subtarefasStr = implode(';', $subtarefas); // Junta as subtarefas em uma string separada por ;
    $linha = "\"{$tarefa}\",\"{$subtarefasStr}\"\n"; // Formata a linha para o CSV
    file_put_contents($csvFile, $linha, FILE_APPEND); // Adiciona a linha ao arquivo
}

// Função para carregar as tarefas do CSV
function carregarTarefas() {
    global $csvFile;
    $tarefas = [];
    if (($handle = fopen($csvFile, 'r')) !== FALSE) {
        fgetcsv($handle); // Ignora o cabeçalho
        while (($data = fgetcsv($handle)) !== FALSE) {
            $tarefas[] = [
                'tarefa' => $data[0],
                'subtarefas' => explode(';', $data[1]) // Divide as subtarefas
            ];
        }
        fclose($handle);
    }
    return $tarefas;
}

// Processa o formulário quando enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['tarefa']) && !empty($_POST['tarefa'])) {
        $tarefa = $_POST['tarefa'];
        $subtarefas = isset($_POST['subtarefa']) ? $_POST['subtarefa'] : [];
        adicionarTarefa($tarefa, $subtarefas);
    }
}

// Carrega as tarefas existentes
$tarefas = carregarTarefas();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Focus Flow</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .subtask-container {
            margin-top: 10px;
        }
        .subtask {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
        }
        .subtask input {
            margin-right: 10px;
        }
        .remove-subtask {
            cursor: pointer;
            color: red;
            margin-left: 10px;
        }
        .create-task-button {
            margin-top: 10px;
            padding: 5px 10px;
            background-color: green;
            color: white;
            border: none;
            cursor: pointer;
        }
        .create-task-button:hover {
            background-color: darkgreen;
        }
        .task-card {
            border: 1px solid #ccc;
            padding: 10px;
            margin-top: 10px;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .task-card h3 {
            margin: 0;
            display: flex;
            align-items: center;
        }
        .task-card h3 input {
            margin-right: 10px;
        }
        .task-card ul {
            list-style-type: none;
            padding: 0;
        }
        .task-card ul li {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
        }
        .task-card ul li input {
            margin-right: 10px;
        }
        .delete-task {
            background-color: red;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            margin-top: 10px;
        }
        .delete-task:hover {
            background-color: darkred;
        }
    </style>
</head>
<body>
    <h1>Focus Flow</h1>
    <form method="POST" action="">
        <label for="tarefa">Tarefa</label>
        <input type="text" name="tarefa" id="tarefa" required>
        <button type="button" id="add-subtask">Adicionar Subtarefa</button>

        <div id="subtasks-container" class="subtask-container"></div>

        <button type="submit" id="create-task" class="create-task-button">Criar Tarefa</button>
    </form>

    <div id="tasks-list">
        <?php foreach ($tarefas as $tarefa): ?>
            <div class="task-card">
                <h3>
                    <input type="checkbox">
                    <?php echo htmlspecialchars($tarefa['tarefa']); ?>
                </h3>
                <?php if (!empty($tarefa['subtarefas'])): ?>
                    <ul>
                        <?php foreach ($tarefa['subtarefas'] as $subtarefa): ?>
                            <li>
                                <input type="checkbox">
                                <?php echo htmlspecialchars($subtarefa); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                <button class="delete-task" onclick="excluirTarefa(this)">Excluir Tarefa</button>
            </div>
        <?php endforeach; ?>
    </div>

    <script>
        // Adiciona subtarefas dinamicamente
        document.getElementById('add-subtask').addEventListener('click', function() {
            const subtasksContainer = document.getElementById('subtasks-container');

            const subtaskDiv = document.createElement('div');
            subtaskDiv.classList.add('subtask');

            const subtaskInput = document.createElement('input');
            subtaskInput.type = 'text';
            subtaskInput.name = 'subtarefa[]';
            subtaskInput.placeholder = 'Subtarefa';

            const removeButton = document.createElement('span');
            removeButton.classList.add('remove-subtask');
            removeButton.innerHTML = 'X';
            removeButton.addEventListener('click', function() {
                subtasksContainer.removeChild(subtaskDiv);
            });

            subtaskDiv.appendChild(subtaskInput);
            subtaskDiv.appendChild(removeButton);

            subtasksContainer.appendChild(subtaskDiv);
        });

        // Função para excluir uma tarefa (simulação)
        function excluirTarefa(botao) {
            const card = botao.closest('.task-card');
            card.remove();
            // Aqui você pode adicionar uma requisição AJAX para remover a tarefa do CSV
        }
    </script>
</body>
</html>