<?php
/**
* @file showcase.php
* @brief Template per la vista vetrina post
*
* Variabili disponibili:
* - **instance_name**: string, nome istanza modulo
* - **items**: array, oggetti di tipo @ref Gino.App.Blog.BlogEntry
* - **feed_url**: string, url ai feed RSS
* - **autostart**: bool, opzione autostart
* - **autointerval**: int, intervallo animazione (ms)
*
* @version 1.0.0
* @copyright 2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
* @authors Marco Guidotti guidottim@gmail.com
* @authors abidibo abidibo@gmail.com
*/
?>
<? namespace Gino\App\Blog; ?>
<? //@cond no-doxygen ?>
<section id="blog-showcase-<?= $instance_name ?>">
    <h1>
        <?= _('Blog') ?>
        <? if($feed_url): ?>
            <a href="<?= $feed_url ?>" class="fa fa-rss"></a>
        <? endif ?>
    </h1>
    <div id="blog-showcase-wrapper-<?= $instance_name ?>">
        <? $ctrls = array(); ?>
        <? $tot = count($items); ?>
        <? $i = 0; ?>
        <? foreach($items as $n): ?>
            <div class='blog-showcase-item' style='display: block;z-index:<?= $tot - $i ?>' id="news_<?= $i ?>">
                <article>
                    <h1><a href="<?= $n->getUrl() ?>"><?= \Gino\htmlChars($n->ml('title')) ?></a></h1>
                    <?= \Gino\htmlChars(\Gino\cutHtmlText($n->ml('text'), 150, '...', false, false, true, array('endingPosition'=>'in'))) ?>
                </article>
            </div>
            <? $ctrls[] = "<div id=\"sym_$i\" class=\"blog-showcase-sym\" onclick=\"blogslider.set($i)\"><span></span></div>"; ?>
            <? $i++; ?>
        <? endforeach ?>
    </div>
    <table>
        <tr>
        <? foreach($ctrls as $ctrl): ?>
            <td><?= $ctrl ?></td>
        <? endforeach ?>
        </tr>
    </table>
    <script type="text/javascript">
        var blogslider;
        window.addEvent('load', function() {
            blogslider = new BlogSlider('blog-showcase-wrapper-<?= $instance_name ?>', 'sym_', {auto_start: <?= $autostart ? 'true' : 'false' ?>, auto_interval: <?= $autointerval ?>});
        });
    </script>
</section>
<? // @endcond ?>
