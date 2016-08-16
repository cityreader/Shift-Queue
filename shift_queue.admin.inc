<?php
/**
 * @file
 * Implements page callbacks for admin.
 */

/**
 * Shift queue admin form.
 *
 * @param $form
 * @param $form_state
 * @return mixed
 */
function shift_queue_admin_form($form, &$form_state) {
  $queues = module_invoke_all('cron_queue_info');
  drupal_alter('cron_queue_info', $queues);

  foreach ($queues as $name=>$queue) {
    $class = variable_get('queue_class_' . $name, 'SystemQueue');
    if ($class == 'ShiftQueue' || is_subclass_of($class, 'ShiftQueue')) {
      $form[$name] = array(
        '#type' => 'fieldset',
        '#title' => $name,
      );

      $form[$name]['queue_enable_' . $name] = array(
        '#type' => 'checkbox',
        '#title' => t('Enable processing of queue: %name', ['%name' => $name]),
        '#default_value' => variable_get('queue_enable_' . $name, 1),
        '#return_value' => 1,
      );

      $form[$name]['queue_item_max_failed_attempts_' . $name] = array(
        '#type' => 'textfield',
        '#title' => t('Maximum failed processing attempts'),
        '#size' => 2,
        '#maxlength' => 2,
        '#default_value' => variable_get('queue_item_max_failed_attempts_' . $name, 2),
      );
    }
  }

  return system_settings_form($form);
}
