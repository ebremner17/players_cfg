<?php

/**
 * @file
 * Contains \Drupal\players_cfg\Controller\direct_clear_cache.
 */

namespace Drupal\players_cfg\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class DirectClearCache.
 *
 * Class to clear cache.
 */
class DirectClearCache extends ControllerBase {

  public function content() {

    // Get the previous url.
    $previousUrl = \Drupal::request()->server->get('HTTP_REFERER');

    // Clear the caches.
    drupal_flush_all_caches();

    // Set the message.
    \Drupal::messenger()->addMessage(
      $this->t('Caches cleared successfully')
    );

    // Redirect back to the previous url.
    return new RedirectResponse($previousUrl);
  }
}
