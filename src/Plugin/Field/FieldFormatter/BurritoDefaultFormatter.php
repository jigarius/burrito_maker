<?php

namespace Drupal\burrito_maker\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * @file
 * Contains field formatter "burrito_default".
 */

/**
 * Contains field widget "burrito_default".
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

    foreach ($items as $delta => $item) {

      $build = array();

      // Name of the Burrito.
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
          '#plain_text' => $item->name,
        ),
      );

      // Show toppings.
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
    return array(
      '#theme' => 'item_list',
      '#items' => $item->getToppings(),
    );
  }

}
