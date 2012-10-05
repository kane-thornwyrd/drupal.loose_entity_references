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
  REGISTRY_TABLE_NAME = 'loose_entity_references_registry'

  ;

  /**
   * Get the informations about the targeting of an Entity.
   *
   * @param string $dbtable
   *   The table to lookup for targeting informations.
   * @param mixed|NULL $entity
   *   The entity for which query.
   * @param array $options
   *   THe options to pass to the Database connector.
   *
   * @return array
   *   the targeting informations concerning this entity or all targets.
   */
  public static function getTargetingInformations($entity = NULL, array $options = array()) {
//    $targeting_informations = &drupal_static(__FUNCTION__);
//    if (!isset($targeting_informations)) {
//      $cache = cache_get('loose_entity_references:targets');
//      if ($cache) {
//        $targeting_informations = $cache->data;
//      }
//      else {

    if (empty($options['target'])) {
      $options['target'] = 'default';
    }

    $query = self::getConnection($options['target'])
    ->select(self::TARGETS_TABLE_NAME, 'targets', $options)
    ->fields('targets');
    $results = $query->execute()->fetchAll();

//    dpm($results);
    $targeting_informations = array();
    foreach ($results as $entry) {
      if (empty($targeting_informations[$entry->entity_type])) {
        $targeting_informations[$entry->entity_type] = array();
      }
      if (empty($targeting_informations[$entry->entity_type][$entry->bundle])) {
        $targeting_informations[$entry->entity_type][$entry->bundle] = array();
      }
      $targeting_informations[$entry->entity_type][$entry->bundle][$entry->field_name] = $entry->display;
    }
//        cache_set('loose_entity_references:targets', $targeting_informations,
//        'cache');
//      }


    if ($entity === NULL) {
      return $targeting_informations;
    }
    else {
      return empty($targeting_informations[$entity->entity_type][$entity->bundle])
      ? array()
      : $targeting_informations[$entity->entity_type][$entity->bundle];
    }
//    }

  }

  /**
   * Determine if an Entity is a valid target for the Registry.
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
   * Gather the referenced entity/ies from the Registry
   *
   * @param type $param
   */
  public static function getMatchingEntities($entity_type, $bundle, $field_name, $field_value, array $options = array()) {

    if (empty($options['target'])) {
      $options['target'] = 'default';
    }

    $query = self::getConnection($options['target'])
    ->select(self::REGISTRY_TABLE_NAME, 'registry', $options)
    ->fields('registry')
    ->condition('entity_type', $entity_type)
    ->condition('bundle', $bundle)
    ->condition('field_value', $field_value);

    $results = $query->execute()->fetchAll();
    $duids = array();
    foreach ($results as $key => $result) {
      $duids[$result->duid] = $result->duid;
    }
    $results = entity_load($entity_type, $duids);
    return $results;

  }

  public static function insertEntityInRegistry($entity, array $options = array()) {

    if (empty($options['target'])) {
      $options['target'] = 'default';
    }
    list($id, $vid, $bundle) = entity_extract_ids($entity->entity_type, $entity);
    $settings = self::getTargetingInformations($entity, $options);
    foreach ($settings as $field_name => $display_mn) {
      $fields_data = field_get_items($entity->entity_type, $entity, $field_name);
      foreach ($fields_data as $value) {
// duid 	entity_type 	bundle 	field_name field_value
        $query = self::getConnection($options['target'])
        ->merge(self::REGISTRY_TABLE_NAME, $options)
        ->key(array(
          'duid'        => $id,
          'entity_type' => $entity->entity_type,
          'bundle'      => $bundle,
          'field_name'  => $field_name,
        ))
        ->fields(array(
          'field_value' => $value
        ))->execute();
      }
    }

  }

  public static function deleteEntityInRegistry($entity, array $options = array()) {

    if (empty($options['target'])) {
      $options['target'] = 'default';
    }

    list($id, $vid, $bundle) = entity_extract_ids($entity->entity_type, $entity);
    $settings = self::getTargetingInformations($entity, $options);
    foreach ($settings as $field_name => $display_mn) {
      $query = self::getConnection($options['target'])
      ->delete(self::REGISTRY_TABLE_NAME)
      ->condition('duid', $id)
      ->condition('entity_type', $entity->entity_type)
      ->condition('bundle', $bundle)
      ->execute();
    }

  }

  public static function insertTargetingDatas($settings, array $options = array()) {

    if (empty($options['target'])) {
      $options['target'] = 'default';
    }
    foreach ($settings as $setting) {
      $query = self::getConnection($options['target'])
      ->merge(self::TARGETS_TABLE_NAME)
      ->key(array('field_id' => $setting->field_id))
      ->fields(array(
        'entity_type' => $setting->entity_type,
        'bundle'      => $setting->bundle,
        'field_name'  => $setting->field,
        'display'     => $setting->display
      ))
      ->execute();
    }

  }

  public static function getLooseEntityReferenceFieldsSettings(array $options = array()) {

    if (empty($options['target'])) {
      $options['target'] = 'default';
    }

    $query = self::getConnection($options['target'])
    ->select('field_config_instance', 'instance_confs');
    $query->join('field_config', 'confs', 'confs.id = instance_confs.field_id');
    $query->fields('instance_confs', array('id', 'data'))
    ->condition('confs.type', 'loose_entity_reference')
    ->condition('confs.active', TRUE)
    ->condition('confs.deleted', FALSE)
    ->condition('instance_confs.deleted', FALSE);

    $results = $query->execute()->fetchAll();
    $out = array();

    foreach ($results as $res) {
      $data = unserialize($res->data);
      $instance_settings = $data['widget']['settings'];

      $settings = ToolBox::targetStringToArrayExtractor($instance_settings['target']['entity_bundle_field'],
      'settingArrayToClass');
      $settings->display = $instance_settings['target']['entity_bundle_display'];
      $settings->field_id = $res->id;
      $out[] = $settings;
    }

    return $out;

  }
}