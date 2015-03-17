<?php
/**
 * @file view/last.php
 * @ingroup blog
 * @brief Template per la vista ultimi post
 *
 * Variabili disponibili:
 * - **instance_name**: nome dell'istanza
 * - **locale**: oggetto Locale
 * - **feed_url**: url feed rss
 * - **entries**: array di oggetti di tipo BlogEntry
 * - **archive_url**: url archivio completo
 *
 * Nota: la funzione cutHtmlText è definita mel seguente modo:
 *       function cutHtmlText($html, $length, $ending, $strip_tags, $cut_words, $cut_images, $options=null)
 *
 * @version 1.0.0
 * @copyright 2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
?>
<? namespace Gino\App\Blog; ?>
<section id="blog-last-<?= $instance_name ?>">
<section>
	<header class="clearfix">
		<h1 class="left"><?= ucfirst($locale->get('blog')) ?></h1>
		<a href="<?= $feed_url ?>" class="fa fa-rss pull-right"></a>
	</header>
	<? if(count($entries)): ?>
		<? foreach($entries as $entry): ?>
        <article>
            <header>
                <h1><?= $entry->title ?></h1>
                <div class="blog-post-info">
                    <div class="pull-left">
                        <span class="fa fa-calendar pull-left"></span> <time pubdate><?= \Gino\dbDatetimeToDate($entry->creation_date, '/') ?></time><br />
                    </div>
                    <div class="pull-right">
                        <span class="fa fa-tag"></span> <?= $entry->linkedTags() ?><br />
                    </div>
                    <div class="clearfix"></div>
                </div>
            </header>
            <?= \Gino\cutHtmlText($entry->text, '200', '...'. false, false, false, null) ?>
        </article>
		<? endforeach ?>
		<p class="archive"><a href="<?= $archive_url ?>"><?= $locale->get('archive') ?></a></p>
	<? else: ?>
		<p><?= $locale->get('no_results') ?></p>
	<? endif ?>
</section>
