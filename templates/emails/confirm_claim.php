<?php
$header_title = 'Réclamation confirmée';
ob_start();
?>
<p>Bonjour <strong><?= htmlspecialchars($user['name']) ?></strong>,</p>
<p>Votre réclamation <span class="badge">#<?= $object['id'] ?></span>
   a été <strong>confirmée</strong> et est en cours de traitement.
</p>
<ul style="line-height:2">
  <li><strong>Objet :</strong> <?= htmlspecialchars($object['title'] ?? $object['subject']) ?></li>
  <li><strong>Statut :</strong> Confirmée</li>
</ul>
<p>Notre équipe va prendre en charge votre réclamation dans les plus brefs délais.</p>
<?php
$body_content = ob_get_clean();
include __DIR__ . '/base_email.php';
?>