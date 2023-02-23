<?php

namespace Drupal\players_cfg\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use MailchimpMarketing;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Players Inc mailchimp.
 */
class PlayersSiteMailchimpForm extends FormBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(
    EntityTypeManagerInterface $entityTypeManager
  ) {

    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {

    // Instantiates this form class.
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'players_site_mailchimp';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    global $config;

    // Setup the client for mailchimp.
    $client = new MailchimpMarketing\ApiClient();
    $client->setConfig([
      'apiKey' => $config['mailchimp_api_key'],
      'server' => $config['mailchimp_server'],
    ]);

    // Get the list.
    $response = $client->lists->getList($config['mailchimp_list_id']);

    // Set the markup to be used for mailchimp stats.
    $markup = '<h2>Audience Stats</h2>';
    $markup .= 'Number of <a href="https://us10.admin.mailchimp.com/lists/members/">members</a>: ' . $response->stats->member_count . '<br />';
    $markup .= 'Number of <a href="https://us10.admin.mailchimp.com/campaigns">campaigns</a>: ' . $response->stats->campaign_count . '<br />';

    // Mailchimp stats form element.
    $form['stats'] = [
      '#type' => 'markup',
      '#markup' => $markup,
    ];

    // Submit buttons.
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Update mailchimp list'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    // Get all the users.
    $users = $this->entityTypeManager->getStorage('user')->loadMultiple();

    // Rmeove user 0 and user 1.
    unset($users[0]);
    unset($users[1]);
    unset($users[2]);

    // Setup the batch.
    $batch = [
      'title' => $this->t('Updating mailchimp list ...'),
      'operations' => [],
      'finished' => '_players_cfg_update_mailchimp_finished',
    ];

    // Get the number of sections to use.
    $sections = (int)(count($users) / 10);

    // Step through and add to operations.
    for ($i = 0; $i <= $sections; $i++) {
      $u = array_slice($users, $i * 10, 10);
      $batch['operations'][] = ['_players_cfg_update_mailchimp', [$u, count($users)]];
    }

    // Set the batch.
    batch_set($batch);
  }

}
