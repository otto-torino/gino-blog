<?php
/**
 * @file view/detail.php
 * @ingroup blog
 * @brief Template per la vista dettaglio post
 *
 * Variabili disponibili:
 * - **instance_name**: nome dell'istanza
 * - **entry**: oggetto BlogEntry
 * - **image**: percorso dell'immagine
 * - **share**
 * - **disqus_shortname**
 * - **related_contents_list**
 * - **tree**
 * - **tagcloud**
 *
 * @version 1.0.0
 * @copyright 2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
?>
<? namespace Gino\App\Blog; ?>
<section id="blog-detail-<?= $instance_name ?>" class="container blog">
    <div class="row">
        <div class='col-xs-12 col-md-2'>
            <aside>
                <p><a href="index.php"><img class="hidden-xs hidden-sm img-responsive" src="graphics/logo_Otto.jpg" alt="Otto" /></a><? $user = new \Gino\App\Auth\User($entry->author) ?><img class="img-responsive" style="width: 165px;" src="contents/auth/<?= $user->photo ?>" alt="<?= $user->firstname ?>"/></p>
            <?= $share ?>
            </aside>
        </div>
        <div class="col-md-7">
            <article>
                <header>
                    <h1><?= $entry->title ?></h1>
                    <div class="blog-post-info">
						<p><span class="fa fa-calendar pull-left"></span> <time pubdate><?= \Gino\dbDatetimeToDate($entry->creation_date, '/') ?></time> <span class="pipe">|</span> <span class="fa fa-tag"></span> <?= $entry->linkedTags() ?> <span class="pipe">|</span> <span class="fa fa-eye"></span> <?= $entry->read ?></p>
                        <? if($disqus_shortname and $entry->enable_comments): ?>
                            <p><span class="fa fa-comment"></span> <a href="<?php echo $entry->getAbsoluteUrl() ?>#disqus_thread"><?php echo _('commenti') ?></a></p>
                        <?php endif ?>
                    </div>
                </header>
                <?php  if($image): ?>
                	<img class="img-responsive" src="<?= $image ?>" />
                <? endif ?>
                <?= $entry->text ?>
                <? if($disqus_shortname and $entry->enable_comments): ?>
                    <aside class=comments>
                         <div id="disqus_thread"></div>
                            <script type="text/javascript">
                                /* * * CONFIGURATION VARIABLES: EDIT BEFORE PASTING INTO YOUR WEBPAGE * * */
                                var disqus_shortname = '<?= $disqus_shortname ?>'; // required: replace example with your forum shortname

                                /* * * DON'T EDIT BELOW THIS LINE * * */
                                (function() {
                                    var dsq = document.createElement('script'); dsq.type = 'text/javascript'; dsq.async = true;
                                    dsq.src = '//' + disqus_shortname + '.disqus.com/embed.js';
                                    (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);
                                })();
                            </script>
                            <noscript>Please enable JavaScript to view the <a href="http://disqus.com/?ref_noscript">comments powered by Disqus.</a></noscript>
                            <a href="http://disqus.com" class="dsq-brlink">comments powered by <span class="logo-disqus">Disqus</span></a>
                    </aside>
                <? endif ?>
            </article>
        </div>
        <div class='col-md-3'>
            <div class="hidden-sm hidden-xs">
                <?= $tree ?>
            </div>
            <?= $tagcloud ?>
        </div>
    </div>
</section>
<?php if($entry->enable_comments and $disqus_shortname): ?>
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
