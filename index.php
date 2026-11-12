<?php
session_start();
if (!isset($_SESSION['player_name'])) {
    header('Location: login.php');
    exit;
}
$playerName = $_SESSION['player_name'];
$userRole = $_SESSION['user_role'] ?? 'standard';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Replay Quadra - Ao Vivo</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <!-- Header / Navbar -->
    <header class="navbar">
        <div class="container nav-content">
            <div class="brand">
                <span class="logo">🎥</span>
                <h2>Quadra 1 - Arena Replay</h2>
            </div>
            <div class="user-badge">
                <span class="user-name">👤 <?= $playerName ?></span>
                <?php if($userRole === 'vip'): ?>
                    <span class="badge badge-vip">VIP</span>
                <?php else: ?>
                    <span class="badge badge-standard">PADRÃO</span>
                <?php endif; ?>
                <a href="logout.php" class="logout-link">Sair</a>
            </div>
        </div>
    </header>

    <!-- Navigation Tabs -->
    <div class="tabs-bar">
        <div class="container tabs-container">
            <button class="tab-btn active" onclick="switchTab('live-tab')">🔴 Camera & Corte</button>
            <button class="tab-btn" onclick="switchTab('gallery-tab'); loadClips();">🎬 Meus Clipes do Dia</button>
        </div>
    </div>

    <!-- Main Content -->
    <main class="container content">

        <!-- TAB 1: Transmissão Ao Vivo e Geração de Clipes -->
        <section id="live-tab" class="tab-content active">
            <div class="video-container">
                <!-- Em produção real entra o stream RTSP/HLS da câmera Ubiquiti -->
                <div class="live-tag">AO VIVO</div>
                <video id="livePlayer" class="video-player" autoplay loop muted playsinline poster="https://images.unsplash.com/photo-1574629810360-7efbbe195018?auto=format&fit=crop&w=1200&q=80">
                    <source src="https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerBlazes.mp4" type="video/mp4">
                    Seu navegador não suporta a exibição de vídeo.
                </video>
            </div>

            <!-- Painel de Limites e Acionamento -->
            <div class="card clip-controls">
                <h3>✂️ Salvar Jogada Recente</h3>
                <p class="subtitle">Escolha o tempo de gravação retroativa desejado:</p>

                <!-- Quota Counter -->
                <div class="quota-box">
                    <div class="quota-info">
                        <span>Clipes gerados nas últimas 2 horas:</span>
                        <strong id="quotaCount">0 / 5</strong>
                    </div>
                    <div class="progress-bar">
                        <div id="quotaProgress" class="progress-fill" style="width: 0%;"></div>
                    </div>
                    <p id="quotaWarning" class="warning-text hidden">⚠️ Você atingiu o limite de 5 clipes por 2h. Solicite uma conta VIP para liberar recortes ilimitados!</p>
                </div>

                <!-- Action Buttons -->
                <div class="buttons-grid">
                    <button class="btn btn-action" onclick="generateClip(30)">
                        <span class="time">⚡ 30 Segundos</span>
                        <span class="sub">Lance rápido / Gol</span>
                    </button>
                    <button class="btn btn-action btn-featured" onclick="generateClip(60)">
                        <span class="time">🔥 60 Segundos</span>
                        <span class="sub">Jogada completa (Recomendado)</span>
                    </button>
                    <button class="btn btn-action" onclick="generateClip(120)">
                        <span class="time">🏆 120 Segundos</span>
                        <span class="sub">Rali longo / Sequência</span>
                    </button>
                </div>

                <div id="processingStatus" class="status-msg hidden"></div>
            </div>
        </section>

        <!-- TAB 2: Galeria de Clipes do Dia -->
        <section id="gallery-tab" class="tab-content">
            <div class="gallery-header">
                <h3>📁 Seus Lances Salvos Hoje</h3>
                <button class="btn btn-sm btn-secondary" onclick="loadClips()">🔄 Atualizar</button>
            </div>
            <div id="clipsGrid" class="clips-grid">
                <!-- Renderizado via JS -->
            </div>
        </section>

    </main>

    <!-- Modal de Compartilhamento -->
    <div id="shareModal" class="modal hidden">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal()">&times;</span>
            <h3>🚀 Compartilhar Jogada</h3>
            <p>Envie seu lance direto para suas redes sociais!</p>
            
            <div class="share-buttons">
                <a id="shareInstagram" class="btn btn-share btn-ig" target="_blank">📸 Instagram (Stories/Reels)</a>
                <a id="shareWhatsapp" class="btn btn-share btn-wa" target="_blank">💬 WhatsApp</a>
                <a id="shareFacebook" class="btn btn-share btn-fb" target="_blank">📘 Facebook</a>
                <a id="shareTwitter" class="btn btn-share btn-tw" target="_blank">𝕏 Twitter / X</a>
            </div>

            <div class="copy-link-box">
                <input type="text" id="shareLinkInput" readonly>
                <button class="btn btn-secondary" onclick="copyShareLink()">Copiar Link</button>
            </div>
        </div>
    </div>

    <script src="js/app.js"></script>
</body>
</html>
