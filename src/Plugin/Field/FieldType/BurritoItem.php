<?php

namespace Drupal\burrito_maker\Plugin\Field\FieldType;

use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;

/**
 * Contains field type "burrito_maker_burrito".
 *
 * @FieldType(
 *   id = "burrito_maker_burrito",
 *   label = @Translation("Burrito"),
 *   description = @Translation("Custom burrito field."),
 *   category = @Translation("Food"),
 *   default_widget = "burrito_default",
 *   default_formatter = "burrito_default",
 * )
 */
class BurritoItem extends FieldItemBase implements FieldItemInterface {

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {

    module_load_include('inc', 'burrito_maker');

    $output = array();

    // Create basic columns for the name and a vegetarian flag.
    $output['columns']['name'] = array(
      'type' => 'varchar',
      'length' => 255,
    );
    $output['columns']['vegetarian'] = array(
      'type' => 'int',
      'length' => 1,
    );

    // Make a column for every topping.
    $topping_coll = burrito_maker_get_toppings();
    foreach ($topping_coll as $topping_key => $topping_name) {
      $output['columns'][$topping_key] = array(
        'type' => 'int',
        'length' => 1,
      );
    }

    return $output;

  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {

    module_load_include('inc', 'burrito_maker');

    $properties['name'] = DataDefinition::create('string')
      ->setLabel(t('Name'))
      ->setRequired(FALSE);

    $properties['vegetarian'] = DataDefinition::create('boolean')
      ->setLabel(t('Vegetarian'));

    $topping_coll = burrito_maker_get_toppings();
    foreach ($topping_coll as $topping_key => $topping_name) {
      $properties[$topping_key] = DataDefinition::create('boolean')
        ->setLabel($topping_name);
    }

    return $properties;

  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {

    $item = $this->getValue();

    $has_stuff = FALSE;

    // See if any of the topping checkboxes have been checked off.
    foreach (burrito_maker_get_toppings() as $topping_key => $topping_name) {
      if (isset($item[$topping_key]) && $item[$topping_key] == 1) {
        $has_stuff = TRUE;
        break;
      }
    }

    // Has the user checked off the 'vegetarian' checkbox?
    if (isset($item['vegetarian']) && $item['vegetarian'] == 1) {
      $has_stuff = TRUE;
    }

    // Has the user entered a name for the Burrito?
    if (isset($item['name']) && !empty($item['name'])) {
      $has_stuff = TRUE;
    }

    return !$has_stuff;

  }

  /**
   * {@inheritdoc}
   */
  public function preSave() {

    // For vegetarian burritos, do not save meat topping data.
    

  }

  /**
   * {@inheritdoc}
   */
  public static function defaultFieldSettings() {
    return array(
      'allow_meat' => 1,
    ) + parent::defaultFieldSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    $output['allow_meat'] = array(
      '#type' => 'checkbox',
      '#title' => t('Allow meat'),
      '#default_value' => $this->getSetting('allow_meat'),
    );
    return $output;
  }

  /**
   * Returns an array of toppings assigned to the burrito.
   *
   * @return array
   *   An associative array of all toppings assigned to the burrito.
   */
  public function getToppings() {

    module_load_include('inc', 'burrito_maker');

    $output = array();

    foreach (burrito_maker_get_toppings() as $topping_key => $topping_name) {
      if ($this->$topping_key) {
        $output[$topping_key] = $topping_name;
      }
    }

    return $output;

  }

}
