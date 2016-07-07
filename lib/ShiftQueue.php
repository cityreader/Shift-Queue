<?php

/**
 * Class ShiftQueue
 */
class ShiftQueue extends SystemQueue {

  /**
   * Override \SystemQueue::claimItem().
   *
   * Allow other module to check claim item condition and add a counter.
   */
  public function claimItem($lease_time = 30) {
    // Pause a queue.
    if (variable_get('queue_enable_' . $this->name, 1) == 0) {
      return FALSE;
    }

    foreach (module_implements('shift_queue_claim_item_check') as $module) {
      $func = $module . '_shift_queue_claim_item_check';
      $result = $func($this->name);
      if (!$result) {
        return FALSE;
      }
    }

    $max_claim_retry = 10;

    do {
      $max_claim_retry--;

      $item = parent::claimItem($lease_time);

      if ($item === FALSE) {
        return FALSE;
      }

      $count = $this->getCount($item);

      if ($this->exceedMaxFailedAttempts($count)) {
        $this->moveToFailedQueue($item);
        continue;
      }

      $this->increaseCounter($item, $count);

      return $item;

    } while ($max_claim_retry > 0);

    return FALSE;
  }

  /**
   * Delete a queue item and its count.
   *
   * @param $item
   */
  public function deleteItem($item) {
    parent::deleteItem($item);

    $this->deleteCounter($item);
  }

  /**
   * Look up count of a queue item.
   *
   * @param $item
   * @return int
   */
  protected function getCount($item) {
    $count = db_query('SELECT count FROM {shift_queue_count} WHERE item_id=:item_id', array(':item_id' => $item->item_id))->fetchField();
    return $count ? $count : 0;
  }

  /**
   * Increase counter by 1.
   *
   * @param $item
   * @param $count
   * @throws \Exception
   */
  protected function increaseCounter($item, $count) {
    if ($count == 0) {
      db_insert('shift_queue_count')->fields(array(
        'item_id' => $item->item_id,
        'count' => 1,
      ))
        ->execute();
    }
    else {
      db_update('shift_queue_count')
        ->expression('count', 'count + 1')
        ->condition('item_id', $item->item_id)
        ->execute();
    }
  }

  /**
   * Delete counter.
   *
   * @param $item
   */
  protected function deleteCounter($item) {
    db_delete('shift_queue_count')
      ->condition('item_id', $item->item_id)
      ->execute();
  }

  /**
   * Check if this queue item is processed up to maximum failed attempts.
   *
   * @param $count
   * @return bool
   */
  protected function exceedMaxFailedAttempts($count) {
    $max = variable_get('queue_item_max_failed_attempts_' . $this->name, 2);
    if ($max == 0 || $count < $max) {
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Move queue item to failed queue.
   *
   * @param $item
   */
  protected function moveToFailedQueue($item) {
    $failed_queue_name = variable_get('queue_failed_name_' . $this->name, $this->name . ':failed');

    db_update('queue')
      ->fields(array('name' => $failed_queue_name))
      ->condition('item_id', $item->item_id)
      ->execute();
  }

}
