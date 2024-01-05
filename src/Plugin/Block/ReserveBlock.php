<?php

namespace Drupal\players_cfg\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\players_reserve\Service\PlayersService;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Copy text block.
 *
 * @Block(
 *   id = "cbl_reserve",
 *   admin_label = @Translation("Reserve"),
 * )
 */
class ReserveBlock extends BlockBase implements ContainerFactoryPluginInterface {

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
    PlayersService $playersService
  ) {

    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->playersService = $playersService;
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
      $container->get('players_reserve.players_service')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    // Get the correct date.
    $date = $this->playersService->getCorrectDate();

    // Get the node based on the current date.
    $node = $this->playersService->getGameNodeByDate($date);

    // Return custom template with variable.
    return [
      '#theme' => 'cbl_reserve',
      '#reserve' => $this->playersService->getGameInfo($node),
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

