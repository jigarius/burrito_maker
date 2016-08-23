<?php

namespace Drupal\burrito_maker\Plugin\Field\FieldWidget;

use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Field\WidgetInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Contains field widget "burrito_default".
 *
 * @FieldWidget(
 *   id = "burrito_default",
 *   label = @Translation("Burrito default"),
 *   field_types = {
 *     "burrito_maker_burrito",
 *   }
 * )
 */
class BurritoDefaultWidget extends WidgetBase implements WidgetInterface {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {

    // Load burrito_maker.toppincs.inc file for reading topping data.
    module_load_include('inc', 'burrito_maker');

    // $field_name is the machine name of the field,
    // assigned by the admin during field creation.
    $field_name = $this->fieldDefinition->getName();

    // $item is where the current saved values are stored.
    $item =& $items[$delta];

    // $element is already populated with #title, #description, #delta,
    // #required, #field_parents, etc.
    //
    // In this example, $element is a fieldset, but it could be any element
    // type (textfield, checkbox, etc.)
    $element += array(
      '#type' => 'fieldset',
    );

    // Array keys in $element correspond roughly
    // to array keys in $item, which correspond
    // roughly to columns in the database table.
    $element['name'] = array(
      '#title' => t('Name'),
      '#type' => 'textfield',
      // Use #default_value to pre-populate the element
      // with the current saved value.
      '#default_value' => isset($item->name) ? $item->name : '',
    );

    // Make sure we aren't a vegetarian burrito restaurant
    // before showing the meat options.
    $allow_meat = $this->getFieldSetting('allow_meat');

    if ($allow_meat) {

      $element['vegetarian'] = array(
        '#title' => t('Vegetarian'),
        '#type' => 'checkbox',
        '#default_value' => isset($item->vegetarian) ? $item->vegetarian : '',
      );

      // Have a separate fieldset for meat.
      $element['meat'] = array(
        '#title' => t('Meat'),
        '#type' => 'fieldset',
        '#process' => array(__CLASS__ . '::process_toppings_fieldset'),
        // The meat fieldset should only be visible if the vegetarian
        // checkbox is not checked.
        '#states' => array(
          'visible' => array(
            self::getJquerySelector($element['#field_parents'], $field_name, $delta, 'vegetarian') =>
            array('checked' => FALSE),
          ),
        ),
      );

      // Create a checkbox item for each meat on the menu.
      foreach (burrito_maker_get_toppings('meat') as $topping_key => $topping_name) {
        $element['meat'][$topping_key] = array(
          '#title' => t($topping_name),
          '#type' => 'checkbox',
          '#default_value' => isset($item->$topping_key) ? $item->$topping_key : '',
        );
      }
    }

    // Have a separate fieldset for non-meat toppings.
    $element['toppings'] = array(
      '#title' => t('Toppings'),
      '#type' => 'fieldset',
      '#process' => array(__CLASS__ . '::processToppingsFieldset'),
    );

    // Create a checkbox item for each topping on the menu.
    foreach (burrito_maker_toppings('vege') as $topping_key => $topping_name) {
      $element['toppings'][$topping_key] = array(
        '#title' => t($topping_name),
        '#type' => 'checkbox',
        '#default_value' => isset($item->$topping_key) ? $item->$topping_key : '',
      );
    }

    return $element;

  }

  /**
   * Form widget process callback.
   */
  public static function processToppingsFieldset($element, FormStateInterface $form_state, array $form) {

    // The last fragment of the name, i.e. meat|toppings is not required
    // for structuring of values.
    $elem_key = array_pop($element['#parents']);

    return $element;

  }

  /**
   * Generates a jQuery selector for a burrito widget element.
   *
   * @return string
   *   A jQuery selector that Drupal will use to test for
   *   #states conditions.
   */
  public static function getJquerySelector($field_parents, $field_name, $delta, $item_name) {

    $field_parents[] = $field_name;
    $field_parents[] = $delta;
    $field_parents[] = $item_name;

    $first_parent = array_shift($field_parents);

    return ':input[name="' . $first_parent . '[' . implode('][', $field_parents) . ']"]';

  }

}
