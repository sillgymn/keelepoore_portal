<?php

namespace Drupal\views_fieldsets;

use Drupal\Component\Utility\Html;
use Drupal\Core\Render\Markup;
use Drupal\views\ResultRow;
use Drupal\views_fieldsets\Plugin\views\field\Fieldset;

class RowFieldset {

  public $row;

  public $properties = [];

  public $children = [];

  /**
   * Constructs an RowFieldset object.
   */
  public function __construct($field, ResultRow $row) {
    $this->row = $row;
    $this->properties = get_object_vars($field);
  }

  /**
   * Magic method: __isset a property value.
   *
   * @param string $name
   *   Method's name.
   */
  public function __isset($name) {
    return TRUE;
  }

  /**
   * Magic method: Gets a property value.
   *
   * @param string $name
   *   Method's name.
   */
  public function __get($name) {
    if (is_callable($method = [$this, "get_$name"])) {
      return call_user_func($method);
    }
    if (!empty($name) && !empty($this->properties[$name])) {
      return $this->properties[$name];
    }
    return FALSE;
  }

  /**
   * Object get_content().
   */
  public function get_content() {
    return $this->render();
  }

  /**
   * Object get_wrapper_element().
   */
  public function get_wrapper_element() {
    return '';
  }

  /**
   * Object get_element_type().
   */
  public function get_element_type() {
    return '';
  }

  /**
   * Object render().
   */
  public function render() {
    // @todo Theme hook suggestions!
    $element = [
      '#theme' => 'views_fieldsets_' . $this->getWrapperType(),
      '#fields' => $this->children,
      '#legend' => Markup::create($this->getLegend()),
      '#collapsible' => (bool) $this->handler->options['collapsible'],
      '#collapsed' => (bool) $this->handler->options['collapsed'],
      '#classes' => $this->getClasses(),
    ];
    return render($element);
  }

  /**
   * Object getWrapperType().
   */
  protected function getWrapperType() {
    $allowed = Fieldset::getWrapperTypes();
    $wrapper = $this->handler->options['wrapper'];
    if (isset($allowed[$wrapper])) {
      return $wrapper;
    }

    reset($allowed);
    return key($allowed);
  }

  /**
   * Object getLegend().
   */
  protected function getLegend() {
    return $this->tokenize($this->handler->options['legend']);
  }

  /**
   * Object getClasses().
   */
  protected function getClasses() {
    $classes = explode('  ', $this->handler->options['classes']);
    $classes = array_map(function ($class) {
      return Html::getClass($this->tokenize($class));
    }, $classes);
    return implode(' ', $classes);
  }

  /**
   * Object tokenize().
   *
   * @param string $string
   *   String.
   */
  protected function tokenize($string) {
    return $this->handler->tokenizeValue($string, $this->row->index);
  }

  /**
   * Object addChild().
   *
   * @param array $fields
   *   Fields.
   * @param array $field_name
   *   Fields name.
   */
  public function addChild(array $fields, $field_name) {
    $this->children[$field_name] = $fields[$field_name];
  }

}
