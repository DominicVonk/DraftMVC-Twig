<?php
namespace DraftMVC;
if (!defined('DRAFT_VIEWS')) {
    define('DRAFT_VIEWS', __DIR__ . '/views');
}
if (!defined('DRAFT_STORAGE')) {
    define('DRAFT_STORAGE', __DIR__ . '/storage');
}
class DraftViewTwig {
    private $twig;
    private $html;
    private $file;
    private $data = array();
    private $filters = array();
    private $functions = array();
    public function __construct($file) {
        $loader = new \Twig_Loader_Filesystem(DRAFT_VIEWS);
        $twig = new \Twig_Environment($loader, array(
            'cache' => DRAFT_STORAGE. '/cache/',
            'auto_reload' => 'true',
        ));
        $this->file = $file;
        $this->twig = $twig;
    }
    public function escape($string) {
        return htmlentities($string);
    }
    public function &__get($var) {
        return $this->data[$var];
    }
    public function __call($func, $args) {
        return call_user_func_array($this->data['_call_' . $func], $args);
    }
    public function addFilter($name, $function, $options = null) {
        $this->filters[$name] = array();
        $this->filters[$name][0] = $function;
        $this->filters[$name][1] = $options;
    }
    public function addFunction($name, $function, $options = null) {
        $this->functions[$name] = array();
        $this->functions[$name][0] = $function;
        $this->functions[$name][1] = $options;
    }
    public function __set($var, $val) {
        if (is_callable($val)) {
            $this->twig->addFunction(new \Twig_Function($var, $val));
            $this->data['_call_' . $var] = $val;
        } else {
            $this->data[$var] = $val;
        }
    }
    public function setViewFile($file) {
        $this->file = $file;
    }
    public function show() {
        foreach($this->filters as $name => $function) {
            $this->twig->addFilter(new \Twig_Filter($name, $function[0], $function[1]));
        }
        foreach($this->functions as $name => $function) {
            $this->twig->addFunction(new \Twig_Function($name, $function[0], $function[1]));
        }
        $template = $this->twig->load($this->file . '.twig');
        return $template->render($this->data);
    }
}
