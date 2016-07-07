<?php

/**
 * @file
 * API function examples of Shift queue.
 */

/**
 * Check condition before claim an item.
 *
 * @param string $queue_name
 *  A string of queue name.
 * 
 * @return bool
 */
function hook_shift_queue_claim_item_check($queue_name) {
  if ($queue_name == ZVM_QUEUE_PRICE_CHANGE_UPDATE) {
    if (!lock_may_be_available('bos_product_list_update')) {
      return FALSE;
    }
  }

  return TRUE;
}
