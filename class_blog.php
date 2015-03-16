<?php
/**
 * \file class_blog.php
 * Contiene la definizione ed implementazione della classe blog.
 * 
 * @version 1.0.0
 * @copyright 2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
namespace Gino\App\Blog;

use Gino\Session;

use \Gino\Options;
use \Gino\AdminTable;
use \Gino\View;
use \Gino\Document;
use \Gino\App\Module\ModuleInstance;

/**
 * Caratteristiche, opzioni configurabili da backoffice ed output disponibili per i template e le voci di menu.
 *
 * CARATTERISTICHE    
 *  
 * Modulo di gestione blog, con predisposizione contenuti per ricerca nel sito e newsletter 
 *
 * OPZIONI CONFIGURABILI
 * - numero ultimi post
 * - numero di post per pagina
 * - numero post in vetrina
 * - animazione automatica in vetrina
 * - intervallo animazione automatica in vetrina
 * - numero di post esportabili per la newsletter
 *
 * OUTPUTS
 * - ultimi post
 * - albero post per data
 * - archivio post
 * - vetrina post
 * - tag cloud
 * - dettaglio post
 * - feed RSS
 * - newsletter
 */
require_once('class.BlogEntry.php');

/**
 * @defgroup blog
 * Modulo di gestione di un blog
 *
 * Il modulo contiene anche dei css, javascript e file di configurazione.
 *
 */

/**
 * \ingroup blog
 * Classe per la gestione di un blog
 * 
 * @version 1.0.0
 * @copyright 2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
class blog extends \Gino\Controller {

    /**
     * Numero ultimi post
     */
    private $_last_number;
    
    /**
     * @brief numero di post per pagina
     */
    private $_list_ifp;

    /**
     * Numero post in vetrina
     */
    private $_showcase_number;
    
    /**
     * @brief animazione vetrina start automatico
     */
    private $_showcase_auto_start;

    /**
     * @brief animazione vetrina intervallo animazione
     */
    private $_showcase_auto_interval;

    /**
     * Numero di post proposti per la newsletter
     */
    private $_newsletter_entries_number;

    /**
     * @brief Tabella di opzioni 
     */
    private $_tbl_opt;

    /**
     * Percorso assoluto alla directory contenente le viste 
     */
    protected $_view_dir;

    /**
     * Array che conserva i risultati di ricerca nel sito per evitare duplicazioni per lingue diverse da quella di default
     */
    private static $_site_search_results;

    /**
     * Costruisce un'istanza di tipo blog
     *
     * @param int $mdlId id dell'istanza di tipo blog
     * @return istanza di blog
     */
    function __construct($mdlId) {

        parent::__construct($mdlId);

        $this->_tbl_opt = 'blog_opt';

        $this->_view_dir = dirname(__FILE__).OS.'views';

        $this->_optionsValue = array(
            'last_number'=>3,
        	'list_ifp'=>5,
        	'showcase_number'=>3,
        	'showcase_auto_start'=>0,
            'showcase_auto_interval'=>5000,
            'newsletter_entries_number'=>5
        );

        $this->_last_number = $this->setOption('last_number', array('value'=>$this->_optionsValue['last_number']));
        $this->_list_ifp = $this->setOption('list_ifp', array('value'=>$this->_optionsValue['list_ifp']));
        $this->_showcase_number = $this->setOption('showcase_number', array('value'=>$this->_optionsValue['showcase_number']));
        $this->_showcase_auto_start = $this->setOption('showcase_auto_start', array('value'=>$this->_optionsValue['showcase_auto_start']));
        $this->_showcase_auto_interval = $this->setOption('showcase_auto_interval', array('value'=>$this->_optionsValue['showcase_auto_interval']));
        $this->_newsletter_entries_number = $this->setOption('newsletter_entries_number', array('value'=>$this->_optionsValue['newsletter_entries_number']));

        $res_newsletter = $this->_db->getFieldFromId(TBL_MODULE_APP, 'id', 'name', 'newsletter');
        if($res_newsletter) {
            $newsletter_module = true;
        }
        else {
            $newsletter_module = false;
        }

        $this->_options = new Options($this);
        $this->_optionsLabels = array(
            "last_number"=>array(
                'label'=>_("Numero ultimi post"),
                'value'=>$this->_optionsValue['last_number'],
                'section'=>true, 
                'section_title'=>_('Opzioni vista ultimi post'),
                'section_description'=>null
            ),
            "list_ifp"=>array(
                'label'=>_("Numero di post per pagina"),
                'value'=>$this->_optionsValue['list_ifp'],
                'section'=>true, 
                'section_title'=>_('Opzioni vista archivio'),
                'section_description'=>null
            ),
            "showcase_number"=>array(
                'label'=>_("Numero post in vetrina"),
                'value'=>$this->_optionsValue['showcase_number'],
                'section'=>true, 
                'section_title'=>_('Opzioni vista vetrina'),
                'section_description'=>null
            ),
            "showcase_auto_start"=>array(
                'label'=>_("Animazione automatica"),
                'value'=>$this->_optionsValue['showcase_auto_start']
            ),
            "showcase_auto_interval"=>array(
                'label'=>_("Intervallo animazione automatica (ms)"),
                'value'=>$this->_optionsValue['showcase_auto_start']
            ),
            "newsletter_entries_number"=>array(
                'label'=>_('Numero di post presentati nel modulo newsletter'),
                'value'=>$this->_optionsValue['newsletter_entries_number'],
                'section'=>true, 
                'section_title'=>_('Opzioni newsletter'),
                'section_description'=> $newsletter_module 
                    ? "<p>"._('La classe si interfaccia al modulo newsletter di GINO installato sul sistema')."</p>"
                    : "<p>"._('Il modulo newsletter non è installato')."</p>",
            ),
        );
    }

    /**
     * Restituisce alcune proprietà della classe utili per la generazione di nuove istanze
     *
     * @static
     * @return lista delle proprietà utilizzate per la creazione di istanze di tipo news
     */
    public static function getClassElements() {

        return array(
            "tables"=>array(
                'blog_entry',
                'blog_opt', 
            ),
            "css"=>array(
                'blog.css',
                'mooTree.css'
            ),
            "views" => array(
                'archive.php' => _('Archivio'),
                'cloud.php' => _('Tag cloud'),
            	'feed_rss.php' => _('Feed RSS'),
                'detail.php' => _('Dettaglio post'),
                'last.php' => _('Lista ultimi post'),
                'showcase.php' => _('Vetrina'),
                'tree.php' => _('Albero cronologico post'), 
            	'newsletter.php' => _('Post esportati in newsletter')
            ),
            "folderStructure"=>array (
                CONTENT_DIR.OS.'blog'=> null
            )
        );

    }

    /**
     * Metodo invocato quando viene eliminata un'istanza di tipo blog
     *
     * Si esegue la cancellazione dei dati da db e l'eliminazione di file e directory 
     * 
     * @access public
     * @return bool il risultato dell'operazione
     */
    public function deleteInstance() {

        $this->requirePerm('can_admin');

        /*
         * blog entries
         */
        $query = "SELECT id FROM ".BlogEntry::$table." WHERE instance='$this->_instance'";
        $a = $this->_db->selectquery($query);
        if(sizeof($a)>0) {
            foreach($a as $b) {
                translation::deleteTranslations(BlogEntry::$table, $b['id']);
            }
        }
        
        $result = $this->_db->delete(BlogEntry::$table, "instance=".$this->_instance);
        
        /*
         * delete record and translation from table blog_opt
         */
        $opt_id = $this->_db->getFieldFromId($this->_tbl_opt, "id", "instance", $this->_instance);
        translation::deleteTranslations($this->_tbl_opt, $opt_id);
        
        $result = $this->_db->delete($this->_tbl_opt, "instance=".$this->_instance);
        
        /*
         * delete css files
         */
        $classElements = $this->getClassElements();
        foreach($classElements['css'] as $css) {
            unlink(APP_DIR.OS.$this->_class_name.OS.\Gino\baseFileName($css)."_".$this->_instance_name.".css");
        }
        
    	/* eliminazione views */
        foreach($classElements['views'] as $k => $v) {
            unlink($this->_view_dir.OS.\Gino\baseFileName($k)."_".$this->_instance_name.".php");
        }

        /* eliminazione cartelle contenuti */
        foreach($classElements['folderStructure'] as $fld=>$fldStructure) {
            \Gino\deleteFileDir($fld.OS.$this->_instance_name, true);
        }

        return $result;
    }


    /**
     * @brief Definizione dei metodi pubblici che forniscono un output per il front-end
     *
     * Questo metodo viene letto dal motore di generazione dei layout (metodi non presenti nel file ini) e dal motore di generazione
     * di voci di menu (metodi presenti nel file ini) per presentare una lista di output associati all'istanza di classe.
     *
     * @return array associativo NOME_METODO => array('label' => LABEL, 'permissions' => PERMESSI)
     */
    public static function outputFunctions() {

        $list = array(
            "last" => array("label"=>_("Lista utimi post"), "permissions"=>array()),
            "tree" => array("label"=>_("Albero post per data"), "permissions"=>array()),
        	"detail" => array("label"=>_("Dettaglio post"), "permissions"=>array()),
            "archive" => array("label"=>_("Archivio"), "permissions"=>array()),
            "showcase" => array("label"=>_("Vetrina (più letti)"), "permissions"=>array()),
            "tagcloud" => array("label"=>_("Tag cloud"), "permissions"=>array()),
        );

        return $list;
    }

   /**
     * Front end ultimi post 
     * 
     * @access public
     * @return lista ultimi post
     */
    public function last() {

        $title = _('Ultimi post').' | '.$this->_registry->sysconf->head_title;

        $this->_registry->addCss($this->_class_www."/blog_".$this->_instance_name.".css");
        $this->_registry->addHeadLink(array(
            'rel' => 'alternate',
            'type' => 'application/rss+xml',
            'title' => \Gino\jsVar($title),
            'href' => $this->link($this->_instance_name, 'feedRSS')
        ));

        $where = BlogEntry::setSelectCondition($this);
        $entries = BlogEntry::objects($this, array('where'=>$where, 'order'=>'creation_date DESC', 'limit'=>array(0, $this->_last_number)));

        $view = new View($this->_view_dir);
        $view->setViewTpl('last_'.$this->_instance_name);
        
        $dict = array(
            'instance_name' => $this->_instance_name,
            'locale' => $this->_locale, 
        	'feed_url' => $this->link($this->_instance_name, 'feedRSS'), 
        	'entries' => $entries, 
        	'archive_url' => $this->link($this->_instance_name, 'archive')
        );

        $document = new Document($view->render($dict));
        return $document();
    }

    /**
     * Front end albero post per data
     * 
     * @access public
     * @return albero post per data
     */
    public function tree() {

        $this->_registry->addCss($this->_class_www."/blog_".$this->_instance_name.".css");
        $this->_registry->addCss($this->_class_www."/mooTree_".$this->_instance_name.".css");
        $this->_registry->addJs($this->_class_www."/mooTree-yc.js");

        $where = BlogEntry::setSelectCondition($this);
        $entries = BlogEntry::objects($this, array('where'=>$where, 'order'=>'creation_date DESC'));

        $tree_array = array();

        if(count($entries)) {
            foreach($entries as $entry) {
                $eyear = date('Y', strtotime($entry->creation_date));
                $emonth = date('m', strtotime($entry->creation_date));
                if(!isset($tree_array[$eyear])) {
                    $tree_array[$eyear] = array();
                }
                if(!isset($tree_array[$eyear][$emonth])) {
                    $tree_array[$eyear][$emonth] = array();
                }
                $tree_array[$eyear][$emonth][] = "<a href=\"".$this->link($this->_instance_name, 'detail', array('id'=>$entry->slug))."\">".\Gino\htmlChars($entry->ml('title'))."</a>";
            }
        }

        $view = new View($this->_view_dir);
        $view->setViewTpl('tree_'.$this->_instance_name);
        
        $dict = array(
            'instance_name' => $this->_instance_name, 
            'locale' => $this->_locale, 
        	'tree_array' => $tree_array
        );

        return $view->render($dict);
    }

    /**
     * Front end archivio 
     * 
     * @param \Gino\Http\Request istanza di Gino.Http.Request
     * @return Gino.Http.Response
     */
    public function archive(\Gino\Http\Request $request) {

        $title = _('Archivio blog').' | '.$this->_registry->sysconf->head_title;

        $this->_registry->addCss($this->_class_www."/blog_".$this->_instance_name.".css");
        $this->_registry->title = \Gino\jsVar($title);
        $this->_registry->addHeadLink(array(
            'rel' => 'alternate',
            'type' => 'application/rss+xml',
            'title' => \Gino\jsVar($title),
        	'href' => $this->link($this->_instance_name, 'feedRSS')
        ));
        
        $tag = \Gino\cleanVar($request->GET, 'id', 'string');
    	
        $entries_number = BlogEntry::getCount($this, array('tag'=>$tag, 'published'=>true));
        
        $paginator = \Gino\Loader::load('Paginator', array($entries_number, $this->_list_ifp));
        $limit = $paginator->limitQuery();
        
        if(!$tag) $tag = null;
        $where = BlogEntry::setSelectCondition($this, array('tag'=>$tag));
        
        $entries = BlogEntry::objects($this, array('where'=>$where, 'order'=>'creation_date DESC', 'limit'=>$limit));

        $view = new View($this->_view_dir);
        $view->setViewTpl('archive_'.$this->_instance_name);
        
        $dict = array(
            'instance_name' => $this->_instance_name,
            'locale' => $this->_locale, 
            'entries' => $entries, 
        	'subtitle' => $tag ? sprintf(_("Pubblicati in %s"), \Gino\htmlChars($tag)) : '', 
        	'feed_url' => $this->link($this->_instance_name, 'feedRSS'), 
            'tree' => $this->tree(), 
        	'disqus_shortname' => $this->_registry->sysconf->disqus_shortname, 
        	'tagcloud' => $this->tagcloud(), 
        	'pagination' => $paginator->pagination()
        );

        $document = new Document($view->render($dict));
        return $document();
    }

    /**
     * Front end vetrina post più letti 
     * 
     * @access public
     * @return vetrina
     */
    public function showcase() {

        $this->_registry->addCss($this->_class_www."/blog_".$this->_instance_name.".css");
        $this->_registry->addJs($this->_class_www."/blog.js");

        $where = BlogEntry::setSelectCondition($this);
        
        $entries = BlogEntry::objects($this, array('where'=>$where, 'order'=>'\'num_read\' DESC, creation_date DESC', 'limit'=>array(0, $this->_showcase_number)));

		$view = new View($this->_view_dir);
		$view->setViewTpl('showcase_'.$this->_instance_name);

        $dict = array(
            'instance_name' => $this->_instance_name,
        	'feed_url' => $this->link($this->_instance_name, 'feedRSS'),
            'items' => $entries,
            'autostart' => $this->_showcase_auto_start,
            'autointerval' => $this->_showcase_auto_interval
        );

        return $view->render($dict);
    }

    /**
     * Front end dettaglio post 
     * 
     * @access public
     * @return dettaglio post
     */
    public function detail(\Gino\Http\Request $request) {

        $slug = \Gino\cleanVar($request->GET, 'id', 'string', '');

        $entry = BlogEntry::getFromSlug($slug, $this);

        if(!$entry || !$entry->id || !$entry->published) {
            throw new \Gino\Exception\Exception404();
        }

        $this->_registry->addCss($this->_class_www."/prettify.css");
        $this->_registry->addJs($this->_class_www."/prettify.js");
        $this->_registry->addCss($this->_class_www."/blog_".$this->_instance_name.".css");
        $this->_registry->addJs($this->_class_www."/blog.js");
        $this->_registry->js_load_sharethis = true;

        $title = \Gino\attributeVar($entry->ml('title').' | '.$this->_registry->title);
        $this->_registry->title = $title;
        $this->_registry->description = \Gino\attributeVar(\Gino\cutHtmlText($entry->ml('text'), 155, '...', true, false, true, array()));
        // facebook
        $this->_registry->addMeta(array(
          'property' => 'og:title',
          'content' => $title
        ));
        $this->_registry->addMeta(array(
          'property' => 'og:description',
          'content' => \Gino\cutHtmlText($entry->ml('text'), 297, '...', true, false, true)
        ));
        $this->_registry->addMeta(array(
          'property' => 'og:type',
          'content' => 'article'
        ));
        $this->_registry->addMeta(array(
          'property' => 'og:url',
          'content' => $entry->getAbsoluteUrl()
        ));
        $this->_registry->addMeta(array(
          'property' => 'og:image',
          'content' => $request->root_absolute_url.$entry->imgPath($this)
        ));
        // twitter
        $this->_registry->addMeta(array(
          'name' => 'twitter:card',
          'content' => 'summary'
        ));
        $this->_registry->addMeta(array(
          'name' => 'twitter:title',
          'content' => $title
        ));
        $this->_registry->addMeta(array(
          'name' => 'twitter:description',
          'content' => \Gino\cutHtmlText($entry->ml('text'), 200, '...', true, false, true)
        ));
        $this->_registry->addMeta(array(
          'name' => 'twitter:url',
          'content' => $entry->getAbsoluteUrl()
        ));

        $view = new View($this->_view_dir);
        $view->setViewTpl('detail_'.$this->_instance_name);

        $share = \Gino\shareAll(
        	array('facebook_large', 'twitter_large', 'linkedin_large', 'googleplus_large', 'pinterest_large', 'evernote_large', 'email_large'), 
        	$request->root_absolute_url.$this->link($this->_instance_name, 'detail', array('id'=>$entry->slug), '', array('abs'=>true)), 
        	\Gino\htmlChars($entry->ml('title')));

        $dict = array(
            'instance_name' => $this->_instance_name,
            'entry' => $entry,
        	'image' => $entry->image ? $entry->imgPath($this) : null, 
            'related_contents_list' => $this->relatedContentsList($entry),
            'tree' => $this->tree(),
        	'share' => $share, 
        	'disqus_shortname' => $this->_registry->sysconf->disqus_shortname, 
        	'tagcloud' => $this->tagcloud()
        );
        
        $session = \Gino\Session::instance();
        
        if(!$session->user_id) {
            $entry->num_read = $entry->num_read + 1;
            $entry->save();
        }

        $document = new Document($view->render($dict));
        return $document();
    }
    
	/**
     * Front end tag cloud 
     * 
     * @return tag cloud
     */
    public function tagcloud() {

        \Gino\Loader::import('class', '\Gino\GTag');
    	
        $this->_registry->addCss($this->_class_www."/blog_".$this->_instance_name.".css");

        $tags_freq = \Gino\GTag::getTagsHistogram();
        
		$items = array();
        $max_f = 0;
        foreach($tags_freq as $tid=>$f) {
            
        	$items[] = array(
                "name"=>\Gino\htmlChars($tid),
                "url"=>$this->link($this->_instance_name, 'archive', array('id'=>$tid)),
                "f"=>$f
            );
            $max_f = max($f, $max_f);
        }

        $view = new View($this->_view_dir);
        $view->setViewTpl('cloud_'.$this->_instance_name);
        
        $dict = array(
            'instance_name' => $this->_instance_name,
        	'items' => $items, 
        	'max_f' => $max_f
        );
        
        return $view->render($dict);
    }
    
	/**
     * @brief Lista di contenuti correlati per tag
     * 
     * @param \Gino\App\Blog\BlogEntry $item oggetto @ref Gino.App.Blog.BlogEntry
     * @return html, lista contenuti correlati
     */
    public function relatedContentsList($item) {
    	
        \Gino\Loader::import('class', '\Gino\GTag');
        
    	$related_contents = \Gino\GTag::getRelatedContents('BlogEntry', $item->id);
        if(count($related_contents)) {
            $view = new View(null, 'related_contents_list');
            return $view->render(array('related_contents' => $related_contents));
        }
        else return '';
    }

    /**
     * @brief Interfaccia di amministrazione del modulo
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.Response interfaccia di back office
     */
    public function manageDoc(\Gino\Http\Request $request) {

        $this->requirePerm(array('can_admin', 'can_publish'));

        $method = 'manageDoc';

        $link_frontend = "<a href=\"".$this->_home."?evt[$this->_instance_name-$method]&block=frontend\">"._("Frontend")."</a>";
        $link_options = "<a href=\"".$this->_home."?evt[$this->_instance_name-$method]&block=options\">"._("Opzioni")."</a>";
        $link_dft = "<a href=\"".$this->_home."?evt[".$this->_instance_name."-$method]\">"._("Post")."</a>";

        $sel_link = $link_dft;

        $id = \Gino\cleanVar($request->GET, 'id', 'int', '');
        $block = \Gino\cleanVar($request->GET, 'block', 'string', '');
        
        if($block == 'frontend' && $this->userHasPerm('can_admin')) {
            $backend = $this->manageFrontend();
            $sel_link = $link_frontend;
        }
        elseif($block == 'options' && $this->userHasPerm('can_admin')) {
            $backend = $this->manageOptions();
            $sel_link = $link_options;
        }
        else {
            $backend = $this->manageEntry($request);
        }
        
    	// groups privileges
        if($this->userHasPerm('can_admin')) {
            $links_array = array($link_frontend, $link_options, $link_dft);
        }
        else {
            $links_array = array($link_dft);
        }
        
    	if(is_a($backend, '\Gino\Http\Response')) {
            return $backend;
        }
        
        $module = ModuleInstance::getFromName($this->_instance_name);

        $dict = array(
          'title' => \Gino\htmlChars($module->label),
          'links' => $links_array,
          'selected_link' => $sel_link,
          'content' => $backend
        );

        $view = new view();
        $view->setViewTpl('tab');

        $document = new Document($view->render($dict));
        return $document();
    }

    /**
     * Interfaccia di amministrazione dei post 
     * 
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return Gino.Http.Redirect oppure html, interfaccia di back office
     */
    private function manageEntry(\Gino\Http\Request $request) {
        
        $registry = \Gino\Registry::instance();
        $registry->addJs($this->_class_www.'/blog.js');
        $registry->addJs($this->_class_www.'/MooComplete.js');
        $registry->addCss($this->_class_www.'/MooComplete.css');
        
        $edit = \Gino\cleanVar($request->GET, 'edit', 'int', '');

		$name_onblur = !$edit 
		? "onblur=\"var date = new Date(); $('slug').value = date.getFullYear() + (date.getMonth().toString().length == 1 ? '0' + date.getMonth().toString() : date.getMonth().toString()) + (date.getDate().toString().length == 1 ? '0' + date.getDate().toString() : date.getDate().toString()) + '-' + $(this).value.slugify()\""
		: '';

        if(!$this->userHasPerm('can_publish')) {
            $remove_fields = array('author', 'published', 'num_read');
        }
        else {
            $remove_fields = array('author', 'num_read');
        }
        
        $admin_table = \Gino\Loader::load('AdminTable', array($this, array()));

        if($this->_registry->sysconf->disqus_shortname) {
            $list_description = _('Il servizio DISQUS per i commenti è abilitato con il seguente shortname: ').$this->_registry->sysconf->disqus_shortname;
        }
        else {
            $list_description = _('Attenzione! Il servizio DISQUS per i commenti non è abilitato. Per abilitarlo creare un account e inserire lo shortname fornito nelle "Impostazioni di sistema" di gino.');
        }

        $buffer = $admin_table->backOffice(
            'BlogEntry', 
            array(
                'list_display' => array('id', 'creation_date', 'title', 'slug', 'tags', 'published'),
                'list_title'=>_("Elenco post"), 
                'list_description' => $list_description,
                'filter_fields'=>array('title', 'tags', 'published'),
                ),
            array(
                'removeFields' => $remove_fields, 
            	'addCell' => array(
            		'creation_date'=>array(
            			'name'=>'date', 
            			'field'=>"<input type=\"hidden\" id=\"date\" name=\"date\" value=\"".date("Y-m-d")."\" />"
            		)
            	)
                ), 
            array(
                'title' => array(
                    'js' => $name_onblur
                ),
                'slug' => array(
                    'id' => 'slug'
                ),
                'tags' => array(
                    'id' => 'tags'
                ),
                'text' => array(
                    'widget'=>'editor', 
                    'notes'=>true, 
                    'img_preview'=>true, 
                ),
                'image' => array(
                    'preview'=>true
                )
            )
        );

        return $buffer;
    }

    /**
     * Metodo per la definizione di parametri da utilizzare per il modulo "Ricerca nel sito"
     *
     * Il modulo "Ricerca nel sito" di Gino base chiama questo metodo per ottenere informazioni riguardo alla tabella, campi, pesi etc...
     * per effettuare la ricerca dei contenuti.
     *
     * @access public
     * @return array[string]mixed array associativo contenente i parametri per la ricerca
     */
    public function searchSite() {
        
        if(!$this->_registry->sysconf->multi_language or ($this->_registry->session->lng == $this->_registry->session->lngDft)) {
            return array(
                "table"=>BlogEntry::$tbl_entry, 
                "selected_fields"=>array("id", "slug", "creation_date", array("highlight"=>true, "field"=>"title"), array("highlight"=>true, "field"=>"text")), 
                "required_clauses"=>array("instance"=>$this->_instance, "published"=>1), 
                "weight_clauses"=>array("title"=>array("weight"=>5), 'tags'=>array('weight'=>3), "text"=>array("weight"=>1))
            );
        }
        else {
            return array(
                "table"=>TBL_TRANSLATION, 
                "selected_fields"=>array("tbl_id_value", "field", array("highlight"=>true, "field"=>"text")), 
                "required_clauses"=>array("1"=>"1' AND tbl_id_value IN (SELECT id FROM ".BlogEntry::$tbl_entry." WHERE instance='".$this->_instance."' AND published='1') AND 1='1", "tbl"=>BlogEntry::$tbl_entry),
                "weight_clauses"=>array("text"=>array("weight"=>1))
            );
        }
    }

    /**
     * Definisce la presentazione del singolo item trovato a seguito di ricerca (modulo "Ricerca nel sito")
     *
     * @param mixed array array[string]string array associativo contenente i risultati della ricerca
     * @access public
     * @return void
     */
    public function searchSiteResult($results) {

        // not default language
        if(isset($results['tbl_id_value'])) {

            if(!isset(self::$_site_search_results)) {
                self::$_site_search_results = array();
            }

            $obj = new BlogEntry($results['tbl_id_value'], $this);

            $buffer = "<dt>".\Gino\dbDatetimeToDate($obj->creation_date, "/")." <a href=\"".$this->link($this->_instance_name, 'detail', array('id'=>$obj->slug))."\">";
            $buffer .= $results['field'] == 'title' ? \Gino\htmlChars($results['title']) : \Gino\htmlChars($obj->ml('title'));
            $buffer .= "</a></dt>";

            if($results['field'] == 'text') {
                $buffer .= "<dd class=\"search-text-result\">...".\Gino\htmlChars($results['text'])."...</dd>";
            }
            else {
                $buffer .= "<dd class=\"search-text-result\">".\Gino\htmlChars(cutHtmlText($obj->ml('text'), 120, '...', false, false, false, array('endingPosition'=>'in')))."</dd>";
            }
        }
        else {
            $obj = new BlogEntry($results['id'], $this);

            $buffer = "<dt>".\Gino\dbDatetimeToDate($results['creation_date'], "/")." <a href=\"".$this->link($this->_instance_name, 'detail', array('id'=>$results['slug']))."\">";
            $buffer .= $results['title'] ? \Gino\htmlChars($results['title']) : \Gino\htmlChars($obj->ml('title'));
            $buffer .= "</a></dt>";

            if($results['text']) {
                $buffer .= "<dd class=\"search-text-result\">...".\Gino\htmlChars($results['text'])."...</dd>";
            }
            else {
                $buffer .= "<dd class=\"search-text-result\">".\Gino\htmlChars(cutHtmlText($obj->ml('text'), 120, '...', false, false, false, array('endingPosition'=>'in')))."</dd>";
            }
        }
        
        return $buffer;

    }

    /**
     * Adattatore per la classe newsletter 
     * 
     * @access public
     * @return array di elementi esportabili nella newsletter
     */
    public function systemNewsletterList() {
        
        $entries = BlogEntry::objects($this, array(
        	'where'=>BlogEntry::setSelectCondition($this), 
        	'order'=>'creation_date DESC', 
        	'limit'=>array(0, $this->_newsletter_entries_number)
        ));

        $items = array();
        foreach($entries as $entry) {
            $items[] = array(
                _('id') => $entry->id,
                _('titolo') => \Gino\htmlChars($entry->ml('title')),
                _('pubblicato') => $entry->published ? _('si') : _('no'),
                _('data creazione') => \Gino\dbDateToDate($entry->creation_date),
            ); 
        }

        return $items;
    }

    /**
     * Contenuto di un post quanto inserito in una newsletter 
     * 
     * @param int $id identificativo del post
     * @return contenuto post
     */
    public function systemNewsletterRender($id) {

        $entry = new BlogEntry($id, $this);
        
        $view = new View($this->_view_dir, 'newsletter_'.$this->_instance_name);
        $dict = array(
            'item' => $entry,
        );

        return $view->render($dict);
    }

    /**
     * Genera un feed RSS standard che presenta gli ultimi 50 post pubblicati
     *
     * @param \Gino\Http\Request $request istanza di Gino.Http.Request
     * @return \Gino\Http\Response, feed RSS
     */
    public function feedRSS(\Gino\Http\Request $request) {

        $title_site = $this->_registry->sysconf->head_title;
        $module = new ModuleInstance($this->_instance);
        $title = $module->label.' | '.$title_site;
        $description = $module->description;

        $items = BlogEntry::objects($this, array(
        	'where' => BlogEntry::setSelectCondition($this), 
        	'order'=>'creation_date DESC', 
        	'limit'=>array(0, 50)
        ));

        $view = new View($this->_view_dir, 'feed_rss_'.$this->_instance_name);
        $dict = array(
            'title' => $title,
            'description' => $description,
            'request' => $request,
            'items' => $items
        );

        $response = new \Gino\Http\Response($view->render($dict));
        $response->setContentType('text/xml');
        return $response;
    }


}
