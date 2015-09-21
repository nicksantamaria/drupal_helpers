<?php
/**
 * @file
 * Utility functionality not related to Drupal.
 */

namespace Drupal\drupal_helpers;

/**
 * Class Utility.
 *
 * @package Drupal\drupal_helpers.
 */
class Utility {
  /**
   * Recursively remove empty elements from array.
   *
   * @param array $haystack
   *   Array to remove elements from.
   *
   * @return array
   *   Array with removed elements.
   */
  static public function arrayRemoveEmpty(array $haystack) {
    foreach ($haystack as $key => $value) {
      if (is_array($haystack[$key])) {
        $haystack[$key] = call_user_func(array(
          __CLASS__,
          __FUNCTION__,
        ), $haystack[$key]);
      }

      if (empty($haystack[$key])) {
        unset($haystack[$key]);
      }
    }

    return $haystack;
  }

  /**
   * Helper to retrieve array column.
   *
   * Supports scalar, arrays and object as array values. For complex objects
   * value retrieval a getter must be specified.
   *
   * @param mixed $value
   *   Value to extract column from.
   * @param string|int $column
   *   Optional array column to retrieve value from.
   * @param string $getter
   *   Optional getter for cases when values are complex objects.
   *
   * @return array
   *   Array of values retrieved from column or a scalar value if scalar value
   *   was provided.
   *
   * @throws \Exception
   *   Exception if specified $column does not exist in array.
   */
  static public function arrayGetColumn($value, $column = NULL, $getter = NULL) {
    $result = $value;

    if (is_array($value)) {
      // Value is an array and $column is set.
      foreach ($value as $k => $v) {
        // Value's value is an array.
        if (is_array($v)) {
          // Column exists.
          if (array_key_exists($column, $v)) {
            // Recursively call current function and retrieve each value.
            $result[$k] = call_user_func(array(
              __CLASS__,
              __FUNCTION__,
            ), $v[$column], NULL, $getter);
          }
          // Column is set, but does not exist.
          else {
            throw new Exception(format_string('Column @column does not exist', array(
              '@column' => $column,
            )));
          }
        }
        // Value is not an array.
        else {
          $result[$k] = call_user_func(array(
            __CLASS__,
            __FUNCTION__,
          ), $v, $column, $getter);
        }
      }
    }
    // Value is not an array.
    else {
      // Value is an object.
      if (is_object($value)) {
        // Try column as object property.
        if (in_array($column, array_keys(get_object_vars($value)))) {
          $result = $value->{$column};
        }
        // Try using getter.
        elseif (method_exists($value, $getter)) {
          $result = call_user_func(array(
            $value,
            $getter,
          ));
        }
        // Value object getter does not exist - return as is.
        else {
          $result = $value;
        }
      }
      // Value is a scalar or an array - return as is.
      else {
        $result = $value;
      }
    }

    return $result;
  }

}