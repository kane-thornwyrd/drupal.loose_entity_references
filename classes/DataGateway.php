<?php
/**
 * @file
 * Contain the DataGateway Class.
 */

namespace kanethornwyrd\loose_entity_references;

use \Database;
use kanethornwyrd\loose_entity_references\ToolBox;

/**
 * DataGateway for loose_entity_references, directly interacting with the Drupal
 * DAL.
 * Nothing really fancy, since we can't properly override the DAL. It's just
 * cleanier than a plain function list.
 *
 * @author jean-cedric Thérond
 */
class DataGateway extends Database {

  const
  TARGETS_TABLE_NAME = 'loose_entity_references_targets',
  REGISTER_TABLE_NAME = 'loose_entity_references_register'

  ;

  /**
   * Get the informations about the targeting of an Entity.
   *
   * @param mixed|NULL $entity
   *   The entity for which query.
   * @param array $options
   *   THe options to pass to the Database connector.
   *
   * @return array
   *   the targeting informations concerning this entity or all targets.
   */
  public static function getTargetingInformations($entity = NULL, array $options = array()) {
    $targeting_informations = &drupal_static(__FUNCTION__);
    if (!isset($targeting_informations)) {
      $cache = cache_get('loose_entity_references:targets');
      if ($cache) {
        $targeting_informations = $cache->data;
      }
      else {

        if (empty($options['target'])) {
          $options['target'] = 'default';
        }

        $query = self::getConnection($options['target'])
        ->select(self::TARGETS_TABLE_NAME, 'targets', $options)
        ->fields('targets');
        $results = $query->execute()->fetchAll();

        $targeting_informations = array();
        foreach ($results as $entry) {
          $targeting_informations[$entry->entity_type][$entry->bundle][$entry->field_name] = $entry->display;
        }
        cache_set('loose_entity_references:targets', $targeting_informations,
        'cache');
      }

      if ($entity === NULL) {
        return $targeting_informations;
      }
      else {
        return $targeting_informations[$entity->entity_type][$entity->bundle];
      }
    }

  }

  /**
   * Determine if an Entity is a valid target by the Register.
   *
   * @param mixed $entity
   *   The entity to test.
   * @param array $options
   *   THe options to pass to the Database connector.
   *
   * @return bool
   *   Is this entity a valid target ?
   */
  public static function isEntityTargetable($entity, array $options = array()) {
    $targeting_informations = self::getTargetingInformations($entity, $options);

    $fields_info = field_info_instances($entity->entity_type, $entity->bundle);
    foreach ($targeting_informations as $field_name => $display) {
      if (isset($fields_info[$field_name])) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Central function of this module. Gather the referenced entity
   *
   * @param type $param
   */
  public static function getReferencedEntity($param) {

  }
}
