<?php
$header_title = 'Réclamation enregistrée';
ob_start();
?>
<p>Bonjour <strong><?= htmlspecialchars($user['name']) ?></strong>,</p>
<p>Votre réclamation a bien été enregistrée sous le numéro
   <span class="badge">#<?= $object['id'] ?></span>.
</p>
<ul style="line-height:2">
  <li><strong>Objet :</strong> <?= htmlspecialchars($object['title'] ?? $object['subject']) ?></li>
  <li><strong>Date :</strong> <?= date('d/m/Y à H:i') ?></li>
  <li><strong>Statut :</strong> En cours de traitement</li>
</ul>
<p>Vous serez notifié(e) de toute mise à jour.</p>
<?php
$body_content = ob_get_clean();
include __DIR__ . '/base_email.php';
?>