<?php
/**
* @file newsletter.php
* @brief Template per la visualizzazione dei post all'interno di newsletter
*
* Variabili disponibili:
* - **item**: \Gino\App\Blog\BlogEntry, istanza di @ref Gino.App.Blog.BlogEntry
*
* @version 1.0.0
* @copyright 2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
* @authors Marco Guidotti guidottim@gmail.com
* @authors abidibo abidibo@gmail.com
*/
?>
<? namespace Gino\App\Blog; ?>
<? //@cond no-doxygen ?>
<section>
    <h1><?= \Gino\htmlChars($item->ml('title')) ?></h1>
    <?= \Gino\htmlChars($item->ml('text')) ?>
 </section>
<? // @endcond ?>
