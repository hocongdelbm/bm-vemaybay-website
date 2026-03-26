<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

/**
 * @package ExtNode
 * node the tree view. no need to add a root node,a invisible root node will be added to the
 * tree by default.
 * predefined properties for a node are  id, label, target and href. label is required property.
 * set the target and href property for cases where target is an iframe.
 */
class ExtNode
{
    // predefined node properties.
    // this is the only required property for a node.
    public $_label;
    public $_href;
    public $id;
    // ad-hoc collection of node properties
    public $_properties = array();
    // collection of parmeter properties;
    public $_params = array();
    // sent to the javascript.
    // unique id for the node.
    public $uid;
    public $nodes = array();
    // false means child records are pre-loaded.
    public $dynamic_load = false;
    //default script to load node data (children)
    public $dynamicloadfunction = 'loadDataForNode';
    //show node expanded during initial load.
    public $expanded = false;

    /**
     * ExtNode constructor.
     * @param $id
     * @param $label
     * @param bool $show_expanded
     *
     * properties set here will be accessible via
     * node.data object in javascript.
     * users can add a collection of paramaters that will
     * be passed to objects responding to tree events
     */
    public function __construct($id, $label, $show_expanded = true)
    {
        $this->_label = $label;
        $this->id = $id;
        $this->_properties['text'] = $label;
        $this->uid = microtime();
        $this->set_property('id', $id);
        $this->expanded = $show_expanded;
    }

    /**
     * @param $name
     * @param $value
     * @param bool $is_param
     */
    public function set_property($name, $value, $is_param = false)
    {
        if (!empty($name) && ($value === 0 || !empty($value))) {
            if ($is_param == false) {
                $this->_properties[$name] = $value;
            } else {
                $this->_params[$name] = $value;
            }
        }
    }

    /**
     * add a child node.
     * @param $node
     */
    function add_node($node)
    {
        $this->nodes[$node->uid] = $node;
    }

    /**
     * @return array - definition of the node. the definition is a multi-dimension array and has 3 parts.
     * data-> definition of the current node.
     * attributes=> collection of additional attributes such as style class etc..
     * nodes: definition of children nodes.
     *
     */
    function get_definition()
    {
        $ret = array();

        $ret = $this->_properties;
        if (!empty($this->_params)) {
            $ret[] = $this->_params;
        }

        $ret['dynamicload'] = $this->dynamic_load;
        $ret['dynamicloadfunction'] = $this->dynamicloadfunction;
        $ret['expanded'] = $this->expanded;
        $ret['children'] = array();
        $ret['type'] = 1;

        foreach ($this->nodes as $node) {
            $ret['children'][] = $node->get_definition();
        }

        //$ret['leaf'] = empty($ret['children']);
        return $ret;
    }
}
