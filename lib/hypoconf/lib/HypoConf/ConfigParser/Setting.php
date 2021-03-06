<?php
/**
 * Required by this class.
 */
namespace HypoConf\ConfigParser;

use HypoConf;
use HypoConf\ConfigParser;

use PEAR2\Console;

class Setting extends Console\CommandLine\Option
{
    public $path;
    public $required = false;
    public $required_one = false;
    public $action = 'StoreStringOrFalse';
    public $divideBy = ' ';
    public $settable = true;
    //public $default = false;
    
    // }}}
    // __construct() {{{
    
    /**
     * Constructor.
     *
     * @param string $name   The name of the option
     * @param array  $params An optional array of parameters
     *
     * @return void
     */
    /*
    public function __construct($name = null, $params = array()) 
    {
        parent::__construct($name, $params);
    }
    */
    
    // }}}
    // dispatchAction() {{{
    
    /**
     * Formats the value $value according to the action of the option and 
     * updates the passed ConfigParser_Result object.
     *
     * @param mixed                      $value  The value to format
     * @param ConfigParser_Result $result The result instance
     * @param ConfigParser        $parser The parser instance
     *
     * @return void
     * @throws ConfigParser_Exception
     */
    public function dispatchAction($value, $result, $parser)
    {
        /*
        $actionInfo = Console\CommandLine::$actions[$this->action];
        $clsname    = $actionInfo[0];
        */
        
        //set_include_path('./');
        if(isset(Console\CommandLine::$actions[$this->action]))
        {
            $actionInfo = Console\CommandLine::$actions[$this->action];
            $clsname = $actionInfo[0];
        }
        else
        {
            $actionInfoConfigParser = ConfigParser::$actions[$this->action];
            $clsname = $actionInfoConfigParser[0];
        }
        
        if(!isset($clsname)) $clsname = $actionInfo[0];
        
        // ALWAYS create a new instance!
        //if ($this->_action_instance === null) {
            $this->_action_instance  = new $clsname($result, $this, $parser);
        //}
    
        // check value is in option choices
        /*
        if (!empty($this->choices) && !in_array($this->_action_instance->format($value), $this->choices)) {
            throw ConfigParser_Exception::factory(
                'OPTION_VALUE_NOT_VALID',
                array(
                    'name'    => $this->name,
                    'choices' => implode('", "', $this->choices),
                    'value'   => $value,
                ),
                $parser,
                $this->messages
            );
        }
        */
        //$this->action_params['path'] = &$this->path;
        //$this->action_params['default'] = &$this->default;
        //var_dump($value);
        $this->_action_instance->execute($value, $this->action_params);
    }
    
    // }}}
    // validate() {{{
    
    /**
     * Validates the option instance.
     *
     * @return void
     * @throws ConfigParser_Exception
     * @todo use exceptions instead
     */
    public function validate()
    {
        // check if the option name is valid
        if (!preg_match('/^[a-zA-Z_\x7f-\xff]+[a-zA-Z0-9_\x7f-\xff]*$/',
            $this->name)) {
            ConfigParser::triggerError('option_bad_name',
                E_USER_ERROR, array('{$name}' => $this->name));
        }
        // call the grandparent validate method
        Console\CommandLine\Element::validate();
        // a path must be provided
        if ($this->path == null) {
            ConfigParser::triggerError('option_long_and_short_name_missing',
                E_USER_ERROR, array('{$name}' => $this->name));
        }
        // check if we have a valid action
        if (!is_string($this->action)) {
            ConfigParser::triggerError('option_bad_action',
                E_USER_ERROR, array('{$name}' => $this->name));
        }
        //var_dump(ConfigParser::$actions[$this->action]);
        if ((!isset(Console\CommandLine::$actions[$this->action])) && (!isset(ConfigParser::$actions[$this->action]))) {
            ConfigParser::triggerError('option_unregistered_action',
                E_USER_ERROR, array(
                    '{$action}' => $this->action,
                    '{$name}' => $this->name
                ));
        }
        // if the action is a callback, check that we have a valid callback
        if ($this->action == 'Callback' && !is_callable($this->callback)) {
            ConfigParser::triggerError('option_invalid_callback',
                E_USER_ERROR, array('{$name}' => $this->name));
        }
    }
    
    // }}}
    // setDefaults() {{{
    
    /**
     * Set the default value according to the configured action.
     *
     * Note that for backward compatibility issues this method is only called 
     * when the 'force_options_defaults' is set to true, it will become the
     * default behaviour in the next major release of PEAR2\Console\CommandLine.
     *
     * @return void
     */
    public function setDefaults()
    {
        if ($this->default !== null) {
            // already set
            return;
        }
        switch ($this->action) {
        case 'Counter':
        case 'StoreInt':
            $this->default = 0;
            break;
        case 'StoreFloat':
            $this->default = 0.0;
            break;
        case 'StoreArray':
            $this->default = array();
            break;
        case 'StoreTrue':
            $this->default = false;
            break;
        case 'StoreFalse':
            $this->default = true;
            break;
        case 'StoreOnOff':
            $this->default = -1;
            break;
        case 'StoreStemOrFalse':
            $this->default = false;
            break;
        default:
            return;
        }
    }
    
}
