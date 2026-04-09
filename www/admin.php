<?php
require_once __DIR__ . '/includes/auth.php';

requireAdmin();

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action  = $_POST['action']  ?? '';
    $user_id = (int)($_POST['user_id'] ?? 0);

    if ($user_id > 0) {
        if ($action === 'disable') {
            // On enregistre la date choisie dans le calendrier
            $disabled_at = $_POST['disabled_at'] ?? date('Y-m-d H:i:s');
            // Convertir depuis le format datetime-local (Y-m-dTH:i) vers MySQL (Y-m-d H:i:s)
            $disabled_at = str_replace('T', ' ', $disabled_at) . ':00';
            $stmt = $db->prepare("UPDATE users SET disabled_at = ? WHERE id = ?");
            $stmt->execute([$disabled_at, $user_id]);
        } elseif ($action === 'enable') {
            // Réactiver = remettre disabled_at à NULL
            $stmt = $db->prepare("UPDATE users SET disabled_at = NULL WHERE id = ?");
            $stmt->execute([$user_id]);
        }
    }
    header("Location: /admin.php");
    exit;
}

$users = $db->query("SELECT id, username, email, first_name, last_name, disabled_at, created_at FROM users ORDER BY id ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Administration — Mon Formulaire</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  :root {
    --bg: #f9f9f8; --white: #ffffff;
    --border: #e8e6e1; --border2: #d4d0c8;
    --text: #1a1a1a; --muted: #6b6b6b; --light: #9b9b9b;
    --accent: #7c6aff; --accent-light: #f0eeff;
    --danger: #dc2626; --danger-light: #fef2f2;
    --success: #16a34a; --success-light: #f0fdf4;
    --radius: 10px;
    --shadow: 0 1px 3px rgba(0,0,0,0.06), 0 4px 16px rgba(0,0,0,0.04);
  }
  body { background: var(--bg); font-family: 'Inter', sans-serif; color: var(--text); min-height: 100vh; }
  header { height: 52px; background: var(--white); border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; padding: 0 24px; position: sticky; top: 0; z-index: 50; }
  .logo { font-size: 15px; font-weight: 600; color: var(--text); text-decoration: none; }
  .badge-admin { font-size: 11px; font-weight: 600; padding: 3px 8px; background: var(--accent-light); color: var(--accent); border-radius: 20px; border: 1px solid rgba(124,106,255,0.2); margin-left: 10px; }
  .page { max-width: 900px; margin: 0 auto; padding: 40px 24px; }
  .page-header { margin-bottom: 28px; }
  .page-header h1 { font-size: 22px; font-weight: 600; letter-spacing: -0.3px; margin-bottom: 4px; }
  .page-header p  { font-size: 13.5px; color: var(--muted); }
  .table-card { background: var(--white); border: 1px solid var(--border); border-radius: 14px; box-shadow: var(--shadow); overflow: hidden; }
  table { width: 100%; border-collapse: collapse; }
  thead th { padding: 12px 16px; text-align: left; font-size: 11.5px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: var(--muted); background: var(--bg); border-bottom: 1px solid var(--border); }
  tbody tr { border-bottom: 1px solid var(--border); transition: background 0.1s; }
  tbody tr:last-child { border-bottom: none; }
  tbody tr:hover { background: #faf9f8; }
  td { padding: 14px 16px; font-size: 13.5px; vertical-align: middle; }
  .user-info { display: flex; align-items: center; gap: 10px; }
  .avatar-sm { width: 34px; height: 34px; border-radius: 50%; background: var(--accent-light); color: var(--accent); display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 600; flex-shrink: 0; }
  .avatar-sm.inactive { background: #f3f4f6; color: var(--light); }
  .user-name  { font-weight: 500; font-size: 14px; }
  .user-email { font-size: 12px; color: var(--muted); margin-top: 1px; }
  .badge { display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 500; }
  .badge-active   { background: var(--success-light); color: var(--success); }
  .badge-inactive { background: var(--danger-light);  color: var(--danger); }
  .badge-dot { width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
  .action-form { display: flex; align-items: center; gap: 8px; }
  .date-input { padding: 6px 10px; border: 1px solid var(--border2); border-radius: 8px; font-family: 'Inter', sans-serif; font-size: 12px; color: var(--text); outline: none; transition: border-color 0.15s; }
  .date-input:focus { border-color: var(--accent); }
  .btn-sm { padding: 6px 14px; border: none; border-radius: 8px; font-family: 'Inter', sans-serif; font-size: 12.5px; font-weight: 500; cursor: pointer; transition: background 0.15s; white-space: nowrap; }
  .btn-disable { background: var(--danger-light); color: var(--danger); }
  .btn-disable:hover { background: #fecaca; }
  .btn-enable  { background: var(--success-light); color: var(--success); }
  .btn-enable:hover  { background: #bbf7d0; }
  .disabled-since { font-size: 12px; color: var(--danger); margin-top: 2px; }
</style>
</head>
<body>

<header>
  <div style="display:flex;align-items:center">
    <a class="logo" href="/">Mon Formulaire</a>
    <span class="badge-admin">Admin</span>
  </div>
  <a href="/login.php" style="font-size:13px;color:var(--muted);text-decoration:none;">← Retour</a>
</header>

<div class="page">
  <div class="page-header">
    <h1>Gestion des utilisateurs</h1>
    <p><?= count($users) ?> compte<?= count($users) > 1 ? 's' : '' ?> enregistré<?= count($users) > 1 ? 's' : '' ?></p>
  </div>

  <div class="table-card">
    <table>
      <thead>
        <tr>
          <th>Utilisateur</th>
          <th>Statut</th>
          <th>Inscrit le</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $u):
          $initials  = strtoupper(mb_substr($u['first_name'] ?: $u['username'], 0, 1) . mb_substr($u['last_name'] ?? '', 0, 1));
          $isActive  = empty($u['disabled_at']);
        ?>
        <tr>
          <td>
            <div class="user-info">
              <div class="avatar-sm <?= $isActive ? '' : 'inactive' ?>"><?= htmlspecialchars($initials) ?></div>
              <div>
                <div class="user-name"><?= sanitize($u['username']) ?></div>
                <div class="user-email"><?= sanitize($u['email']) ?></div>
              </div>
            </div>
          </td>

          <td>
            <?php if ($isActive): ?>
              <span class="badge badge-active"><span class="badge-dot"></span>Actif</span>
            <?php else: ?>
              <div>
                <span class="badge badge-inactive"><span class="badge-dot"></span>Désactivé</span>
                <div class="disabled-since">depuis le <?= (new DateTime($u['disabled_at']))->format('d/m/Y à H:i') ?></div>
              </div>
            <?php endif; ?>
          </td>

          <td style="font-size:13px;color:var(--muted);">
            <?= (new DateTime($u['created_at']))->format('d/m/Y') ?>
          </td>

          <td>
            <?php if ($isActive): ?>
              <form method="POST" action="/admin.php" class="action-form">
                <input type="hidden" name="action"  value="disable">
                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                <input class="date-input" type="datetime-local" name="disabled_at"
                       value="<?= date('Y-m-d\TH:i') ?>"
                       title="Date et heure de désactivation">
                <button class="btn-sm btn-disable" type="submit">Désactiver</button>
              </form>
            <?php else: ?>
              <form method="POST" action="/admin.php" class="action-form">
                <input type="hidden" name="action"  value="enable">
                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                <button class="btn-sm btn-enable" type="submit">Réactiver</button>
              </form>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

</body>
</html>
