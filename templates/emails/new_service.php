<?php
$header_title = 'Demande de service enregistrée';
ob_start();
?>
<p>Bonjour <strong><?= htmlspecialchars($user['name']) ?></strong>,</p>
<p>Votre demande de service a bien été enregistrée sous le numéro
   <span class="badge">#<?= $object['id'] ?></span>.
</p>
<ul style="line-height:2">
  <li><strong>Service :</strong> <?= htmlspecialchars($object['service_name'] ?? $object['title']) ?></li>
  <li><strong>Date :</strong> <?= date('d/m/Y à H:i') ?></li>
  <li><strong>Statut :</strong> En attente de traitement</li>
</ul>
<p>Vous serez notifié(e) dès que votre demande sera prise en charge.</p>
<?php
$body_content = ob_get_clean();
include __DIR__ . '/base_email.php';
?>