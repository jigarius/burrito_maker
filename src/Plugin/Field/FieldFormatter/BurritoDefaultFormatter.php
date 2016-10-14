<?php

namespace Drupal\burrito_maker\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Field formatter "burrito_default".
 *
 * @FieldFormatter(
 *   id = "burrito_default",
 *   label = @Translation("Burrito default"),
 *   field_types = {
 *     "burrito_maker_burrito",
 *   }
 * )
 */
class BurritoDefaultFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
      'toppings' => 'csv',
    ) + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {

    $output['toppings'] = array(
      '#title' => t('Toppings'),
      '#type' => 'select',
      '#options' => array(
        'csv' => t('Comma separated values'),
        'list' => t('Unordered list'),
      ),
      '#default_value' => $this->getSetting('toppings'),
    );

    return $output;

  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {

    $summary = array();

    // Determine ingredients summary.
    $toppings_summary = FALSE;
    switch ($this->getSetting('toppings')) {

      case 'csv':
        $toppings_summary = 'Comma separated values';
        break;

      case 'list':
        $toppings_summary = 'Unordered list';
        break;

    }

    // Display ingredients summary.
    if ($toppings_summary) {
      $summary[] = t('Toppings display: @format', array(
        '@format' => t($toppings_summary),
      ));
    }

    return $summary;

  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {

    $output = array();

    // Iterate over every field item and build a renderable array
    // (I call them rarray for short) for each item.
    foreach ($items as $delta => $item) {

      $build = array();

      // Render burrito name. Nothing fancy as such.
      // We build a "container" element, within which we render
      // 2 child elements: one, the label of the property (Name);
      // two, the value of the property (The name of the burrito
      // as entered by the user).
      $build['name'] = array(
        '#type' => 'container',
        '#attributes' => array(
          'class' => array('burrito__name'),
        ),
        'label' => array(
          '#type' => 'container',
          '#attributes' => array(
            'class' => array('field__label'),
          ),
          '#markup' => t('Name'),
        ),
        'value' => array(
          '#type' => 'container',
          '#attributes' => array(
            'class' => array('field__item'),
          ),
          // We use #plain_text instead of #markup to prevent XSS.
          // plain_text will clean up the burrito name and render an
          // HTML entity encoded string to prevent bad-guys from
          // injecting JavaScript.
          '#plain_text' => $item->name,
        ),
      );

      // Render toppings (or ingredients) for the burrito.
      // Here as well, we follow the same format as above.
      // We build a container, within which, we render the property
      // label (Toppings) and the actual values for the toppings
      // as per configuration.
      $toppings_format = $this->getSetting('toppings');
      $build['toppings'] = array(
        '#type' => 'container',
        '#attributes' => array(
          'class' => array('burrito__toppings'),
        ),
        'label' => array(
          '#type' => 'container',
          '#attributes' => array(
            'class' => array('field__label'),
          ),
          '#markup' => t('Toppings'),
        ),
        'value' => array(
          '#type' => 'container',
          '#attributes' => array(
            'class' => array('field__item'),
          ),
          // The buildToppings method takes responsibility of generating
          // markup for burrito toppings as per the format set in field
          // configuration. We use $this->getSetting('toppings') above to
          // read the configuration.
          'text' => $this->buildToppings($toppings_format, $item),
        ),
      );

      $output[$delta] = $build;

    }

    return $output;

  }

  /**
   * Builds a renderable array or string of toppings.
   *
   * @param string $format
   *   The format in which the toppings are to be displayed.
   *
   * @return array
   *   A renderable array of toppings.
   */
  public function buildToppings($format, FieldItemInterface $item) {
    // Instead of having a switch-case we build a dynamic method name
    // as per a pre-determined format. In this way, if we will to add
    // a new format in the future, all we will have to do is create a
    // new method named "buildToppingsFormatName()".
    $callback = 'buildToppings' . ucfirst($format);
    return $this->$callback($item);
  }

  /**
   * Format toppings as CSV.
   */
  public function buildToppingsCsv(FieldItemInterface $item) {
    $toppings = $item->getToppings();
    return array(
      '#markup' => implode(', ', $toppings),
    );
  }

  /**
   * Format toppings as an unordered list.
   */
  public function buildToppingsList(FieldItemInterface $item) {
    // "item_list" is a very handy render type.
    return array(
      '#theme' => 'item_list',
      '#items' => $item->getToppings(),
    );
  }

}
