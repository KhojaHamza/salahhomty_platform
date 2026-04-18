<?php
$header_title = 'Annulation confirmée';
ob_start();
?>
<p>Bonjour <strong><?= htmlspecialchars($user['name']) ?></strong>,</p>
<p>Votre demande <span class="badge">#<?= $object['id'] ?></span>
   a bien été <strong>annulée</strong>.
</p>
<p>Si vous pensez que c'est une erreur, vous pouvez soumettre une nouvelle demande
   depuis votre espace citoyen.</p>
<?php
$body_content = ob_get_clean();
include __DIR__ . '/base_email.php';
?>