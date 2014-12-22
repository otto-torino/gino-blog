<?php
/**
 * \file class.BlogEntry.php
 * Contiene la definizione ed implementazione della classe blogEntry.
 * 
 * @version 1.0.0
 * @copyright 2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
namespace Gino\App\Blog;

use Gino\Http\Request;

/**
 * \ingroup blog
 * Classe tipo model che rappresenta una post del blog.
 *
 * @version 1.0.0
 * @copyright 2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
class BlogEntry extends \Gino\Model {

	protected static $_extension_img = array('jpg', 'jpeg', 'png');
	public static $table = 'blog_entry';

	/**
	 * Costruttore
	 * 
	 * @param integer $id valore ID del record
	 * @param object $instance istanza del controller
	 */
	function __construct($id, $instance) {

		$this->_controller = $instance;
		$this->_tbl_data = self::$table;

		$this->_fields_label = array(
			'author'=>_('Autore'),
			'creation_date'=>_('Data creazione'),
			'last_edit_date'=>_('Data ultima modifica'),
			'title'=>_("Titolo"),
			'slug'=>array(_("Slug"), _('utilizzato per creare un permalink alla risorsa')),
			'image'=>_('Immagine'),
			'text'=>_('Testo'),
			'tags'=>_('Tag'),
			'enable_comments'=>_('abilita commenti'),
			'published'=>_('Pubblicato'),
			'num_read'=>_('Visualizzazioni'),
		);

		parent::__construct($id);

		$this->_model_label = $this->id ? $this->title : '';
	}

	/**
	 * Rappresentazione testuale del modello 
	 * 
	 * @return string
	 */
	function __toString() {
		
		return (string) $this->_model_label;
	}

	/**
	 * Sovrascrive la struttura di default
	 * 
	 * @see propertyObject::structure()
	 * @param integer $id
	 * @return array
	 */
	public function structure($id) {
		
		$structure = parent::structure($id);

		$structure['slug'] = new \Gino\SlugField(array(
            'name'=>'slug',
            'model'=>$this,
            'required'=>true,
			'autofill' => array('date', 'title')
        ));
        
		$structure['published'] = new \Gino\BooleanField(array(
			'name'=>'published', 
			'model'=>$this,
			'required'=>true,
			'label'=>$this->_fields_label['published'], 
			'enum'=>array(1 => _('si'), 0 => _('no')), 
			'default'=>0,
			'value'=>$this->published, 
			'table'=>$this->_tbl_data 
		));

		$structure['enable_comments'] = new \Gino\BooleanField(array(
			'name'=>'enable_comments', 
			'model'=>$this,
			'required'=>true,
			'label'=>$this->_fields_label['enable_comments'], 
			'enum'=>array(1 => _('si'), 0 => _('no')), 
			'default'=>0, 
			'value'=>$this->enable_comments, 
			'table'=>$this->_tbl_data 
		));

		$structure['creation_date'] = new \Gino\DatetimeField(array(
			'name'=>'creation_date', 
			'model'=>$this,
			'required'=>true,
			'label'=>$this->_fields_label['creation_date'], 
			'auto_now'=>false, 
			'auto_now_add'=>true, 
			'value'=>$this->creation_date 
		));

		$structure['last_edit_date'] = new \Gino\DatetimeField(array(
			'name'=>'last_edit_date', 
			'model'=>$this,
			'required'=>true,
			'label'=>$this->_fields_label['last_edit_date'], 
			'auto_now'=>true, 
			'auto_now_add'=>true, 
			'value'=>$this->last_edit_date 
		));

		$base_path = $this->_controller->getBaseAbsPath();

		$structure['image'] = new \Gino\ImageField(array(
			'name'=>'image', 
			'model'=>$this,
			'value'=>$this->image, 
			'label'=>$this->_fields_label['image'], 
			'lenght'=>100, 
			'extensions'=>self::$_extension_img, 
			'path'=>$base_path, 
			'resize'=>false
		));
		
		$structure['tags'] = new \Gino\TagField(array(
            'name' => 'tags',
            'model' => $this,
            'model_controller_class' => 'blog',
            'model_controller_instance' => $this->_controller->getInstance()
        ));

		return $structure;

	}

    /**
     * @brief Elenco tag con link all'elenco post correlati
     *
     * @param string $separator separatore dei tag (default ', ')
     * @return elenco tag con link
     */
    public function linkedTags($separator = ', ') {
        
    	$linked_tags = array();
        foreach(explode(',', $this->tags) as $tag) {
            $linked_tags[] = "<a href=\"".$this->_registry->router->link($this->_controller->getInstanceName(), 'archive', array('id' => $tag))."\">".$tag."</a>";
        }

        return implode($separator, $linked_tags);
    }

    /**
     * @brief Url dettaglio entry
     *
     * @return url dettaglio
     */
    public function getUrl() {
        return $this->_registry->router->link($this->_controller->getInstanceName(), 'detail', array('id' => $this->slug));
    }

    /**
     * @brief Url assoluto dettaglio entry
     *
     * @return url assoluto dettaglio
     */
    public function getAbsoluteUrl() {
        
    	$request = Request::instance();
    	return $request->root_absolute_url.$this->_registry->router->link($this->_controller->getInstanceName(), 'detail', array('id' => $this->slug));
    }

    /**
     * @brief Url assoluto immagine autore
     *
     * @return url assoluto immagine
     */
    public function getAbsoluteAuthorImageUrl() {
        
    	$request = Request::instance();
    	$user = new \Gino\App\Auth\User($this->author);
        if ($user->photo) {
            return $request->root_absolute_url.'contents/auth/'.$user->photo;
        }
        else {
            return null;
        }
    }
    
    /**
     * Imposta la condizione di una query
     * 
     * @param object $controller
     * @param array $options
     *   array associativo di opzioni
     *   - @b published (boolean): indica se selezionare post pubblicati (default true)
     *   - @b tag (string): nome di un tag
     * @return string
     */
    public static function setSelectCondition($controller, $options=null) {
    	
    	$published = \Gino\gOpt('published', $options, true);
		$tag = \Gino\gOpt('tag', $options, null);
		
    	$where = array("instance='".$controller->getInstance()."'");
    	
    	if($published) {
			$where[] = "published='1'";
		}
		if($tag) {
			$where[] = "tags REGEXP '".$tag."'";
		}
		$where = implode(' AND ', $where);
		
		return $where;
    }

	/**
	 * Restituisce il numero di oggetti BlogEntry selezionati 
	 * 
	 * @param blog $controller istanza del controller 
	 * @param array $options array associativo di opzioni 
	 * @return numero di post
	 */
	public static function getCount($controller, $options = null) {

		$res = 0;

		$published = \Gino\gOpt('published', $options, true);
		$tag = \Gino\gOpt('tag', $options, null);

		$db = \Gino\Db::instance();
		$selection = 'COUNT(id) AS tot';
		$table = self::$table;
		$where_arr = array("instance='".$controller->getInstance()."'");
		if($published) {
			$where_arr[] = "published='1'";
		}
		if($tag) {
			$where_arr[] = "tags REGEXP '".$tag."'";
		}
		$where = implode(' AND ', $where_arr);

		$rows = $db->select($selection, $table, $where);
		if($rows and count($rows)) {
			$res = $rows[0]['tot'];
		}

		return $res;

	}
	
	/**
	 * Path relativo dell'immagine associata 
	 * 
	 * @param object $controller istanza del controller
	 * @return path relativo dell'immagine
	 */
	public function imgPath($controller) {

		return $controller->getBasePath().'/'.$this->image;

	}
}

?>
