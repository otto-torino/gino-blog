<?php
/**
 * @file view/tree.php
 * @ingroup blog
 * @brief Template per la vista albero post per data
 *
 * Variabili disponibili:
 * - **instance_name**: nome dell'istanza
 * - **tree_array**: array associativo dell'albero di post array($year=>array($month=>array($entry1, $entry2, ...)))
 *
 * @version 1.0.0
 * @copyright 2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
?>
<? namespace Gino\App\Blog; ?>
<section id="blog-tree-<?= $instance_name ?>">
<h1><?= _('Archivio') ?></h1>
<? if(count($tree_array)): ?>
	<ul>
	<? foreach($tree_array as $year => $month_array): ?>
		<li><?= $year ?><ul>
		<? foreach($month_array as $month => $entries): ?>
			<li><?= $month ?> (<?= count($entries) ?>)<ul>
				<? foreach($entries as $entry): ?>
					<li><?= $entry ?></li>
				<? endforeach ?>
			</ul></li>
		<? endforeach ?>
		</ul></li>	
	<? endforeach ?>
	</ul>
	<script type="text/javascript">
		window.addEvent('domready', function() {
			var mt_instance = new mooTree($$('#<?= $section_id ?> ul')[0], {collapse:true, expand_top: true});
		});
	</script>
<? endif ?>
</section>
