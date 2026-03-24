<?php
/* ══════════════════════════════════════════════════════
   CaroController.php — Controller MVC
   Routes:
     ?controller=CaroController&action=move
     ?controller=CaroController&action=newGame
     ?controller=CaroController&action=resetAll
     ?controller=CaroController&action=getHistory
     ?controller=CaroController&action=getState
   ══════════════════════════════════════════════════════ */
 
class CaroController extends Controller
{
    private const COLS  = 30;
    private const ROWS  = 30;
    private const WIN   = 5;
    private const TOTAL = self::ROWS * self::COLS;
 
    // ════════════════ SESSION ════════════════════════
 
    private function initSession(): void
    {
        if (!isset($_SESSION['caro_board'])) $this->resetSession();
    }
 
    private function resetSession(): void
    {
        $_SESSION['caro_board']        = array_fill(0, self::TOTAL, '');
        $_SESSION['caro_current']      = 'X';
        $_SESSION['caro_move_count']   = 0;
        $_SESSION['caro_over']         = false;
        $_SESSION['caro_last_move']    = -1;
        $_SESSION['caro_win_cells']    = [];
        $_SESSION['caro_game_mode']    = $_SESSION['caro_game_mode']    ?? 'pvp';
        $_SESSION['caro_ai_diff']      = $_SESSION['caro_ai_diff']      ?? 'easy';
        $_SESSION['caro_player_piece'] = $_SESSION['caro_player_piece'] ?? 'X';
        $_SESSION['caro_ai_piece']     = $_SESSION['caro_ai_piece']     ?? 'O';
        $_SESSION['caro_scores']       = $_SESSION['caro_scores']       ?? ['X'=>0,'O'=>0,'D'=>0];
    }
 
    private function fullReset(): void
    {
        $_SESSION['caro_scores'] = ['X'=>0,'O'=>0,'D'=>0];
        $this->resetSession();
    }
 
    // ════════════════ PUBLIC ACTIONS ═════════════════
 
    public function move(): void
    {
        $this->initSession();
        $this->jsonHeader();
 
        $cell = (int)($_POST['cell'] ?? -1);
        if ($cell < 0 || $cell >= self::TOTAL) {
            echo $this->json(['ok'=>false,'error'=>'Invalid cell']); return;
        }
 
        // Đồng bộ settings từ client
        $_SESSION['caro_game_mode']    = $_POST['game_mode']    ?? $_SESSION['caro_game_mode'];
        $_SESSION['caro_ai_diff']      = $_POST['ai_diff']      ?? $_SESSION['caro_ai_diff'];
        $_SESSION['caro_player_piece'] = $_POST['player_piece'] ?? $_SESSION['caro_player_piece'];
        $_SESSION['caro_ai_piece']     = ($_SESSION['caro_player_piece'] === 'X') ? 'O' : 'X';
 
        // Game kết thúc hoặc ô đã có quân
        if ($_SESSION['caro_over'] || $_SESSION['caro_board'][$cell] !== '') {
            echo $this->json(['ok'=>false,'error'=>'Invalid move']); return;
        }
 
        // Chế độ AI: chỉ nhận đúng lượt người chơi
        if ($_SESSION['caro_game_mode'] === 'ai'
            && $_SESSION['caro_current'] !== $_SESSION['caro_player_piece']) {
            echo $this->json(['ok'=>false,'error'=>'Not your turn']); return;
        }
 
        // --- Nước người chơi ---
        $playerResult = $this->applyMove($cell);
 
        // --- Nước AI (nếu game chưa kết thúc) ---
        $aiResult = null;
        if (!$_SESSION['caro_over']
            && $_SESSION['caro_game_mode'] === 'ai'
            && $_SESSION['caro_current'] === $_SESSION['caro_ai_piece']) {
            $aiCell = $this->getBestMove();
            if ($aiCell !== -1) $aiResult = $this->applyMove($aiCell);
        }
 
        echo $this->json([
            'ok'         => true,
            'player'     => $playerResult,
            'ai'         => $aiResult,
            'board'      => $_SESSION['caro_board'],
            'current'    => $_SESSION['caro_current'],
            'over'       => $_SESSION['caro_over'],
            'scores'     => $_SESSION['caro_scores'],
            'move_count' => $_SESSION['caro_move_count'],
        ]);
    }
 
    public function newGame(): void
    {
        $this->initSession();
        $this->jsonHeader();
        $_SESSION['caro_game_mode']    = $_POST['game_mode']    ?? $_SESSION['caro_game_mode'];
        $_SESSION['caro_ai_diff']      = $_POST['ai_diff']      ?? $_SESSION['caro_ai_diff'];
        $_SESSION['caro_player_piece'] = $_POST['player_piece'] ?? $_SESSION['caro_player_piece'];
        $_SESSION['caro_ai_piece']     = ($_SESSION['caro_player_piece'] === 'X') ? 'O' : 'X';
        $this->resetSession();
        echo $this->json(['ok'=>true,'board'=>$_SESSION['caro_board'],'current'=>$_SESSION['caro_current'],'scores'=>$_SESSION['caro_scores']]);
    }
 
    public function resetAll(): void
    {
        $this->initSession();
        $this->jsonHeader();
        $this->clearHistory();
        $this->fullReset();
        echo $this->json(['ok'=>true,'board'=>$_SESSION['caro_board'],'current'=>$_SESSION['caro_current'],'scores'=>$_SESSION['caro_scores']]);
    }
 
    public function getHistory(): void
    {
        $this->jsonHeader();
        echo $this->json(['ok'=>true,'history'=>$this->fetchHistory(50)]);
    }
 
    public function getState(): void
    {
        $this->initSession();
        $this->jsonHeader();
        echo $this->json(['ok'=>true,'board'=>$_SESSION['caro_board'],'current'=>$_SESSION['caro_current'],'over'=>$_SESSION['caro_over'],'scores'=>$_SESSION['caro_scores']]);
    }
 
    // ════════════════ GAME LOGIC ═════════════════════
 
    private function applyMove(int $cell): array
    {
        $cur  = $_SESSION['caro_current'];
        $board = &$_SESSION['caro_board'];
        $board[$cell] = $cur;
        $_SESSION['caro_last_move'] = $cell;
        $_SESSION['caro_move_count']++;
 
        $win = $this->checkWin($board, $cell, $cur);
        if ($win !== null) {
            $_SESSION['caro_over']      = true;
            $_SESSION['caro_win_cells'] = $win;
            $_SESSION['caro_scores'][$cur]++;
            $this->saveHistory($cur);
            return ['cell'=>$cell,'piece'=>$cur,'win'=>true,'win_cells'=>$win,'draw'=>false];
        }
 
        if ($_SESSION['caro_move_count'] >= self::TOTAL) {
            $_SESSION['caro_over'] = true;
            $_SESSION['caro_scores']['D']++;
            $this->saveHistory('D');
            return ['cell'=>$cell,'piece'=>$cur,'win'=>false,'draw'=>true];
        }
 
        $_SESSION['caro_current'] = ($cur === 'X') ? 'O' : 'X';
        return ['cell'=>$cell,'piece'=>$cur,'win'=>false,'draw'=>false];
    }
 
    /**
     * Kiểm tra thắng tại ô $i với quân $p.
     * Trả về mảng WIN ô hoặc null.
     */
    private function checkWin(array $board, int $i, string $p): ?array
    {
        $r = intdiv($i, self::COLS);
        $c = $i % self::COLS;

        $dirs = [[0,1],[1,0],[1,1],[1,-1]];

        foreach ($dirs as [$dr,$dc]) {

            $cells = [$i];
            $count = 1;

            // đi xuôi
            for ($s=1;$s<self::WIN;$s++){
                $nr=$r+$dr*$s;
                $nc=$c+$dc*$s;

                if($nr<0||$nr>=self::ROWS||$nc<0||$nc>=self::COLS) break;

                $idx=$nr*self::COLS+$nc;

                if($board[$idx]!==$p) break;

                $cells[]=$idx;
                $count++;
            }

            // đi ngược
            for ($s=1;$s<self::WIN;$s++){
                $nr=$r-$dr*$s;
                $nc=$c-$dc*$s;

                if($nr<0||$nr>=self::ROWS||$nc<0||$nc>=self::COLS) break;

                $idx=$nr*self::COLS+$nc;

                if($board[$idx]!==$p) break;

                array_unshift($cells,$idx);
                $count++;
            }

            if($count>=self::WIN){
                return array_slice($cells,0,self::WIN);
            }
        }

        return null;
    }
 
    // ════════════════ AI ENGINE ══════════════════════
 
    private function getBestMove(): int
    {
        $board  = $_SESSION['caro_board'];
        $ai     = $_SESSION['caro_ai_piece'];
        $player = $_SESSION['caro_player_piece'];
        $diff   = $_SESSION['caro_ai_diff'];
        $count  = $_SESSION['caro_move_count'];
 
        $empty = [];
        for ($i = 0; $i < self::TOTAL; $i++) {
            if ($board[$i] === '') $empty[] = $i;
        }
        if (empty($empty)) return -1;
 
        // Nước đầu → tâm bàn
        $center = intdiv(self::ROWS,2)*self::COLS + intdiv(self::COLS,2);
        if ($count === 0) return $center;
        if ($count === 1 && $board[$center] === '') return $center;
 
        return match($diff) {
            'easy'  => $this->aiEasy($board, $ai, $player, $empty),
            'med'   => $this->aiMed($board, $ai, $player, $empty),
            default => $this->aiHard($board, $ai, $player, $empty),
        };
    }
 
    private function aiEasy(array $b, string $ai, string $pl, array $empty): int
    {
        $w = $this->findWinMove($b, $ai);   if ($w  !== -1) return $w;
        $k = $this->findWinMove($b, $pl);   if ($k  !== -1) return $k;
        $near = $this->nearbyEmpty($b, 2);
        $pool = !empty($near) ? $near : $empty;
        return $pool[array_rand($pool)];
    }
 
    private function aiMed(array $b, string $ai, string $pl, array $empty): int
    {
        $w5 = $this->findWinMove($b, $ai);    if ($w5 !== -1) return $w5;
        $b5 = $this->findWinMove($b, $pl);    if ($b5 !== -1) return $b5;
        $w4 = $this->findNInRow($b, $ai, 4);  if ($w4 !== -1) return $w4;
        $b4 = $this->findNInRow($b, $pl, 4);  if ($b4 !== -1) return $b4;
        $cands = $this->nearbyEmpty($b, 2);
        return $this->scoredMove($b, !empty($cands)?$cands:$empty, $ai, $pl, false);
    }
 
    private function aiHard(array $b, string $ai, string $pl, array $empty): int
    {
        $w5 = $this->findWinMove($b, $ai);    if ($w5 !== -1) return $w5;
        $b5 = $this->findWinMove($b, $pl);    if ($b5 !== -1) return $b5;
        $w4 = $this->findNInRow($b, $ai, 4);  if ($w4 !== -1) return $w4;
        $b4 = $this->findNInRow($b, $pl, 4);  if ($b4 !== -1) return $b4;
        $w3 = $this->findOpen3($b, $ai);       if ($w3 !== -1) return $w3;
        $b3 = $this->findOpen3($b, $pl);       if ($b3 !== -1) return $b3;
        $cands = $this->nearbyEmpty($b, 2);
        return $this->scoredMove($b, !empty($cands)?$cands:$empty, $ai, $pl, true);
    }
 
    /** Tìm ô thắng ngay (5 liên tiếp) — quét toàn bàn */
    private function findWinMove(array $b, string $piece): int
    {
        for ($i = 0; $i < self::TOTAL; $i++) {
            if ($b[$i] !== '') continue;
            $b[$i] = $piece;
            $win   = $this->checkWin($b, $i, $piece);
            $b[$i] = '';
            if ($win !== null) return $i;
        }
        return -1;
    }
 
    /** Tìm ô tạo chuỗi >= $n — dùng nearbyEmpty */
    private function findNInRow(array $b, string $piece, int $n): int
    {
        $cands = $this->nearbyEmpty($b, 3);
        foreach ($cands as $i) {
            if ($b[$i] !== '') continue;
            $b[$i] = $piece;
            $len   = $this->maxLine($b, $i, $piece);
            $b[$i] = '';
            if ($len >= $n) return $i;
        }
        return -1;
    }
 
    /** Tìm ô tạo chuỗi 3 mở 2 đầu */
    private function findOpen3(array $b, string $piece): int
    {
        $dirs  = [[0,1],[1,0],[1,1],[1,-1]];
        $cands = $this->nearbyEmpty($b, 3);
 
        foreach ($cands as $i) {
            if ($b[$i] !== '') continue;
            $b[$i] = $piece;
            $r = intdiv($i, self::COLS); $c = $i % self::COLS;
 
            foreach ($dirs as [$dr,$dc]) {
                $cnt = 1; $openEnds = 0;
                for ($s=1;$s<self::WIN;$s++) {
                    $nr=$r+$dr*$s; $nc=$c+$dc*$s;
                    if ($nr<0||$nr>=self::ROWS||$nc<0||$nc>=self::COLS) break;
                    $v=$b[$nr*self::COLS+$nc];
                    if ($v===$piece) $cnt++;
                    elseif ($v==='') { $openEnds++; break; }
                    else break;
                }
                for ($s=1;$s<self::WIN;$s++) {
                    $nr=$r-$dr*$s; $nc=$c-$dc*$s;
                    if ($nr<0||$nr>=self::ROWS||$nc<0||$nc>=self::COLS) break;
                    $v=$b[$nr*self::COLS+$nc];
                    if ($v===$piece) $cnt++;
                    elseif ($v==='') { $openEnds++; break; }
                    else break;
                }
                if ($cnt>=3 && $openEnds>=2) { $b[$i]=''; return $i; }
            }
            $b[$i] = '';
        }
        return -1;
    }
 
    /** Độ dài chuỗi dài nhất qua ô $i cho quân $p */
    private function maxLine(array $b, int $i, string $p): int
    {
        $r=intdiv($i,self::COLS); $c=$i%self::COLS;
        $dirs=[[0,1],[1,0],[1,1],[1,-1]]; $max=0;
        foreach ($dirs as [$dr,$dc]) {
            $cnt=1;
            for ($s=1;$s<=29;$s++) {
                $nr=$r+$dr*$s; $nc=$c+$dc*$s;
                if ($nr<0||$nr>=self::ROWS||$nc<0||$nc>=self::COLS) break;
                if ($b[$nr*self::COLS+$nc]!==$p) break;
                $cnt++;
            }
            for ($s=1;$s<=29;$s++) {
                $nr=$r-$dr*$s; $nc=$c-$dc*$s;
                if ($nr<0||$nr>=self::ROWS||$nc<0||$nc>=self::COLS) break;
                if ($b[$nr*self::COLS+$nc]!==$p) break;
                $cnt++;
            }
            if ($cnt>$max) $max=$cnt;
        }
        return $max;
    }
 
    /** Ô trống trong bán kính $radius quanh quân đã đặt */
    private function nearbyEmpty(array $b, int $radius): array
    {
        $set=[];
        for ($i=0;$i<self::TOTAL;$i++) {
            if ($b[$i]==='') continue;
            $tr=intdiv($i,self::COLS); $tc=$i%self::COLS;
            for ($dr=-$radius;$dr<=$radius;$dr++) {
                for ($dc=-$radius;$dc<=$radius;$dc++) {
                    $nr=$tr+$dr; $nc=$tc+$dc;
                    if ($nr<0||$nr>=self::ROWS||$nc<0||$nc>=self::COLS) continue;
                    $ni=$nr*self::COLS+$nc;
                    if ($b[$ni]==='') $set[$ni]=true;
                }
            }
        }
        return array_keys($set);
    }
 
    /** Chọn nước tốt nhất theo heuristic */
    private function scoredMove(array $b, array $cands, string $ai, string $pl, bool $deep): int
    {
        $best=-1; $bestScore=PHP_INT_MIN;
        foreach ($cands as $i) {
            if ($b[$i]!=='') continue;
            $b[$i]=$ai;
            $score=$this->heurScore($b,$i,$ai)*1.1+$this->heurScore($b,$i,$pl);
            if ($deep) {
                $r=intdiv($i,self::COLS); $c=$i%self::COLS;
                $score+=min($r,self::ROWS-1-$r,$c,self::COLS-1-$c)*0.5;
            }
            $b[$i]='';
            if ($score>$bestScore) { $bestScore=$score; $best=$i; }
        }
        if ($best!==-1) return $best;
        $valid=array_values(array_filter($cands,fn($i)=>$b[$i]===''));
        return !empty($valid)?$valid[array_rand($valid)]:-1;
    }
 
    /** Điểm heuristic tại ô $i cho quân $p */
    private function heurScore(array $b, int $i, string $p): float
    {
        $r=intdiv($i,self::COLS); $c=$i%self::COLS;
        $dirs=[[0,1],[1,0],[1,1],[1,-1]]; $power=[0,1,10,100,1000,100000]; $total=0.0;
        foreach ($dirs as [$dr,$dc]) {
            $cnt=1; $open=0;
            for ($s=1;$s<self::WIN;$s++) {
                $nr=$r+$dr*$s; $nc=$c+$dc*$s;
                if ($nr<0||$nr>=self::ROWS||$nc<0||$nc>=self::COLS) break;
                $v=$b[$nr*self::COLS+$nc];
                if ($v===$p) $cnt++;
                elseif ($v==='') { $open++; break; }
                else break;
            }
            for ($s=1;$s<self::WIN;$s++) {
                $nr=$r-$dr*$s; $nc=$c-$dc*$s;
                if ($nr<0||$nr>=self::ROWS||$nc<0||$nc>=self::COLS) break;
                $v=$b[$nr*self::COLS+$nc];
                if ($v===$p) $cnt++;
                elseif ($v==='') { $open++; break; }
                else break;
            }
            $total+=($power[min($cnt,5)]??0)*($open+1);
        }
        return $total;
    }
 
    // ════════════════ DATABASE ═══════════════════════
 
    private function getDB(): PDO
    {
        $db=new PDO('sqlite:'.dirname(__DIR__).'/Database/caro_history.db');
        $db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
        $db->exec("CREATE TABLE IF NOT EXISTS game_history (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            winner TEXT NOT NULL, game_mode TEXT NOT NULL,
            ai_diff TEXT, played_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
        return $db;
    }
 
    private function saveHistory(string $winner): void
    {
        $stmt=$this->getDB()->prepare("INSERT INTO game_history (winner,game_mode,ai_diff) VALUES (?,?,?)");
        $stmt->execute([$winner,$_SESSION['caro_game_mode'],$_SESSION['caro_game_mode']==='ai'?$_SESSION['caro_ai_diff']:null]);
    }
 
    private function fetchHistory(int $limit=50): array
    {
        $stmt=$this->getDB()->prepare("SELECT id,winner,game_mode,ai_diff,played_at FROM game_history ORDER BY id DESC LIMIT ?");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
 
    private function clearHistory(): void { $this->getDB()->exec("DELETE FROM game_history"); }
 
    // ════════════════ HELPERS ════════════════════════
 
    private function jsonHeader(): void { header('Content-Type: application/json; charset=utf-8'); }
    private function json(array $data): string { return json_encode($data, JSON_UNESCAPED_UNICODE); }
}