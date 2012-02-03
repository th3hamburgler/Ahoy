<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * NOTICE OF LICENSE
 * 
 * Licensed under the Academic Free License version 3.0
 * 
 * This source file is subject to the Academic Free License (AFL 3.0) that is
 * bundled with this package in the files license_afl.txt / license_afl.rst.
 * It is also available through the world wide web at this URL:
 * http://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to obtain it
 * through the world wide web, please send an email to
 * licensing@ellislab.com so we can send you a copy immediately.
 *
 * @package		CodeIgniter
 * @author		EllisLab Dev Team
 * @copyright	Copyright (c) 2008 - 2011, EllisLab, Inc. (http://ellislab.com/)
 * @license		http://opensource.org/licenses/AFL-3.0 Academic Free License (AFL 3.0)
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 */

/*
 * Required functions:
 *
 * - element()
 * - humanize()
 */

class Ahoy {

	private $items;	// array of menu items

   /**
	* Constructor - Sets Ahoy Preferences
	*
	* The constructor can be passed an array of config values
	*/
	public function __construct($items=array())
	{	
		$this->_load_required_helpers();
		
		if ($items)
			$this->initialize($items);
		
		
		log_message('debug', "Ahoy Class Initialized");
	}

   /**
	* Checks to see if required functions are loaded
	* and loads their helper if not
	*
	* @access	private
	* @param	void
	* @return	void
	*/
	private function _load_required_helpers()
	{
		$CI=false;		
		$helpers = array(
			'element'	=> 'array',
			'humanize'	=> 'inflector',
			'site_url'	=> 'url',
		);		
		foreach($helpers as $function => $helper) {
			if(!function_exists($function)) {
				if(!$CI)
					$CI =& get_instance();
					$CI->load->helper($helper);
			}
		}		
	}

   /**
	* Initialize the menu
	*
	* @access	public
	* @param	array
	* @return	object
	*/
	public function initialize($items=array())
	{
		$this->add_menu_items($items);
		
		return $this;
	}

   /**
	* Generate the menus html as a list
	*
	* @access	public
	* @param	array
	* @return	string
	*/
	public function generate($attr=array())
	{
		$items = array();		
		foreach($this->items as $controller => $item)
		{
			$items[] = $item->generate();
		}
		return html_element('ul', implode("\n", $items), $attr);
	}

   /**
	* Generate the menu html with twitter bootstrap tab defaults
	*
	* @access	public
	* @param	array
	* @return	string
	*/
	public function tabs($attr=array())
	{
		$attr = array_merge(array('class' => 'nav nav-tabs'), $attr);
		return $this->generate($attr);
	}

   /**
	* Generate the menu html with twitter bootstrap pill defaults
	*
	* @access	public
	* @param	array
	* @return	string
	*/
	public function pills($attr=array())
	{
		$attr = array_merge(array('class' => 'nav nav-pills'), $attr);
		return $this->generate($attr);
	}

   /**
	* Generate the menu html with twitter bootstrap stacked tab defaults
	*
	* @access	public
	* @param	array
	* @return	string
	*/
	public function stacked_tabs($attr=array())
	{
		$attr = array_merge(array('class' => 'nav nav-tabs nav-stacked'), $attr);
		return $this->generate($attr);
	}

   /**
	* Generate the menu html with twitter bootstrap stacked pill defaults
	*
	* @access	public
	* @param	array
	* @return	string
	*/
	public function stacked_pills($attr=array())
	{
		$attr = array_merge(array('class' => 'nav nav-pills nav-stacked'), $attr);
		return $this->generate($attr);
	}

   /**
	* Generate the menu html with twitter bootstrap nav list defaults
	*
	* @access	public
	* @param	array
	* @return	string
	*/
	public function nav_list($attr=array())
	{
		$attr = array_merge(array('class' => 'nav nav-list'), $attr);
		return $this->generate($attr);
	}
	
   /**
	* Generate the menu html with twitter bootstrap navbar defaults
	*
	* @access	public
	* @param	array
	* @return	string
	*/
	public function navbar($attr=array())
	{
		$attr = array_merge(array('class' => 'nav'), $attr);
		return $this->generate($attr);
	}

   /**
	* Generate the menu html with twitter bootstrap breadcrumb defaults
	* This includes a divider span between items suffixed to each anchor
	*
	* @access	public
	* @param	array
	* @param	string
	* @return	string
	*/
	public function breadcrumb($attr=array(), $divider=' <span class="divider">/</span>')
	{
		$attr = array_merge(array('class' => 'breadcrumb'), $attr);
		$n=0;
		foreach($this->items as $item)
		{
			$n++;
			if($n!=count($this->items))
				$item->anchor_suffix = $divider;
		}
		return $this->generate($attr);
	}

   /**
	* Add the menu items - preparing them as we add
	*
	* @access	public
	* @param	array
	* @return	object
	*/
	public function add_menu_items($items)
	{
		foreach($items as $controller => $spec)
			// prep menu item and store in object
			$this->items[$controller] = $this->add_menu_item($controller, $spec);

		return $this;
	}

   /**
	* Add the menu items - preparing them as we add
	*
	* @access	public
	* @param	string
	* @param	array
	* @return	object
	*/
	public function add_menu_item($controller, $spec)
	{
		return new Ahoy_Item($controller, $spec);
	}
}

class Ahoy_Item {
	
	public $active	= false;
	public $anchor_prefix;
	public $anchor_suffix;
	public $class	= array();
	public $icon;
	public $label;
	public $uri;
	public $dropdown;

	static $uri_depth=2;

   /**
	* Constructor - Creates a new menu item
	*
	* The constructor can be passed a controller and array of config values
	*/
	public function __construct($controller=null, $spec=array())
	{
		if ($controller OR $spec)
			$this->initialize($controller, $spec);
	}

   /**
	* Initialize the menu item
	*
	* @access	public
	* @param	array
	* @return	object
	*/
	public function initialize($controller, $spec)
	{
		// if not already, turn item spec into an array
		if(!is_array($spec)) 
			$spec = array('label' => $spec);
		
		$this->set_uri($controller);
		
		$this->set_label($controller, $spec);
		
		$this->set_icon($spec);
		
		$this->initilize_dropdown($spec);
		
		$this->check_active();
		
		return $this;
	}

   /**
	* Generate the menus html as a list
	*
	* @access	public
	* @param	array
	* @return	string
	*/
	public function generate()
	{
		if($this->is_dropdown()) {
			return $this->generate_dropdown();
		} else if($this->uri === FALSE) {
			return $this->generate_header();
		} else {
			return $this->generate_single();
		}
	}

   /**
	* Generate this item as a single list item
	*
	* @access	public
	* @param	array
	* @return	string
	*/
	public function generate_single()
	{
		$this->get_icon();
		
		$attr = array(
			'class'	=> implode('', $this->class),
		);
		
		if(substr($this->uri, 0, 1) == '#')
			$href = $this->uri;
		else if(substr($this->uri, 0, 4) == 'http')
			$href = $this->uri;
		else
			$href = site_url($this->uri);
		
		$anchor = html_element('a', $this->label, array('href' => $href));
		
		return html_element('li', $this->anchor_prefix.$anchor.$this->anchor_suffix, $attr);
	}
	
   /**
	* Generate this item as a single list item
	*
	* @access	public
	* @param	array
	* @return	string
	*/
	public function generate_header()
	{
		$this->class[] = 'nav-header';
		
		$attr = array(
			'class'	=> implode('', $this->class),
		);
		return html_element('li', $this->label, $attr);
	}

   /**
	* Generate this item as a dropdown list menu
	*
	* @access	public
	* @param	array
	* @return	string
	*/
	public function generate_dropdown()
	{
		$attr = array(
			'class' 		=>'dropdown-toggle',
			'data-toggle'	=> 'dropdown',
			'href'			=> '#',
		);		
		$anchor = html_element('a', $this->label.'<b class="caret"></b>', $attr);
		
		$dropdown = $this->dropdown->generate(array('class' => 'dropdown-menu'));
		
		return html_element('li', $anchor.$dropdown, array('class' => 'dropdown'));
	}


   /**
	* adds the icon element to the label if set
	*
	* @access	public
	* @return	void
	*/
	public function set_icon($attr=array())
	{
		$this->icon = element('icon', $attr);
	}
   /**
	* adds the icon element to the label if set
	*
	* @access	private
	* @return	void
	*/
	private function get_icon()
	{
		if(!$this->icon)
			return;
			
		$attr = array('class'=>'icon-'.$this->icon);
		
		if($this->active)
			$attr['class'] .= ' icon-white';
		
		$this->label = html_element('i', '', $attr)."\n".$this->label;
	}

   /**
	* Sets the url property
	*
	* @access	private
	* @param	void
	* @return	boolean
	*/
	private function is_dropdown()
	{
		if($this->dropdown)
			return true;
		return false;
	}

   /**
	* Sets the url property
	*
	* @access	private
	* @param	string
	* @param	array
	* @return	void
	*/
	private function set_uri($controller)
	{
		if(is_int($controller))
			$this->uri = FALSE;
		else
			$this->uri = $controller;
	}

   /**
	* Sets the label property
	* Humanize the controller string if not set
	*
	* @access	private
	* @param	string
	* @param	array
	* @return	void
	*/
	private function set_label($controller, $spec)
	{
		$this->label = element('label', $spec, humanize($controller));
	}

   /**
	* Looks in the spec array to see if
	* this contains a list of child items
	*
	* @access	private
	* @param	array
	* @return	void
	*/
	private function initilize_dropdown($spec)
	{
		// look for items element - means this is a sub menu
		if(element('items', $spec))
			$this->dropdown = new Ahoy(element('items', $spec));
		// loo to see if the spec is in fact a list of items
		else if(!element('label', $spec))
			$this->dropdown = new Ahoy($spec);
	}

   /**
	* Check if item is active and adds active class if true
	*
	* @access	private
	* @return	void
	*/
	private function check_active()
	{
		$CI =& get_instance();
		
		$uri = array_slice(explode('/', $this->uri), 0, static::$uri_depth);
		
		$current_uri = array_slice($CI->uri->segment_array(), 0, static::$uri_depth);
		
		if($current_uri == $uri)
		{
			$this->active = true;
			$this->class[] = 'active';
		}
	}

}
if ( ! function_exists('html_element'))
{
   /**
	* HTML Element
	*
	* Generates a generic HTML element
	*
	* @author    Jim Wardlaw
	* @access    public
	* @param     string
	* @param     string
	* @param     array
	* @return    string
	* @version   1.1
	*/ 
	function html_element($tag, $contents, $attributes = NULL)
    {
    	// Were any attributes submitted?  If so generate a string
		if (is_array($attributes))
		{
			$atts = '';
			foreach ($attributes as $key => $val)
			{
				$atts .= ' ' . $key . '="' . $val . '"';
			}
			$attributes = $atts;
		}
		
    	return "<".$tag.$attributes.">".$contents."</".$tag.">";
    }
}