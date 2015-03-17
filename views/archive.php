<?php
/**
 * @file view/archive.php
 * @ingroup blog
 * @brief Template per la vista archivio
 *
 * Variabili disponibili:
 * - **instance_name**: nome dell'istanza
 * - **locale**: oggetto Locale
 * - **subtitle**: sottotitolo (se si tratta di una lista per tag, specifica quale tag)
 * - **feed_url**: url feed rss
 * - **entries**: array di oggetti BlogEntry
 * - **pagination**: riassunto paginazione (es. 1-5 di 10) e navigazione della paginazione
 *
 * @version 1.0.0
 * @copyright 2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
?>
<? namespace Gino\App\Blog; ?>
<section id="blog-archive-<?= $instance_name ?>" class="container blog">
    <div class="row">
        <div class='col-xs-12 col-md-2'>
            <aside>
                <p><a href="index.php"><img class="hidden-xs hidden-sm img-responsive" src="graphics/logo_Otto.jpg" alt="Otto" /></a></p>
            </aside>
        </div>
        <div class="col-md-7">
            <section>
                <header>
                    <h1 class="left"><?= ucfirst($locale->get('blog')) ?></h1>
                    <a href="<?= $feed_url ?>" class="fa fa-2x fa-rss pull-right feed"></a>
                    <div class="null"></div>
                    <? if($subtitle): ?>
                        <h2><?= $subtitle ?></h2>
                    <? endif ?>
                </header>
                <? if(count($entries)): ?>
                    <? foreach($entries as $entry): ?>
                        <article>
                            <header>
                                <h1><a href="<?php echo $entry->getUrl() ?>"><?= $entry->ml('title') ?></a></h1>
                                <div class="blog-post-info">
                                    <p><span class="fa fa-calendar pull-left"></span> <time pubdate><?= \Gino\dbDatetimeToDate($entry->creation_date, '/') ?></time> 
                                    <span class="pipe">|</span> 
                                    <span class="fa fa-tag"></span> <?= $entry->linkedTags() ?>
                                    <? if($disqus_shortname and $entry->enable_comments): ?>
                                        <span class="pipe">|</span> 
                                        <span class="fa fa-comment"></span> <a href="<?php echo $entry->getAbsoluteUrl() ?>#disqus_thread"><?php echo _('commenti') ?></a>
                                    <?php endif ?>
                                </div>
                            </header>
                            <?= \Gino\cutHtmlText(\Gino\htmlChars($entry->ml('text')), 420, "<a href=\"".$entry->getUrl()."\" class=\"btn btn-primary\">".$locale->get('go_to_detail')." &raquo;</a>", false, false, true, null) ?>
                        </article>
                        <hr />
                    <? endforeach ?>
                    <?= $pagination ?>
                <? else: ?>
                    <p><?= $locale->get('no_results') ?></p>
                <? endif ?>
            </section>

        </div>
        <div class='col-md-3'>
            <div class="hidden-sm hidden-xs">
                <?= $tree ?>
            </div>
            <?= $tagcloud ?>
        </div>
    </div>
<?php if($disqus_shortname): ?>
    <script type="text/javascript">
    /* * * CONFIGURATION VARIABLES: EDIT BEFORE PASTING INTO YOUR WEBPAGE * * */
    var disqus_shortname = '<?php echo $disqus_shortname ?>'; // required: replace example with your forum shortname

    /* * * DON'T EDIT BELOW THIS LINE * * */
    (function () {
        var s = document.createElement('script'); s.async = true;
        s.type = 'text/javascript';
        s.src = '//' + disqus_shortname + '.disqus.com/count.js';
        (document.getElementsByTagName('HEAD')[0] || document.getElementsByTagName('BODY')[0]).appendChild(s);
    }());
    </script>
<?php endif ?>
