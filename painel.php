<?php
// -- O BLOCO DE PROTEÇÃO DE LOGIN FOI REMOVIDO DESTA ÁREA --

// Conexão com o banco de dados
$host = "localhost"; $usuario = "u784380269_exemplo"; $senha = "EchXThGfBplO8J8@!"; $banco = "u784380269_exemplo";
$conn = new mysqli($host, $usuario, $senha, $banco);
if ($conn->connect_error) { die("Erro na conexão: " . $conn->connect_error); }
$conn->set_charset("utf8");

// Lógica de CRUD (Create, Read, Update, Delete)
$mensagem_sucesso = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Salvar Configurações
    if (isset($_POST['salvar_configuracoes'])) {
        $stmt = $conn->prepare("INSERT INTO configuracoes (config_key, config_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE config_value = ?");
        foreach ($_POST as $key => $value) {
            if ($key == 'salvar_configuracoes') continue;
            $stmt->bind_param("sss", $key, $value, $value);
            $stmt->execute();
        }
        $stmt->close();
        $mensagem_sucesso = "Configurações salvas com sucesso!";
    }
    // Adicionar/Editar Categoria
    if (isset($_POST['salvar_categoria'])) {
        $id = $_POST['id_categoria'];
        $nome = $_POST['nome_categoria'];
        $ordem = $_POST['ordem_categoria'];
        if (empty($id)) { // Inserir
            $stmt = $conn->prepare("INSERT INTO categorias (nome, ordem) VALUES (?, ?)");
            $stmt->bind_param("si", $nome, $ordem);
        } else { // Atualizar
            $stmt = $conn->prepare("UPDATE categorias SET nome = ?, ordem = ? WHERE id = ?");
            $stmt->bind_param("sii", $nome, $ordem, $id);
        }
        $stmt->execute();
        $stmt->close();
        $mensagem_sucesso = "Categoria salva com sucesso!";
    }
    // Adicionar/Editar Item
    if (isset($_POST['salvar_item'])) {
        $id = $_POST['id_item'];
        $nome = $_POST['nome_item'];
        $descricao = $_POST['descricao_item'];
        $preco = $_POST['preco_item'];
        $id_categoria = $_POST['id_categoria_item'];
        $imagem_url = $_POST['imagem_url_item'];
        $em_promocao = isset($_POST['em_promocao_item']) ? 1 : 0;
        $ativo = isset($_POST['ativo_item']) ? 1 : 0;

        if (empty($id)) { // Inserir
            $stmt = $conn->prepare("INSERT INTO itens_cardapio (nome, descricao, preco, id_categoria, imagem_url, em_promocao, ativo) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssdisii", $nome, $descricao, $preco, $id_categoria, $imagem_url, $em_promocao, $ativo);
        } else { // Atualizar
            $stmt = $conn->prepare("UPDATE itens_cardapio SET nome=?, descricao=?, preco=?, id_categoria=?, imagem_url=?, em_promocao=?, ativo=? WHERE id=?");
            $stmt->bind_param("ssdisiii", $nome, $descricao, $preco, $id_categoria, $imagem_url, $em_promocao, $ativo, $id);
        }
        $stmt->execute();
        $stmt->close();
        $mensagem_sucesso = "Item salvo com sucesso!";
    }
}
// Deletar Categoria ou Item
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['delete'])) {
    if ($_GET['delete'] == 'categoria' && isset($_GET['id'])) {
        $stmt = $conn->prepare("DELETE FROM categorias WHERE id = ?");
        $stmt->bind_param("i", $_GET['id']);
        $stmt->execute();
        header("Location: painel.php"); exit;
    }
    if ($_GET['delete'] == 'item' && isset($_GET['id'])) {
        $stmt = $conn->prepare("DELETE FROM itens_cardapio WHERE id = ?");
        $stmt->bind_param("i", $_GET['id']);
        $stmt->execute();
        header("Location: painel.php"); exit;
    }
}


// Buscar dados para preencher o painel
$config_result = $conn->query("SELECT * FROM configuracoes");
$config = [];
while ($row = $config_result->fetch_assoc()) { $config[$row['config_key']] = $row['config_value']; }
$categorias = $conn->query("SELECT * FROM categorias ORDER BY ordem ASC");
$itens = $conn->query("SELECT i.*, c.nome as nome_categoria FROM itens_cardapio i JOIN categorias c ON i.id_categoria = c.id ORDER BY i.nome ASC");
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Painel de Admin - Cardápio</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-6">
        <header class="flex justify-between items-center mb-8">
            <h1 class="text-4xl font-bold text-gray-800">Painel do Cardápio</h1>
            </header>

        <?php if ($mensagem_sucesso): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert"><p><?php echo $mensagem_sucesso; ?></p></div>
        <?php endif; ?>

        <section class="mb-10">
            <h2 class="text-2xl font-semibold mb-4 border-b pb-2">Configurações Gerais</h2>
            <form action="painel.php" method="POST" class="bg-white p-6 rounded-lg shadow-md grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block font-medium">Nome da Lanchonete</label>
                    <input type="text" name="lanchonete_nome" value="<?php echo htmlspecialchars($config['lanchonete_nome'] ?? ''); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <label class="block font-medium">URL da Logo</label>
                    <input type="text" name="lanchonete_logo_url" value="<?php echo htmlspecialchars($config['lanchonete_logo_url'] ?? ''); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <label class="block font-medium">Horário de Funcionamento</label>
                    <input type="text" name="horario_funcionamento" value="<?php echo htmlspecialchars($config['horario_funcionamento'] ?? ''); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                 <div>
                    <label class="block font-medium">Número do WhatsApp (com 55)</label>
                    <input type="text" name="whatsapp_numero" value="<?php echo htmlspecialchars($config['whatsapp_numero'] ?? ''); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div class="md:col-span-2">
                    <label class="block font-medium">Mensagem Padrão do WhatsApp</label>
                    <input type="text" name="mensagem_whatsapp" value="<?php echo htmlspecialchars($config['mensagem_whatsapp'] ?? ''); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div class="md:col-span-2">
                    <button type="submit" name="salvar_configuracoes" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg">Salvar Configurações</button>
                </div>
            </form>
        </section>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
            <section>
                <h2 class="text-2xl font-semibold mb-4 border-b pb-2">Categorias</h2>
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <form action="painel.php" method="POST" class="mb-6 space-y-3">
                        <input type="hidden" name="id_categoria" id="id_categoria">
                        <div>
                            <label class="block font-medium">Nome da Categoria</label>
                            <input type="text" name="nome_categoria" id="nome_categoria" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                        </div>
                        <div>
                            <label class="block font-medium">Ordem de Exibição</label>
                            <input type="number" name="ordem_categoria" id="ordem_categoria" value="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                        </div>
                        <button type="submit" name="salvar_categoria" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg">Salvar Categoria</button>
                    </form>
                    <table class="w-full text-left">
                        <?php while($cat = $categorias->fetch_assoc()): ?>
                        <tr class="border-t">
                            <td class="py-2"><?php echo htmlspecialchars($cat['nome']); ?> (Ordem: <?php echo htmlspecialchars($cat['ordem']); ?>)</td>
                            <td class="text-right">
                                <button onclick="editCategoria(<?php echo htmlspecialchars(json_encode($cat)); ?>)" class="text-blue-500 hover:underline p-1">Editar</button>
                                <a href="?delete=categoria&id=<?php echo $cat['id']; ?>" onclick="return confirm('Tem certeza?')" class="text-red-500 hover:underline p-1">Excluir</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </table>
                </div>
            </section>

            <section>
                 <h2 class="text-2xl font-semibold mb-4 border-b pb-2">Itens do Cardápio</h2>
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <form action="painel.php" method="POST" class="mb-6 space-y-3">
                        <input type="hidden" name="id_item" id="id_item">
                        <div>
                            <label class="block font-medium">Nome do Item</label>
                            <input type="text" name="nome_item" id="nome_item" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                        </div>
                        <div>
                            <label class="block font-medium">Descrição</label>
                            <textarea name="descricao_item" id="descricao_item" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block font-medium">Preço (Ex: 15.50)</label>
                                <input type="text" name="preco_item" id="preco_item" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                            </div>
                            <div>
                                <label class="block font-medium">Categoria</label>
                                <select name="id_categoria_item" id="id_categoria_item" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                    <?php 
                                    $categorias->data_seek(0); // Reinicia o ponteiro do resultado
                                    while($cat = $categorias->fetch_assoc()){
                                        echo "<option value='{$cat['id']}'>".htmlspecialchars($cat['nome'])."</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                         <div>
                            <label class="block font-medium">URL da Imagem</label>
                            <input type="text" name="imagem_url_item" id="imagem_url_item" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div class="flex items-center space-x-4">
                            <label class="flex items-center"><input type="checkbox" name="em_promocao_item" id="em_promocao_item" class="rounded border-gray-300 text-blue-600 shadow-sm"> <span class="ml-2">Em promoção?</span></label>
                            <label class="flex items-center"><input type="checkbox" name="ativo_item" id="ativo_item" checked class="rounded border-gray-300 text-blue-600 shadow-sm"> <span class="ml-2">Ativo?</span></label>
                        </div>
                        <button type="submit" name="salvar_item" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg">Salvar Item</button>
                    </form>
                    <table class="w-full text-left">
                        <?php while($item = $itens->fetch_assoc()): ?>
                        <tr class="border-t">
                            <td class="py-2">
                                <p class="font-semibold"><?php echo htmlspecialchars($item['nome']); ?></p>
                                <p class="text-xs text-gray-500"><?php echo htmlspecialchars($item['nome_categoria']); ?></p>
                            </td>
                            <td class="text-right">
                                <button onclick="editItem(<?php echo htmlspecialchars(json_encode($item)); ?>)" class="text-blue-500 hover:underline p-1">Editar</button>
                                <a href="?delete=item&id=<?php echo $item['id']; ?>" onclick="return confirm('Tem certeza?')" class="text-red-500 hover:underline p-1">Excluir</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </table>
                </div>
            </section>
        </div>
    </div>
    <script>
        lucide.createIcons();
        function editCategoria(cat) {
            document.getElementById('id_categoria').value = cat.id;
            document.getElementById('nome_categoria').value = cat.nome;
            document.getElementById('ordem_categoria').value = cat.ordem;
            window.scrollTo(0, document.getElementById('nome_categoria').offsetTop - 100);
        }
        function editItem(item) {
            document.getElementById('id_item').value = item.id;
            document.getElementById('nome_item').value = item.nome;
            document.getElementById('descricao_item').value = item.descricao;
            document.getElementById('preco_item').value = item.preco;
            document.getElementById('id_categoria_item').value = item.id_categoria;
            document.getElementById('imagem_url_item').value = item.imagem_url;
            document.getElementById('em_promocao_item').checked = !!parseInt(item.em_promocao);
            document.getElementById('ativo_item').checked = !!parseInt(item.ativo);
            window.scrollTo(0, document.getElementById('nome_item').offsetTop - 100);
        }
    </script>
</body>
</html>
