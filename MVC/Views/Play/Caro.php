<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['caro_scores'])) {
    $_SESSION['caro_scores'] = ['X' => 0, 'O' => 0, 'D' => 0];
}
$scores = $_SESSION['caro_scores'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cờ Caro XO</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;700;800&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/Test/Public/Css/Play/Caro.css">
</head>
<body>
<div class="caro-wrap">
 
  <!-- Header -->
  <div class="caro-header">
    <a href="/Test/index.php?controller=GamesController&action=index" class="back-btn">&#8592; Quay lại</a>
    <div class="logo">Cờ <span class="x">X</span><span class="o">O</span></div>
    <div class="header-sub">Bàn 30×30 &nbsp;·&nbsp; Có AI</div>
    <!-- Cài đặt số quân thắng -->
    <div class="win-setting">
      <label>Thắng khi đủ</label>
      <input type="number" id="win-count" min="3" max="10" value="5">
      <label>quân liên tiếp</label>
    </div>
  </div>
 
  <!-- Modal kết quả -->
  <div class="result-overlay" id="result-overlay">
    <div class="result-modal">
      <div class="result-icon" id="result-icon"></div>
      <div class="result-title" id="result-title"></div>
      <div class="result-sub" id="result-sub"></div>
      <div class="result-btns">
        <button class="result-btn primary" onclick="Game.newGame(); closeModal()">Chơi lại</button>
        <button class="result-btn" onclick="closeModal()">Thoát</button>
      </div>
    </div>
  </div>
 
  <div class="caro-main">
 
    <!-- SIDEBAR -->
    <aside class="caro-sidebar">
 
      <div class="section">
        <div class="section-label">Chế độ</div>
        <div class="toggle-group">
          <button class="toggle-btn active" id="btn-pvp" onclick="UI.setMode('pvp')">2 Người</button>
          <button class="toggle-btn"        id="btn-ai"  onclick="UI.setMode('ai')">vs AI</button>
        </div>
        <div id="ai-options" style="display:none;margin-top:12px">
          <div class="section-label" style="margin-bottom:6px">Độ khó</div>
          <div class="diff-group">
            <button class="diff-btn easy active" id="d-easy" onclick="UI.setDiff('easy')">Dễ</button>
            <button class="diff-btn med"         id="d-med"  onclick="UI.setDiff('med')">Vừa</button>
            <button class="diff-btn hard"        id="d-hard" onclick="UI.setDiff('hard')">Khó</button>
          </div>
          <div class="section-label" style="margin-bottom:6px;margin-top:12px">Bạn chơi quân</div>
          <div class="piece-group">
            <button class="piece-btn X active" id="p-X" onclick="UI.setPlayerPiece('X')">X</button>
            <button class="piece-btn O"        id="p-O" onclick="UI.setPlayerPiece('O')">O</button>
          </div>
        </div>
      </div>
 
      <div class="section">
        <div class="section-label">Lượt đi</div>
        <div class="turn-display">
          <div class="turn-badge X" id="turn-badge">X</div>
          <div class="turn-info">
            <div class="turn-player" id="turn-player">Người chơi X</div>
            <div class="turn-sub"    id="turn-sub">Đến lượt đi</div>
          </div>
        </div>
      </div>
 
      <div class="section">
        <div class="section-label">Tỉ số</div>
        <div class="score-list">
          <div class="score-item">
            <div class="score-label"><div class="dot x"></div><span id="lbl-x">Người X</span></div>
            <div class="score-num x" id="sc-x"><?= $scores['X'] ?></div>
          </div>
          <div class="score-item">
            <div class="score-label"><div class="dot o"></div><span id="lbl-o">Người O</span></div>
            <div class="score-num o" id="sc-o"><?= $scores['O'] ?></div>
          </div>
          <div class="score-item">
            <div class="score-label"><div class="dot d"></div>Hòa</div>
            <div class="score-num d" id="sc-d"><?= $scores['D'] ?></div>
          </div>
        </div>
      </div>
 
      <div class="section">
        <div class="btn-group">
          <button class="btn primary" onclick="Game.newGame()">Ván mới</button>
          <button class="btn"         onclick="Game.resetAll()">Xóa lịch sử</button>
        </div>
      </div>
 
      <div class="section" style="flex:1;border-bottom:none">
        <div class="section-label">Lịch sử ván đấu</div>
        <ul class="history-list" id="history-list"></ul>
      </div>
 
    </aside>
 
    <!-- BÀN CỜ -->
    <div class="board-area">
      <div class="board-wrap">
        <div class="board-container">
          <div class="board-grid" id="board"></div>
        </div>
        <div class="loading-overlay" id="caro-loading">
          <div class="loading-text">AI đang suy nghĩ...</div>
        </div>
      </div>
      <div class="status-bar" id="status-bar"></div>
    </div>
 
  </div>
</div>
 
<script>
'use strict';
 
// ════════════════════════════════════════════════
// CẤU HÌNH
// ════════════════════════════════════════════════
const COLS = 30;
const ROWS = 30;
const TOTAL = COLS * ROWS;
 
// Lấy số quân thắng từ input (mặc định 5)
function getWIN() {
  return parseInt(document.getElementById('win-count').value) || 5;
}
 
// ════════════════════════════════════════════════
// TRẠNG THÁI GAME (hoàn toàn ở client)
// ════════════════════════════════════════════════
const G = {
  board:       Array(TOTAL).fill(''),
  cur:         'X',
  over:        false,
  moveCount:   0,
  lastMove:    -1,
  gameMode:    'pvp',
  aiDiff:      'easy',
  playerPiece: 'X',
  aiPiece:     'O',
  scores:      { X: <?= $scores['X'] ?>, O: <?= $scores['O'] ?>, D: <?= $scores['D'] ?> },
  histCount:   0,
};
 
// ════════════════════════════════════════════════
// KIỂM TRA THẮNG — logic cốt lõi, chạy ở client
// ════════════════════════════════════════════════
 
/**
 * Sau khi đặt quân tại ô index, kiểm tra xem piece có thắng không.
 * Trả về mảng các ô thắng, hoặc null.
 */
function checkWin(board, index, piece, WIN) {
  const r = Math.floor(index / COLS);
  const c = index % COLS;
  const dirs = [[0,1],[1,0],[1,1],[1,-1]];
 
  for (const [dr, dc] of dirs) {
    const line = [index];
 
    // Hướng dương
    for (let s = 1; s < WIN * 2; s++) {
      const nr = r + dr*s, nc = c + dc*s;
      if (nr < 0 || nr >= ROWS || nc < 0 || nc >= COLS) break;
      const ni = nr*COLS + nc;
      if (board[ni] !== piece) break;
      line.push(ni);
    }
    // Hướng âm
    for (let s = 1; s < WIN * 2; s++) {
      const nr = r - dr*s, nc = c - dc*s;
      if (nr < 0 || nr >= ROWS || nc < 0 || nc >= COLS) break;
      const ni = nr*COLS + nc;
      if (board[ni] !== piece) break;
      line.push(ni);
    }
 
    if (line.length >= WIN) return line.slice(0, WIN);
  }
  return null;
}
 
/**
 * Đếm chiều dài chuỗi liên tiếp dài nhất qua ô index cho piece.
 */
function maxLine(board, index, piece) {
  const r = Math.floor(index / COLS);
  const c = index % COLS;
  const dirs = [[0,1],[1,0],[1,1],[1,-1]];
  let max = 0;
 
  for (const [dr, dc] of dirs) {
    let cnt = 1;
    for (let s = 1; s <= 29; s++) {
      const nr = r + dr*s, nc = c + dc*s;
      if (nr < 0 || nr >= ROWS || nc < 0 || nc >= COLS) break;
      if (board[nr*COLS+nc] !== piece) break;
      cnt++;
    }
    for (let s = 1; s <= 29; s++) {
      const nr = r - dr*s, nc = c - dc*s;
      if (nr < 0 || nr >= ROWS || nc < 0 || nc >= COLS) break;
      if (board[nr*COLS+nc] !== piece) break;
      cnt++;
    }
    if (cnt > max) max = cnt;
  }
  return max;
}
 
// ════════════════════════════════════════════════
// MODAL
// ════════════════════════════════════════════════
function showModal(icon, title, sub) {
  document.getElementById('result-icon').textContent  = icon;
  document.getElementById('result-title').textContent = title;
  document.getElementById('result-sub').textContent   = sub;
  document.getElementById('result-overlay').classList.add('show');
}
function closeModal() {
  document.getElementById('result-overlay').classList.remove('show');
}
 
// ════════════════════════════════════════════════
// UI HELPERS
// ════════════════════════════════════════════════
const UI = {
  cells() { return document.querySelectorAll('.cell'); },
 
  markCell(index, piece) {
    const cells = this.cells();
    if (G.lastMove !== -1) cells[G.lastMove].classList.remove('last-move');
    const el = cells[index];
    el.innerHTML = `<span class="sym">${piece}</span>`;
    el.classList.add(piece, 'taken', 'last-move');
    G.lastMove = index;
  },
 
  highlightWin(winCells, piece) {
    const cells = this.cells();
    winCells.forEach(i => {
      cells[i].classList.add('win-cell', 'win-' + piece.toLowerCase());
    });
  },
 
  clearBoard() {
    this.cells().forEach(el => {
      el.textContent = '';
      el.className   = 'cell';
    });
  },
 
  setTurn(piece, player, sub, subCls) {
    const badge = document.getElementById('turn-badge');
    badge.textContent = piece || '·';
    badge.className   = 'turn-badge ' + (piece || 'none');
    document.getElementById('turn-player').textContent = player;
    const s = document.getElementById('turn-sub');
    s.textContent = sub;
    s.className   = 'turn-sub' + (subCls ? ' ' + subCls : '');
  },
 
  setStatus(msg, cls) {
    const el = document.getElementById('status-bar');
    el.textContent = msg;
    el.className   = 'status-bar' + (cls ? ' ' + cls : '');
  },
 
  setScores() {
    document.getElementById('sc-x').textContent = G.scores.X;
    document.getElementById('sc-o').textContent = G.scores.O;
    document.getElementById('sc-d').textContent = G.scores.D;
  },
 
  addHistory(winner) {
    G.histCount++;
    let label, cls;
    if (winner === 'D') { label = 'Hòa'; cls = 'D'; }
    else if (G.gameMode === 'ai') {
      label = winner === G.playerPiece ? 'Bạn thắng' : 'AI thắng';
      cls   = winner;
    } else {
      label = winner + ' thắng'; cls = winner;
    }
    const li = document.createElement('li');
    li.className = 'h-item';
    li.innerHTML = `<span class="h-num">Ván ${G.histCount}</span><span class="h-result ${cls}">${label}</span>`;
    document.getElementById('history-list').prepend(li);
  },
 
  updateLabels() {
    if (G.gameMode === 'ai') {
      document.getElementById('lbl-x').textContent = G.playerPiece === 'X' ? 'Bạn (X)' : 'AI (X)';
      document.getElementById('lbl-o').textContent = G.playerPiece === 'O' ? 'Bạn (O)' : 'AI (O)';
    } else {
      document.getElementById('lbl-x').textContent = 'Người X';
      document.getElementById('lbl-o').textContent = 'Người O';
    }
  },
 
  setLoading(show) {
    document.getElementById('caro-loading').classList.toggle('show', show);
  },
 
  setMode(m) {
    G.gameMode = m;
    document.getElementById('btn-pvp').classList.toggle('active', m === 'pvp');
    document.getElementById('btn-ai').classList.toggle('active', m === 'ai');
    document.getElementById('ai-options').style.display = m === 'ai' ? 'block' : 'none';
    this.updateLabels();
    Game.newGame();
  },
 
  setDiff(d) {
    G.aiDiff = d;
    ['easy','med','hard'].forEach(x =>
      document.getElementById('d-'+x).classList.toggle('active', x === d)
    );
    Game.newGame();
  },
 
  setPlayerPiece(p) {
    G.playerPiece = p;
    G.aiPiece     = p === 'X' ? 'O' : 'X';
    document.getElementById('p-X').classList.toggle('active', p === 'X');
    document.getElementById('p-O').classList.toggle('active', p === 'O');
    this.updateLabels();
    Game.newGame();
  },
};
 
// ════════════════════════════════════════════════
// GAME CONTROLLER
// ════════════════════════════════════════════════
const Game = {
 
  // Người chơi click ô
  play(index) {
    if (G.over || G.board[index] !== '') return;
    if (G.gameMode === 'ai' && G.cur !== G.playerPiece) return;
 
    this.doMove(index);
 
    // Nếu game chưa kết thúc và là chế độ AI → AI đánh
    if (!G.over && G.gameMode === 'ai' && G.cur === G.aiPiece) {
      UI.setLoading(true);
      setTimeout(() => {
        const aiMove = AI.getBestMove();
        if (aiMove !== -1) this.doMove(aiMove);
        UI.setLoading(false);
      }, 100);
    }
  },
 
  // Thực hiện 1 nước đi
  doMove(index) {
    const WIN   = getWIN();
    const piece = G.cur;
 
    G.board[index] = piece;
    G.moveCount++;
    UI.markCell(index, piece);
 
    // Kiểm tra thắng
    const winCells = checkWin(G.board, index, piece, WIN);
    if (winCells) {
      G.over = true;
      UI.highlightWin(winCells, piece);
      G.scores[piece]++;
      UI.setScores();
      UI.addHistory(piece);
      UI.setTurn(null, 'Kết thúc', 'Nhấn "Ván mới" để tiếp tục', '');
 
      if (G.gameMode === 'ai') {
        piece === G.playerPiece
          ? showModal('🏆', 'Bạn thắng!', `Bạn đã tạo được ${WIN} quân liên tiếp!`)
          : showModal('😔', 'Bạn đã thua!', 'AI thắng lần này. Hãy thử lại!');
      } else {
        showModal('🏆', `Người chơi ${piece} thắng!`, `Tạo được ${WIN} quân liên tiếp!`);
      }
      return;
    }
 
    // Kiểm tra hòa
    if (G.moveCount >= TOTAL) {
      G.over = true;
      G.scores.D++;
      UI.setScores();
      UI.addHistory('D');
      UI.setTurn(null, 'Hòa!', 'Bàn cờ đã đầy', '');
      showModal('🤝', 'Hòa!', 'Bàn cờ đã đầy, không ai thắng.');
      return;
    }
 
    // Chuyển lượt
    G.cur = piece === 'X' ? 'O' : 'X';
    this.updateTurnUI();
  },
 
  updateTurnUI() {
    const cur = G.cur;
    if (G.gameMode === 'ai') {
      cur === G.playerPiece
        ? UI.setTurn(cur, 'Bạn (' + cur + ')', 'Đến lượt bạn', '')
        : UI.setTurn(cur, 'AI (' + cur + ')', 'Đang suy nghĩ...', 'thinking');
    } else {
      UI.setTurn(cur, 'Người chơi ' + cur, 'Đến lượt đi', '');
    }
  },
 
  newGame() {
    G.board     = Array(TOTAL).fill('');
    G.cur       = 'X';
    G.over      = false;
    G.moveCount = 0;
    G.lastMove  = -1;
 
    UI.clearBoard();
    UI.setStatus('', '');
    closeModal();
 
    if (G.gameMode === 'ai' && G.aiPiece === 'X') {
      UI.setTurn('X', 'AI (X)', 'Đang suy nghĩ...', 'thinking');
      UI.setLoading(true);
      setTimeout(() => {
        const aiMove = AI.getBestMove();
        if (aiMove !== -1) this.doMove(aiMove);
        UI.setLoading(false);
      }, 300);
    } else {
      this.updateTurnUI();
    }
  },
 
  resetAll() {
    G.scores  = { X: 0, O: 0, D: 0 };
    G.histCount = 0;
    document.getElementById('history-list').innerHTML = '';
    UI.setScores();
 
    // Đồng bộ điểm lên server
    fetch('/Test/index.php?controller=CaroController&action=resetAll', { method: 'POST' })
      .catch(() => {});
 
    this.newGame();
  },
};
 
// ════════════════════════════════════════════════
// AI ENGINE (chạy hoàn toàn ở client JS)
// ════════════════════════════════════════════════
const AI = {
 
  getBestMove() {
    const WIN   = getWIN();
    const board = G.board;
    const ai    = G.aiPiece;
    const pl    = G.playerPiece;
 
    const empty = [];
    for (let i = 0; i < TOTAL; i++) {
      if (board[i] === '') empty.push(i);
    }
    if (empty.length === 0) return -1;
 
    // Nước đầu → tâm bàn
    const center = Math.floor(ROWS/2)*COLS + Math.floor(COLS/2);
    if (G.moveCount === 0) return center;
    if (G.moveCount === 1 && board[center] === '') return center;
 
    switch(G.aiDiff) {
      case 'easy':  return this.easy(board, ai, pl, empty, WIN);
      case 'med':   return this.med(board, ai, pl, empty, WIN);
      default:      return this.hard(board, ai, pl, empty, WIN);
    }
  },
 
  // ── Dễ: chỉ chặn/thắng ngay, còn lại random ──
  easy(board, ai, pl, empty, WIN) {
    const w = this.findWin(board, ai, WIN); if (w !== -1) return w;
    const b = this.findWin(board, pl, WIN); if (b !== -1) return b;
    const near = this.nearby(board, 2);
    const pool = near.length > 0 ? near : empty;
    return pool[Math.floor(Math.random() * pool.length)];
  },
 
  // ── Vừa: thêm chặn chuỗi N-1 ──
  med(board, ai, pl, empty, WIN) {
    const w5 = this.findWin(board, ai, WIN);     if (w5 !== -1) return w5;
    const b5 = this.findWin(board, pl, WIN);     if (b5 !== -1) return b5;
    const w4 = this.findN(board, ai, WIN-1);     if (w4 !== -1) return w4;
    const b4 = this.findN(board, pl, WIN-1);     if (b4 !== -1) return b4;
    const cands = this.nearby(board, 2);
    return this.scored(board, cands.length > 0 ? cands : empty, ai, pl, WIN, false);
  },
 
  // ── Khó: thêm chặn chuỗi 3 mở 2 đầu ──
  hard(board, ai, pl, empty, WIN) {
    const w5 = this.findWin(board, ai, WIN);     if (w5 !== -1) return w5;
    const b5 = this.findWin(board, pl, WIN);     if (b5 !== -1) return b5;
    const w4 = this.findN(board, ai, WIN-1);     if (w4 !== -1) return w4;
    const b4 = this.findN(board, pl, WIN-1);     if (b4 !== -1) return b4;
    const w3 = this.findOpen(board, ai, WIN-2);  if (w3 !== -1) return w3;
    const b3 = this.findOpen(board, pl, WIN-2);  if (b3 !== -1) return b3;
    const cands = this.nearby(board, 2);
    return this.scored(board, cands.length > 0 ? cands : empty, ai, pl, WIN, true);
  },
 
  // Tìm nước thắng ngay (quét toàn bàn)
  findWin(board, piece, WIN) {
    for (let i = 0; i < TOTAL; i++) {
      if (board[i] !== '') continue;
      board[i] = piece;
      const win = checkWin(board, i, piece, WIN);
      board[i] = '';
      if (win) return i;
    }
    return -1;
  },
 
  // Tìm ô tạo chuỗi >= n (dùng nearby để tối ưu)
  findN(board, piece, n) {
    const cands = this.nearby(board, 3);
    for (const i of cands) {
      if (board[i] !== '') continue;
      board[i] = piece;
      const len = maxLine(board, i, piece);
      board[i] = '';
      if (len >= n) return i;
    }
    return -1;
  },
 
  // Tìm ô tạo chuỗi >= n mở 2 đầu
  findOpen(board, piece, n) {
    if (n < 2) return -1;
    const dirs  = [[0,1],[1,0],[1,1],[1,-1]];
    const cands = this.nearby(board, 3);
 
    for (const i of cands) {
      if (board[i] !== '') continue;
      board[i] = piece;
      const r = Math.floor(i/COLS), c = i%COLS;
 
      for (const [dr,dc] of dirs) {
        let cnt = 1, openEnds = 0;
        for (let s=1; s<10; s++) {
          const nr=r+dr*s, nc=c+dc*s;
          if (nr<0||nr>=ROWS||nc<0||nc>=COLS) break;
          const v = board[nr*COLS+nc];
          if (v===piece) cnt++;
          else if (v==='') { openEnds++; break; }
          else break;
        }
        for (let s=1; s<10; s++) {
          const nr=r-dr*s, nc=c-dc*s;
          if (nr<0||nr>=ROWS||nc<0||nc>=COLS) break;
          const v = board[nr*COLS+nc];
          if (v===piece) cnt++;
          else if (v==='') { openEnds++; break; }
          else break;
        }
        if (cnt >= n && openEnds >= 2) { board[i]=''; return i; }
      }
      board[i] = '';
    }
    return -1;
  },
 
  // Ô trống gần các quân đã đặt
  nearby(board, radius) {
    const set = new Set();
    for (let i=0; i<TOTAL; i++) {
      if (board[i]==='') continue;
      const tr=Math.floor(i/COLS), tc=i%COLS;
      for (let dr=-radius; dr<=radius; dr++) {
        for (let dc=-radius; dc<=radius; dc++) {
          const nr=tr+dr, nc=tc+dc;
          if (nr<0||nr>=ROWS||nc<0||nc>=COLS) continue;
          const ni=nr*COLS+nc;
          if (board[ni]==='') set.add(ni);
        }
      }
    }
    return [...set];
  },
 
  // Chọn nước tốt nhất theo điểm heuristic
  scored(board, cands, ai, pl, WIN, deep) {
    let best=-1, bestScore=-Infinity;
    const power=[0,1,10,100,1000,100000];
 
    const score = (b, idx, p) => {
      const r=Math.floor(idx/COLS), c=idx%COLS;
      const dirs=[[0,1],[1,0],[1,1],[1,-1]];
      let total=0;
      for (const [dr,dc] of dirs) {
        let cnt=1, open=0;
        for (let s=1;s<WIN;s++) {
          const nr=r+dr*s,nc=c+dc*s;
          if (nr<0||nr>=ROWS||nc<0||nc>=COLS) break;
          const v=b[nr*COLS+nc];
          if (v===p) cnt++;
          else if (v==='') { open++; break; }
          else break;
        }
        for (let s=1;s<WIN;s++) {
          const nr=r-dr*s,nc=c-dc*s;
          if (nr<0||nr>=ROWS||nc<0||nc>=COLS) break;
          const v=b[nr*COLS+nc];
          if (v===p) cnt++;
          else if (v==='') { open++; break; }
          else break;
        }
        total += (power[Math.min(cnt,5)]||0) * (open+1);
      }
      return total;
    };
 
    for (const i of cands) {
      if (board[i]!=='') continue;
      board[i]=ai;
      let s = score(board,i,ai)*1.1 + score(board,i,pl);
      if (deep) {
        const r=Math.floor(i/COLS), c=i%COLS;
        s += Math.min(r,ROWS-1-r,c,COLS-1-c)*0.5;
      }
      board[i]='';
      if (s>bestScore) { bestScore=s; best=i; }
    }
    return best!==-1 ? best : cands[Math.floor(Math.random()*cands.length)];
  },
};
 
// ════════════════════════════════════════════════
// KHỞI TẠO BÀN CỜ
// ════════════════════════════════════════════════
function initBoard() {
  const grid = document.getElementById('board');
  grid.innerHTML = '';
  for (let i=0; i<TOTAL; i++) {
    const el = document.createElement('div');
    el.className = 'cell';
    el.addEventListener('click', () => Game.play(i));
    grid.appendChild(el);
  }
}
 
// Boot
initBoard();
UI.updateLabels();
Game.updateTurnUI();
 
// Cập nhật WIN khi thay đổi input
document.getElementById('win-count').addEventListener('change', () => Game.newGame());
</script>
</body>
</html>