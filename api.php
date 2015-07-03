<?php

/**
 * Metabox
 *
 * @package Boots
 * @subpackage Metabox
 * @version 1.0.7
 * @license GPLv2
 *
 * Boots - The missing WordPress framework. http://wpboots.com
 *
 * Copyright (C) <2014>  <M. Kamal Khan>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 */

// see http://codex.wordpress.org/Function_Reference/add_meta_box

class Boots_Metabox {
    private $Boots;
    private $Settings;
    private $dir;
    private $url;

    private $Boxes = array();

    private $nonce;
    private $nonce_field;

    private $id = null;
    private $section = null;

    public function __construct($Boots, $Args, $dir, $url)
    {
        $this->Boots = $Boots;
        $this->Settings = $Args;
        $this->dir = $dir;
        $this->url = $url;

        $this->nonce = $Args['APP_ID'] . '_metabox';
        $this->nonce_field = $this->nonce . '_nonce';

        if(!has_action('admin_enqueue_scripts', array(&$this, 'scripts_and_styles')))
            add_action('admin_enqueue_scripts', array(&$this, 'scripts_and_styles'));
        if(!has_action('save_post', array(&$this, 'save')))
            add_action('save_post', array(&$this, 'save'), PHP_INT_MAX);
    }

    public function scripts_and_styles($hook)
    {
        global $post;
        $Hooked = array();
        if($hook == 'post-new.php' || $hook == 'post.php')
        {
            foreach($this->Boxes as $id => $Box)
            {
                $Post_types = is_array($Box['post_type'])
                ? $Box['post_type']
                : array($Box['post_type']);

                foreach($Post_types as $post_type)
                {
                    if($post->post_type == $post_type && !in_array($post_type, $Hooked))
                    {
                        $this->Boots->Form->styles();
                        $this->Boots->Form->scripts();

                        $this->Boots->Enqueue
                        ->raw_style('boots_metabox')
                            ->source($this->url . '/css/boots_metabox.min.css')
                            ->requires('boots_form')
                            ->done();

                        do_action('boots_metabox_print_styles-' . $id, 'boots_metabox');
                        do_action('boots_metabox_print_scripts-' . $id, 'boots_form');

                        add_filter('postbox_classes_' . $post_type . '_' . $id, array(&$this, 'add_class'));
                        $Hooked[] = $post_type;
                    }
                }
            }
        }
    }

    public function add_class($Classes)
    {
        $Classes[] = 'boots-metabox-container';
        return $Classes;
    }

    private function option($key, $val = false)
    {
        if(!$this->id)
        {
            // notice of incorrect id.
            return false;
        }

        $id = $this->id;

        if(!isset($this->Boxes[$id]))
        {
            // notice that metabox does not exist.
            return false;
        }
        if($val === false)
        {
            return $this->Boxes[$id][$key];
        }
        $this->Boxes[$id][$key] = $val;
    }

    public function in($post_type)
    {
        $T = is_array($post_type) ? $post_type : array($post_type);
        $Types = array_merge((array) $this->option('post_type'), $T);
        $this->option('post_type', $Types);

        return $this;
    }

    public function context($context)
    {
        $this->option('context', $context);

        return $this;
    }

    public function priority($priority)
    {
        $this->option('priority', $priority);

        return $this;
    }

    public function section($name)
    {
        $S = array($name => array());
        $Sections = array_merge_recursive((array) $this->option('sections'), $S);
        $this->option('sections', $Sections);

        $this->section = $name;
        return $this;
    }

    public function add($field_str, $Args = array(), $Extras = null)
    {
        if(!$this->id)
        {
            // notice that the metabox does not exist.
            return false;
        }

        if(!$this->section)
        {
            $this->section('Section');
        }

        $id = $this->id;
        $section = $this->section;

        if(is_array($field_str))
        {
            $Extras = $Args;
        }

        $group = false;
        $Requires = array();
        if($Extras !== null)
        {
            if(!is_array($Extras))
            {
                $group = $Extras;
            }
            else
            {
                $group = isset($Extras['group'])
                ? $Extras['group'] : false;
                $Requires = isset($Extras['requires'])
                ? $Extras['requires'] : array();
            }
        }

        //
        $Keys = array();

        if(is_array($field_str))
        {
            foreach($field_str as $Field)
            {
                if(!is_array($Field) || (!isset($Field['_'])))
                {
                    if($group !== false)
                    {
                        $this->Boxes[$id]['sections'][$section][$group][] = array(
                            'type' => '_',
                            'args' => $Field,
                            'requires' => $Requires
                        );
                    }
                    else
                    {
                        $this->Boxes[$id]['sections'][$section][] = array(
                            'type' => '_',
                            'args' => $Field,
                            'requires' => $Requires
                        );
                    }
                    if(isset($Field['name']))
                    $Keys[] = $Field['name'];
                }
                else
                {
                    $f = $Field['_'];
                    unset($Field['_']);
                    $args = $Field;
                    if($group !== false)
                    {
                        $this->Boxes[$id]['sections'][$section][$group][] = array(
                            'type' => $f,
                            'args' => $args,
                            'requires' => $Requires
                        );
                    }
                    else
                    {
                        $this->Boxes[$id]['sections'][$section][] = array(
                            'type' => $f,
                            'args' => $args,
                            'requires' => $Requires
                        );
                    }
                    if(isset($args['name']))
                    $Keys[] = $args['name'];
                }
            }
        }
        else
        {
            if($group !== false)
            {
                $this->Boxes[$id]['sections'][$section][$group][] = array(
                    'type' => $field_str,
                    'args' => $Args,
                    'requires' => $Requires
                );
            }
            else
            {
                $this->Boxes[$id]['sections'][$section][] = array(
                    'type' => $field_str,
                    'args' => $Args,
                    'requires' => $Requires
                );
            }
            if(isset($Args['name']))
            $Keys[] = $Args['name'];
        }

        $this->Boxes[$id]['keys'] = isset($this->Boxes[$id]['keys'])
        ? array_merge($this->Boxes[$id]['keys'], $Keys)
        : $Keys;

        return $this;
    }

    // post_type: 'post', 'page', 'dashboard', 'link', 'attachment' or 'custom_post_type'
    // context: 'normal', 'advanced', or 'side'
    // priority: 'high', 'core', 'default' or 'low'
    public function create($id, $title)
    {
        $this->Boxes[$id] = array(
            'title' => $title,
            'post_type' => array(),
            'context' => 'normal',
            'priority' => 'default',
            'sections' => array()
        );

        $this->id = $id;
        return $this;
    }

    public function done($Args = array())
    {
        if(!$this->id)
        {
            // notice that the metabox does not exist.
            return false;
        }

        if(isset($Args['_']))
        {
            // notice that _ can not be a key in the $Args
            return false;
        }

        $id = $this->id;

        $Box = $this->Boxes[$id];

        $this->add('hidden', array(
            'id' => 'boots_metabox_' . $id,
            'name' => 'boots_metabox[' . $id . ']',
            'value' => implode(':', $Box['keys'])
        ));

        foreach($Box['post_type'] as $post_type)
        {
            add_meta_box(
                $id,
                $Box['title'],
                array(& $this, 'callback'),
                $post_type,
                $Box['context'],
                $Box['priority'],
                array_merge(array('_' => $id), $Args)
            );
        }

        $this->id = null;
        $this->section = null;
        return $this;
    }

    public function callback($Post, $Args)
    {
        $Args = $Args['args'];
        $id = $Args['_'];
        unset($Args['_']);

        if(!isset($this->Boxes[$id]))
        {
            // notice that the metabox does not exist
            return false;
        }

        // add nonce
        wp_nonce_field($this->nonce, $this->nonce_field);

        // spit out the metabox content
        $Sections = $this->Boxes[$id]['sections'];
        include $this->dir . '/metabox.php';
    }

    public function save($post_id)
    {
        $nonce_field = $this->nonce_field;

        if(!isset($_POST[$nonce_field]))
        {
            return $post_id;
        }

        $nonce = $_POST[$nonce_field];

        if(!wp_verify_nonce($nonce, $this->nonce))
        {
            return $post_id;
        }

        if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        {
            return $post_id;
        }

        if($_POST['post_type'] == 'page')
        {
            if(!current_user_can('edit_page', $post_id))
            {
                return $post_id;
            }
        }
        else if(!current_user_can('edit_post', $post_id))
        {
            return $post_id;
        }

        $Post = array();
        foreach($_POST as $k => $v)
        {
            $Key = array();
            if(preg_match('/^boots_metabox_(.*?)$/', $k, $Key))
            {
                $Post[$Key[1]] = $v;
            }
        }

        if(
            isset($Post['boots_metabox'])
            && is_array($Post['boots_metabox'])
        )
        {
            $Metaboxes = $Post['boots_metabox'];
            unset($Post['boots_metabox']);
            foreach($Metaboxes as $metabox => $keys)
            {
                $PostData = array();
                $Fields = explode(':', $keys);
                $Filter = array();
                foreach($Fields as $field)
                {
                    $Filter[$field] = isset($Post[$field]) ? $Post[$field] : false;
                }
                $PostData = array_merge($PostData, apply_filters('boots_metabox_save_meta-' . $metabox, $Filter));
            }
        }

        if(!is_array($PostData)) return $post_id;

        foreach($PostData as $term => $value)
        {
			$this->Boots->Database
				->term($term)
				->id($post_id)
			    ->update($value);
        }
    }
}
