const STORAGE_KEY = 'replay_clips_db';
const DEMO_VIDEO_URL = 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerBlazes.mp4';
const QUOTA_LIMIT = 5;
const TWO_HOURS_MS = 2 * 60 * 60 * 1000;

// Verifica autenticação
const playerName = localStorage.getItem('replay_player_name');
const userRole = localStorage.getItem('replay_user_role') || 'standard';

if (!playerName) {
    window.location.href = 'index.html';
}

// Atualiza UI do Cabeçalho
document.getElementById('displayPlayerName').innerText = `👤 ${playerName}`;
const roleBadge = document.getElementById('displayUserRole');
if (userRole === 'vip') {
    roleBadge.innerText = 'VIP';
    roleBadge.className = 'badge badge-vip';
} else {
    roleBadge.innerText = 'PADRÃO';
    roleBadge.className = 'badge badge-standard';
}

// Logout
document.getElementById('logoutBtn').addEventListener('click', function(e) {
    e.preventDefault();
    localStorage.removeItem('replay_player_name');
    localStorage.removeItem('replay_user_role');
    window.location.href = 'index.html';
});

function switchTab(tabId, btnElement) {
    document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));

    document.getElementById(tabId).classList.add('active');
    if (btnElement) btnElement.classList.add('active');
}

function getAllClips() {
    try {
        return JSON.parse(localStorage.getItem(STORAGE_KEY)) || [];
    } catch {
        return [];
    }
}

function saveAllClips(clips) {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(clips));
}

function getRecentClipsCount() {
    const clips = getAllClips();
    const twoHoursAgo = Date.now() - TWO_HOURS_MS;
    return clips.filter(c => c.player_name === playerName && c.timestamp >= twoHoursAgo).length;
}

function checkQuota() {
    const count = getRecentClipsCount();
    const isVip = (userRole === 'vip');

    const countEl = document.getElementById('quotaCount');
    const progressEl = document.getElementById('quotaProgress');
    const warningEl = document.getElementById('quotaWarning');
    const buttons = document.querySelectorAll('.buttons-grid .btn');

    if (isVip) {
        countEl.innerText = `${count} / ∞ (VIP)`;
        progressEl.style.width = '100%';
        progressEl.style.background = '#eab308';
        warningEl.classList.add('hidden');
        buttons.forEach(btn => btn.disabled = false);
    } else {
        countEl.innerText = `${count} / ${QUOTA_LIMIT}`;
        const pct = Math.min((count / QUOTA_LIMIT) * 100, 100);
        progressEl.style.width = pct + '%';

        if (count >= QUOTA_LIMIT) {
            warningEl.classList.remove('hidden');
            buttons.forEach(btn => btn.disabled = true);
        } else {
            warningEl.classList.add('hidden');
            buttons.forEach(btn => btn.disabled = false);
        }
    }
}

function generateClip(seconds) {
    const statusEl = document.getElementById('processingStatus');
    const count = getRecentClipsCount();

    if (userRole !== 'vip' && count >= QUOTA_LIMIT) {
        statusEl.className = 'status-msg error';
        statusEl.innerText = '❌ Limite de 5 clipes a cada 2 horas atingido.';
        statusEl.classList.remove('hidden');
        return;
    }

    statusEl.className = 'status-msg loading';
    statusEl.innerText = `⏳ Processando recorte de ${seconds} segundos...`;
    statusEl.classList.remove('hidden');

    // Simula delay de processamento de vídeo (1.2s)
    setTimeout(() => {
        const now = new Date();
        const todayStr = now.toISOString().split('T')[0];
        const timeStr = now.toTimeString().split(' ')[0];

        const newClip = {
            id: 'clip_' + Date.now(),
            player_name: playerName,
            seconds: seconds,
            timestamp: now.getTime(),
            date: todayStr,
            time: timeStr,
            url: DEMO_VIDEO_URL
        };

        const clips = getAllClips();
        clips.unshift(newClip);
        saveAllClips(clips);

        statusEl.className = 'status-msg success';
        statusEl.innerText = `✅ Clipe de ${seconds}s salvo na sua galeria!`;
        checkQuota();

        setTimeout(() => statusEl.classList.add('hidden'), 3500);
    }, 1200);
}

function loadClips() {
    const grid = document.getElementById('clipsGrid');
    const clips = getAllClips();
    const todayStr = new Date().toISOString().split('T')[0];

    const userTodayClips = clips.filter(c => c.player_name === playerName && c.date === todayStr);

    if (userTodayClips.length === 0) {
        grid.innerHTML = '<p style="color: #94a3b8; grid-column: 1/-1;">Nenhum clipe gerado hoje até o momento. Salve seus lances na aba Câmera & Corte!</p>';
        return;
    }

    grid.innerHTML = '';
    userTodayClips.forEach(clip => {
        const card = document.createElement('div');
        card.className = 'clip-card';
        card.innerHTML = `
            <video controls preload="metadata">
                <source src="${clip.url}" type="video/mp4">
            </video>
            <div class="clip-body">
                <div class="clip-meta">
                    <span>⏱️ ${clip.seconds} Segundos</span>
                    <span>🕒 ${clip.time}</span>
                </div>
                <div class="clip-actions">
                    <a href="${clip.url}" download="replay_${clip.id}.mp4" target="_blank" class="btn btn-sm btn-primary">⬇️ Baixar</a>
                    <button class="btn btn-sm btn-secondary" onclick="openShareModal('${clip.url}')">🔗 Compartilhar</button>
                </div>
            </div>
        `;
        grid.appendChild(card);
    });
}

function openShareModal(clipUrl) {
    document.getElementById('shareLinkInput').value = clipUrl;
    
    const encodedUrl = encodeURIComponent(clipUrl);
    const text = encodeURIComponent("Olha esse meu lance na quadra! ⚽🔥");

    document.getElementById('shareInstagram').href = `https://instagram.com`;
    document.getElementById('shareWhatsapp').href = `https://api.whatsapp.com/send?text=${text}%20${encodedUrl}`;
    document.getElementById('shareFacebook').href = `https://www.facebook.com/sharer/sharer.php?u=${encodedUrl}`;
    document.getElementById('shareTwitter').href = `https://twitter.com/intent/tweet?text=${text}&url=${encodedUrl}`;

    document.getElementById('shareModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('shareModal').classList.add('hidden');
}

function copyShareLink() {
    const input = document.getElementById('shareLinkInput');
    input.select();
    navigator.clipboard.writeText(input.value).then(() => {
        alert('Link copiado para a área de transferência!');
    });
}

// Inicialização
document.addEventListener('DOMContentLoaded', () => {
    checkQuota();
});
