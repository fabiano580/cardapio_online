<?php
// Conexão com o banco de dados
$host = "localhost"; $usuario = "u784380269_exemplo"; $senha = "EchXThGfBplO8J8@!"; $banco = "u784380269_exemplo";
$conn = new mysqli($host, $usuario, $senha, $banco);
if ($conn->connect_error) { die("Erro na conexão: " . $conn->connect_error); }
$conn->set_charset("utf8");

// Buscar dados
$config_result = $conn->query("SELECT * FROM configuracoes");
$config = [];
while ($row = $config_result->fetch_assoc()) { $config[$row['config_key']] = $row['config_value']; }

$categorias = $conn->query("SELECT * FROM categorias WHERE id IN (SELECT DISTINCT id_categoria FROM itens_cardapio WHERE ativo = 1) ORDER BY ordem ASC");

$itens_result = $conn->query("SELECT * FROM itens_cardapio WHERE ativo = 1");
$itens_por_categoria = [];
while ($item = $itens_result->fetch_assoc()) {
    $itens_por_categoria[$item['id_categoria']][] = $item;
}

$whatsapp_url = "https://wa.me/{$config['whatsapp_numero']}?text=" . urlencode($config['mensagem_whatsapp']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $config['lanchonete_nome'] ?? 'Cardápio Online'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root { --cor-principal: <?php echo $config['cor_principal'] ?? '#ea580c'; ?>; }
        .bg-principal { background-color: var(--cor-principal); }
        .text-principal { color: var(--cor-principal); }
        .border-principal { border-color: var(--cor-principal); }
    </style>
</head>
<body class="bg-gray-50">

    <a href="<?php echo $whatsapp_url; ?>" target="_blank" class="fixed bottom-5 right-5 bg-green-500 text-white p-4 rounded-full shadow-lg z-20 hover:scale-110 transition-transform">
        <i data-lucide="message-circle" class="w-8 h-8"></i>
    </a>

    <header class="bg-white shadow-md sticky top-0 z-10">
        <div class="container mx-auto p-4 flex flex-col sm:flex-row justify-between items-center">
            <div class="flex items-center gap-4 mb-4 sm:mb-0">
                <img src="<?php echo $config['lanchonete_logo_url'] ?? ''; ?>" alt="Logo" class="h-16 w-16 rounded-full object-cover">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800"><?php echo $config['lanchonete_nome'] ?? ''; ?></h1>
                    <p class="text-sm text-gray-500 flex items-center gap-2"><i data-lucide="clock" class="w-4 h-4"></i> <?php echo $config['horario_funcionamento'] ?? ''; ?></p>
                </div>
            </div>
            <div class="w-full sm:w-auto">
                <input type="text" id="searchInput" placeholder="Buscar no cardápio..." class="w-full p-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-principal">
            </div>
        </div>
        <nav class="bg-gray-100 border-t border-b">
            <div class="container mx-auto px-4 py-2 flex items-center overflow-x-auto space-x-4">
                <button class="category-filter active whitespace-nowrap px-4 py-2 rounded-full bg-principal text-white" data-filter="all">Todos</button>
                <?php while($cat = $categorias->fetch_assoc()): ?>
                    <button class="category-filter whitespace-nowrap px-4 py-2 rounded-full bg-white text-gray-700 hover:bg-gray-200" data-filter="cat-<?php echo $cat['id']; ?>"><?php echo $cat['nome']; ?></button>
                <?php endwhile; ?>
            </div>
        </nav>
    </header>

    <main class="container mx-auto p-4 sm:p-6">
        <?php 
        $categorias->data_seek(0); // Reinicia o ponteiro
        while($cat = $categorias->fetch_assoc()): 
            if(isset($itens_por_categoria[$cat['id']])):
        ?>
            <section id="cat-<?php echo $cat['id']; ?>" class="category-section mb-12">
                <h2 class="text-3xl font-bold text-gray-800 mb-6 border-l-4 border-principal pl-4"><?php echo $cat['nome']; ?></h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach($itens_por_categoria[$cat['id']] as $item): ?>
                        <div class="item-card bg-white rounded-lg shadow-md overflow-hidden transform hover:scale-105 transition-transform cursor-pointer">
                            <div class="relative">
                                <img src="<?php echo $item['imagem_url'] ?? 'https://via.placeholder.com/300x200'; ?>" alt="<?php echo $item['nome']; ?>" class="w-full h-48 object-cover">
                                <?php if($item['em_promocao']): ?>
                                    <span class="absolute top-2 left-2 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-full">PROMO</span>
                                <?php endif; ?>
                            </div>
                            <div class="p-4">
                                <h3 class="text-xl font-bold text-gray-900 item-name"><?php echo $item['nome']; ?></h3>
                                <p class="text-gray-600 mt-1 text-sm item-desc"><?php echo $item['descricao']; ?></p>
                                <p class="text-2xl font-extrabold text-principal mt-4">
                                    R$ <?php echo number_format($item['preco'], 2, ',', '.'); ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php 
            endif;
        endwhile; 
        $conn->close();
        ?>
    </main>

    <script>
        lucide.createIcons();

        // Filtro de Categoria
        const filterButtons = document.querySelectorAll('.category-filter');
        const sections = document.querySelectorAll('.category-section');

        filterButtons.forEach(button => {
            button.addEventListener('click', () => {
                filterButtons.forEach(btn => {
                    btn.classList.remove('active', 'bg-principal', 'text-white');
                    btn.classList.add('bg-white', 'text-gray-700');
                });
                button.classList.add('active', 'bg-principal', 'text-white');
                button.classList.remove('bg-white', 'text-gray-700');
                
                const filter = button.dataset.filter;
                sections.forEach(section => {
                    if (filter === 'all' || section.id === filter) {
                        section.style.display = 'block';
                    } else {
                        section.style.display = 'none';
                    }
                });
            });
        });

        // Barra de Busca
        const searchInput = document.getElementById('searchInput');
        searchInput.addEventListener('keyup', () => {
            const searchTerm = searchInput.value.toLowerCase();
            document.querySelectorAll('.item-card').forEach(card => {
                const name = card.querySelector('.item-name').textContent.toLowerCase();
                const desc = card.querySelector('.item-desc').textContent.toLowerCase();
                if (name.includes(searchTerm) || desc.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
