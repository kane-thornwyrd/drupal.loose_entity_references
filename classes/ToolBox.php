<?php
/**
 * @file
 * Contain the ToolBox Class.
 */

namespace kanethornwyrd\loose_entity_references;

/**
 * Dumb class, with only static public utility methods.
 *
 * @author jean-cedric
 */
class ToolBox {

  public static $value;

  /**
   *  We don't allow instanciation, because it's just an utility class to embed
   *   stateless functions.
   */
  protected final function __construct() {

  }

  /**
   *  We don't allow cloning, because it's just an utility class to embed
   *   stateless functions.
   */
  protected final function __clone() {

  }

  /**
   * Transform pseudo array string into actual Array
   *
   * @example
   *   use kanethornwyrd\loose_entity_references\ToolBox;
   *   $string = 'entity[bundle][field]';
   *   $out = ToolBox::targetArrayExtractor($string);
   *   //$out == array('entity' => array('bundle' => array('field' => '')))
   *
   * @param string $string
   *   The pseudo array String.
   *
   * @return array
   *   The resulting array
   */
  public static function targetStringToArrayExtractor($string, $chain = NULL) {
    for ($i = 0, $par_in = 0, $out = '$return = array("', $string_len = strlen($string); $i < $string_len; $i++) {
      if ($string[$i] !== "[" && $string[$i] !== "]") {
        $out .= $string[$i];
      }
      else {
        if ($string[$i] === "[") {
          $out .= '" => array("';
          $par_in++;
        }
      }
    }
    $out .= '"=>"")';
    $out .= str_repeat(')', $par_in);
    $out .= ';';
    eval($out);

    if (!empty($chain)) {
      self::$value = $return;
      $return = self::$chain();
    }
    return $return;

  }

  public static function settingArrayToClass(array $settings = NULL) {
    if (empty($settings)) {
      $settings = self::$value;
    }
    $entity_type = key($settings);
    $bundle = key($settings[$entity_type]);
    $field = key($settings[$entity_type][$bundle]);
    $settings = new \stdClass();
    $settings->entity_type = $entity_type;
    $settings->bundle = $bundle;
    $settings->field = $field;
    return $settings;
  }
}
