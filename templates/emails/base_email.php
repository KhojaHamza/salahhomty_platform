<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <style>
    body { font-family: Arial, sans-serif; background:#f4f4f4; margin:0; }
    .container { max-width:600px; margin:30px auto; background:#fff; border-radius:8px; overflow:hidden; }
    .header { background:#1a3c5e; color:#fff; padding:24px; text-align:center; }
    .header h2 { margin:0; font-size:20px; }
    .body { padding:32px; color:#333; line-height:1.7; }
    .badge { background:#eaf3de; color:#3b6d11; padding:3px 10px; border-radius:4px; font-size:13px; }
    .btn-cancel { display:inline-block; margin-top:20px; padding:12px 28px;
                  background:#e74c3c; color:#fff; text-decoration:none;
                  border-radius:6px; font-weight:bold; }
    .deadline { color:#e74c3c; font-weight:bold; }
    .footer { background:#f9f9f9; padding:16px; text-align:center; font-size:12px; color:#888; }
  </style>
</head>
<body>
<div class="container">
  <div class="header">
    <h2><?= $header_title ?></h2>
  </div>
  <div class="body">

    <?= $body_content ?>

    <?php if (!empty($cancellation_url)): ?>
    <hr style="margin:24px 0; border:none; border-top:1px solid #eee">
    <p>Vous pouvez annuler cette demande dans un délai de <strong>2 heures</strong> :</p>
    <p class="deadline">Expire le : <?= $cancellation_deadline ?></p>
    <a href="<?= htmlspecialchars($cancellation_url) ?>" class="btn-cancel">
      Annuler ma demande
    </a>
    <?php endif; ?>

  </div>
  <div class="footer">
    Email automatique — Plateforme Salahhomty. Merci de ne pas répondre.
  </div>
</div>
</body>
</html>