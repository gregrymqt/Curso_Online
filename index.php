<?php
require_once 'includes/db_connect.php';

// Busca conteúdo de marketing do banco (opcional)
// $stmt = $pdo->query("SELECT * FROM marketing_content");
// $contents = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Curso Incrível - Assine Já!</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="marketing-page">
        <header>
            <h1>Transforme sua vida com nosso curso!</h1>
            <img src="assets/images/banner.jpg" alt="Banner do curso">
        </header>
        
        <main class="marketing-content">
            <section>
                <img src="assets/images/beneficio1.jpg" alt="Benefício 1">
                <h2>Aprenda com os melhores</h2>
                <p>Nossos instrutores são especialistas no mercado com anos de experiência.</p>
            </section>
            
            <section>
                <img src="assets/images/beneficio2.jpg" alt="Benefício 2">
                <h2>Conteúdo exclusivo</h2>
                <p>Acesso a materiais que você não encontra em nenhum outro lugar.</p>
            </section>
            
            <section>
                <img src="assets/images/beneficio3.jpg" alt="Benefício 3">
                <h2>Suporte personalizado</h2>
                <p>Tire todas suas dúvidas diretamente com nossa equipe.</p>
            </section>
            
            <div class="cta-section">
                <h2>Não perca essa oportunidade!</h2>
                <a href="register.php" class="cta-button">ASSINE JÁ!</a>
            </div>
        </main>
        
        <footer>
            <p>© <?php echo date('Y'); ?> Curso Incrível. Todos os direitos reservados.</p>
        </footer>
    </div>
    
    <script src="assets/js/script.js"></script>
</body>
</html>