<?php

/**
 * @file
 * Contains utility functions.
 */

/**
 * Returns an array of topping names.
 *
 * @param string $type
 *   [optional] Category of toppings to return. Defauts to "*",
 *   and returns both vege and meat toppings.
 *
 * @return array
 *   An array of topping names, indexed by machine names.
 *   array(
 *     'sweet_potato' => 'Sweet Potato',
 *     'tomato' => 'Tomato',
 *     ...
 *   );
 */
function burrito_maker_get_toppings($type = '*') {

  switch ($type) {

    case 'vege':
      $topping_coll = array(
        'Rice', 'Beans', 'Jalapeno', 'Corn', 'Olives', 'Capsicum', 'Lettuce',
        'Cheese', 'Tofu', 'Guacamole', 'Hot Sauce',
      );
      return _burrito_maker_toppings_make_assoc($topping_coll);

    case 'meat':
      $topping_coll = array(
        'Gravy', 'Chicken', 'Beef', 'Pork', 'Smoked Meat',
      );
      return _burrito_maker_toppings_make_assoc($topping_coll);

    case '*':
      return array_merge(burrito_maker_get_toppings('vege'), burrito_maker_get_toppings('meat'));

    default:
      throw new \InvalidArgumentException('Argument 1 expected to be one of "meat" or "vege".');

  }

}

/**
 * Converts an array of topping names into an assocative array.
 */
function _burrito_maker_toppings_make_assoc($array) {
  $output = array();
  foreach ($array as $val) {
    $key = _burrito_maker_make_machine_name($val);
    $output[$key] = $val;
  }
  return $output;
}

/**
 * Generates a machine name from a given string.
 */
function _burrito_maker_make_machine_name($string) {
  return str_replace(' ', '_', strtolower($string));
}
