<?php
/**
 * @file view/cloud.php
 * @ingroup blog
 * @brief Template per la vista cloud
 *
 * Variabili disponibili:
 * - **instance_name**: nome dell'istanza
 * - **items**: array di array con chiavi (name, url, f) (nome tag, url archivio tag, frequenza)
 * - **max_f**: massima frequenza
 *
 * @version 1.0.0
 * @copyright 2012 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
?>
<? namespace Gino\App\Blog; ?>
<section id="blog-cloud-<?= $instance_name ?>">
	<h1><?= _('Tag') ?></h1>
	<? if(count($items)): ?>
		<? foreach($items as $item): ?>
			<a href="<?= $item['url'] ?>" style="font-size:<?= preg_replace("#,#", ".", (1 + 1.2 * $item['f']/$max_f)) ?>em"><?= $item['name'] ?></a> 
		<? endforeach ?>
	<? endif ?>
</section>
