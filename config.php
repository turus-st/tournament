<?php
declare(strict_types=1);
const APP_NAME = 'U12 Tournament 2026';
const DB_FILE = __DIR__ . '/data/tournament.sqlite';
const ADMIN_SESSION = 'u12_admin';
// In production set the ADMIN_PASSWORD environment variable on the server.
function admin_password(): string { return getenv('ADMIN_PASSWORD') ?: 'ChangeMe-U12-2026'; }
function db(): PDO {
  static $pdo=null;
  if ($pdo) return $pdo;
  if (!is_dir(dirname(DB_FILE))) mkdir(dirname(DB_FILE), 0775, true);
  $pdo=new PDO('sqlite:'.DB_FILE, null, null, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
  $pdo->exec('PRAGMA foreign_keys=ON; PRAGMA journal_mode=WAL;');
  initialize($pdo);
  return $pdo;
}
function initialize(PDO $db): void {
  $db->exec("CREATE TABLE IF NOT EXISTS games(id INTEGER PRIMARY KEY, game_no TEXT UNIQUE, play_date TEXT, play_time TEXT, field TEXT, phase TEXT, group_code TEXT, team1 TEXT, team2 TEXT, score1 INTEGER, score2 INTEGER, status TEXT NOT NULL DEFAULT 'scheduled' CHECK(status IN ('scheduled','live','completed')), updated_at TEXT);
  CREATE TABLE IF NOT EXISTS pitchers(id INTEGER PRIMARY KEY AUTOINCREMENT, game_id INTEGER NOT NULL, name TEXT NOT NULL, team TEXT NOT NULL, rl REAL NOT NULL DEFAULT 0, runs_allowed INTEGER NOT NULL DEFAULT 0, earned_runs INTEGER NOT NULL DEFAULT 0, FOREIGN KEY(game_id) REFERENCES games(id) ON DELETE CASCADE);
  CREATE TABLE IF NOT EXISTS batters(id INTEGER PRIMARY KEY AUTOINCREMENT, game_id INTEGER NOT NULL, name TEXT NOT NULL, team TEXT NOT NULL, h INTEGER NOT NULL DEFAULT 0, tb INTEGER NOT NULL DEFAULT 0, ab INTEGER NOT NULL DEFAULT 0, rbi INTEGER NOT NULL DEFAULT 0, FOREIGN KEY(game_id) REFERENCES games(id) ON DELETE CASCADE);
  CREATE TABLE IF NOT EXISTS mvp(game_id INTEGER PRIMARY KEY, name TEXT NOT NULL DEFAULT '', team TEXT NOT NULL DEFAULT '', notes TEXT NOT NULL DEFAULT '', FOREIGN KEY(game_id) REFERENCES games(id) ON DELETE CASCADE);");
  $n=(int)$db->query('SELECT COUNT(*) FROM games')->fetchColumn();
  if ($n===0) seed_games($db);
}
function seed_games(PDO $db): void {
  $games=json_decode(file_get_contents(__DIR__.'/seed_games.json'),true);
  $q=$db->prepare('INSERT INTO games(game_no,play_date,play_time,field,phase,group_code,team1,team2) VALUES(?,?,?,?,?,?,?,?)');
  foreach($games as $g) $q->execute([$g['game'],$g['date'],$g['time'],$g['field'],$g['phase'],$g['group'],$g['team1'],$g['team2']]);
}
function h(?string $s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function is_admin(): bool { return !empty($_SESSION[ADMIN_SESSION]); }
function require_admin(): void { if(!is_admin()){http_response_code(403);header('Content-Type: application/json');echo json_encode(['error'=>'Forbidden']);exit;} }
function csrf(): string { if(empty($_SESSION['csrf'])) $_SESSION['csrf']=bin2hex(random_bytes(24)); return $_SESSION['csrf']; }
function verify_csrf(): void { if(!hash_equals($_SESSION['csrf']??'', $_POST['csrf']??($_SERVER['HTTP_X_CSRF_TOKEN']??''))){http_response_code(419);exit('Invalid CSRF token');} }
function groups(): array { return ['A'=>['DUCKS','ÉRD INDIANS HUNGARY','DIVING DUCKS','LONDON MONARCHS','RONCHI NBP','KIEV Baseball'],'B'=>['TIGERS','BEAVERS METROSTARS VIENNA','LONDON ARCHERS','FUN & PLAY','RANGERS REDIPUGLIA','RADVILIŠKIS']]; }
function standings(string $group): array {
  $teams=groups()[$group]; $s=[]; foreach($teams as $t)$s[$t]=['team'=>$t,'g'=>0,'w'=>0,'l'=>0,'rf'=>0,'ra'=>0,'diff'=>0,'pct'=>0,'tb_pct'=>0,'tb_diff'=>0];
  $q=db()->prepare("SELECT * FROM games WHERE group_code=? AND status='completed' AND score1 IS NOT NULL AND score2 IS NOT NULL");$q->execute([$group]);$games=$q->fetchAll();
  foreach($games as $g){ if($g['score1']===$g['score2'])continue;$a=&$s[$g['team1']];$b=&$s[$g['team2']];$a['g']++;$b['g']++;$a['rf']+=(int)$g['score1'];$a['ra']+=(int)$g['score2'];$b['rf']+=(int)$g['score2'];$b['ra']+=(int)$g['score1'];if($g['score1']>$g['score2']){$a['w']++;$b['l']++;}else{$b['w']++;$a['l']++;}unset($a,$b);}
  foreach($s as &$x){$x['diff']=$x['rf']-$x['ra'];$x['pct']=$x['g']?$x['w']/$x['g']:0;}unset($x);
  // Tie-break mini-table among teams with the same overall win percentage.
  $buckets=[];foreach($s as $t=>$x)$buckets[number_format($x['pct'],6)][]=$t;
  foreach($buckets as $tied){if(count($tied)<2)continue;$mini=[];foreach($tied as $t)$mini[$t]=['g'=>0,'w'=>0,'rf'=>0,'ra'=>0];foreach($games as $g){if(!in_array($g['team1'],$tied,true)||!in_array($g['team2'],$tied,true)||$g['score1']===$g['score2'])continue;$mini[$g['team1']]['g']++;$mini[$g['team2']]['g']++;$mini[$g['team1']]['rf']+=(int)$g['score1'];$mini[$g['team1']]['ra']+=(int)$g['score2'];$mini[$g['team2']]['rf']+=(int)$g['score2'];$mini[$g['team2']]['ra']+=(int)$g['score1'];$mini[$g['score1']>$g['score2']?$g['team1']:$g['team2']]['w']++;}foreach($tied as $t){$s[$t]['tb_pct']=$mini[$t]['g']?$mini[$t]['w']/$mini[$t]['g']:0;$s[$t]['tb_diff']=$mini[$t]['rf']-$mini[$t]['ra'];}}
  $r=array_values($s);usort($r,fn($a,$b)=>($b['pct']<=>$a['pct'])?:($b['tb_pct']<=>$a['tb_pct'])?:($b['tb_diff']<=>$a['tb_diff'])?:($b['diff']<=>$a['diff'])?:($b['rf']<=>$a['rf'])?:strcmp($a['team'],$b['team']));return $r;
}
function update_finalists(): void {
  $a=standings('A');$b=standings('B');$map=['31'=>[5,5],'32'=>[4,4],'33'=>[3,3],'34'=>[2,2],'35'=>[1,1],'36'=>[0,0]];
  $q=db()->prepare("UPDATE games SET team1=?,team2=? WHERE game_no=? AND status='scheduled'");foreach($map as $gameNo=>$pos)$q->execute([$a[$pos[0]]['team']??'', $b[$pos[1]]['team']??'', $gameNo]);
}
?>