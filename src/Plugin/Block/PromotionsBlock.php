<?php

namespace Drupal\players_cfg\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\media\Entity\Media;
use Drupal\players_reserve\Service\PlayersService;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Promotions block.
 *
 * @Block(
 *   id = "cbl_promotions_block",
 *   admin_label = @Translation("Promotions"),
 * )
 */
class PromotionsBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The players service.
   *
   * @var \Drupal\players_reserve\Service\PlayersService
   */
  protected $playersService;

  /**
   * Constructs a BlockComponentRenderArray object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\players_reserve\Service\PlayersService $playersService
   *   The players service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    PlayersService $playersService,
    EntityTypeManager $entityTypeManager
  ) {

    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->playersService = $playersService;
    $this->entityTypeManager = $entityTypeManager;
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
      $plugin_definition,
      $container->get('players_reserve.players_service'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    // Get the header text config.
    $config = \Drupal::config('players_cfg.header_text');

    // Get the marketing items.
    $mis = $config->get('marketing_items');

    // If there are marketing items, add to the variables.
    if ($mis) {

      $marketing_items = [];

      if ($mis) {
        foreach ($mis as $mi) {

          if ($mi['image']) {
            $media = Media::load($mi['image']);

            if ($media && $media->hasField('field_media_image_2')) {
              $fid = $media->field_media_image_2[0]->getValue()['target_id'];

              $file = File::load($fid);

              $uri = $file->getFileUri();

              $image = \Drupal::service('file_url_generator')->generateAbsoluteString($uri);
            }
            else {
              $image = NULL;
            }
          }
          else {
            $media = NULL;
          }

          if ($mi['icon']) {
            $media = Media::load($mi['icon']);

            $fid = $media->field_media_image_1[0]->getValue()['target_id'];

            $file = File::load($fid);

            $uri = $file->getFileUri();

            $icon = \Drupal::service('file_url_generator')->generateAbsoluteString($uri);
          }
          else {
            $media = NULL;
          }

          $marketing_items[] = [
            'image' => $image,
            'icon' => $icon,
            'heading' => $mi['heading'],
            'text' => [
              '#type' => 'processed_text',
              '#text' => $mi['text']['value'],
              '#format' => $mi['text']['format'],
            ],
            'url' => $mi['url'],
            'color' => $mi['color'],
          ];
        }
      }
    }

    // Return custom template with variable.
    return [
      '#theme' => 'cbl_promotions',
      '#marketing_items' => $marketing_items,
      '#attached' => [
        'library' => [
          'players_theme/owl',
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {

  }

}

