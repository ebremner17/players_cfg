<?php

namespace Drupal\players_cfg\Plugin\Block;

use DateInterval;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Gallery block.
 *
 * @Block(
 *   id = "cbl_gallery",
 *   admin_label = @Translation("Gallery"),
 * )
 */
class GalleryBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Constructs a BlockComponentRenderArray object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactory $configFactory
   *   The config factory.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {

    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {

    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition
    );
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm(
    $form,
    FormStateInterface $form_state
  ): array {

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {

    $now = date('Y-m-d', strtotime("today"));
    $week = date('Y-m-d', strtotime("-1 week"));
    $query = \Drupal::entityQuery('node')
      ->condition('type', 'pi_ct_gallery') // Replace with your content type machine name.
      ->condition('status', 1) // Published
      ->condition('field_gallery_date', $week, '>=')
      ->condition('field_gallery_date', $now, '<=');
    $nids = $query->execute();

    $nodes = Node::loadMultiple($nids);

    foreach ($nodes as $node) {

      $aaa = $node->field_gallery_media->entity;
      $media_items[] = \Drupal::entityTypeManager()->getViewBuilder('media')->view($aaa);
    }

    // Return custom template with variable.
    return [
      '#theme' => 'cbl_gallery',
      '#media_items' => $media_items,
    ];
  }
}
