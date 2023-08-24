<?php

namespace Drupal\players_cfg\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure header text for this site.
 */
class PlayersSiteHeaderTextForm extends ConfigFormBase {

  /**
   * Config settings.
   *
   * @var string
   */
  const SETTINGS = 'players_cfg.header_text';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'players_site_header_text';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    // Get the config.
    $config = $this->config(static::SETTINGS);

    // Need this for the color picker.
    $form['#prefix'] = '<div class="form-type-color-picker">';
    $form['#suffix'] = '</div>';

    // Adding the ability to do ajax forms.
    $form['#tree'] = TRUE;

    // Setup the add_more_called flag, so that we know
    // which of the items to load.
    // If we even do one add or remove, we set this flag
    // so that the form will load the values from the
    // form_state, if flag is not set, it will load
    // from the block config.
    if (empty($form_state->get('add_more_called'))) {
      $add_more_called = FALSE;
      $form_state->set('add_more_called', $add_more_called);
    }
    else {
      $add_more_called = $form_state->get('add_more_called');
    }

    // If the add_more_flag is not set, we load from the
    // block config.  If it is set, we load from the form_state.
    if (!$add_more_called) {
      $marketing_items = $config->get('marketing_items') ?? NULL;
      $form_state->set('marketing_items', $marketing_items);
    }
    else {
      $marketing_items = $form_state->get('marketing_items');
    }

    // Get the num_of_rows from the form_state.
    $num_of_rows = $form_state->get('num_of_rows');

    // If the num_of_rows is not set, we first look at
    // the block config and see if we have events, and if
    // so set num_of_rows to the number of events.  If we
    // do not have num_of_rows set, default to 1 (this is
    // the first load of the form).
    if (empty($num_of_rows)) {
      if (isset($marketing_items)) {
        $num_of_rows = count($marketing_items);
      }
      else {
        $num_of_rows = 1;
      }
    }

    // Set the num_of_rows to the form_state so that we
    // can use it in the ajax calls.
    $form_state->set('num_of_rows', $num_of_rows);

    // The details to hold the events items.
    $form['items_fieldset'] = [
      '#type' => 'details',
      '#title' => $this->t('Marketing items'),
      '#open' => TRUE,
      '#prefix' => '<div id="items-wrapper">',
      '#suffix' => '</div>',
    ];

    // The class to be used for groups.
    $group_class = 'group-order-weight';

    // Build the table.
    $form['items_fieldset']['items'] = [
      '#type' => 'table',

      '#header' => [
        [
          'data' => $this->t('<h4 class="label form-required">Add Marekting Items</h4>'),
          'colspan' => 2,
        ],
        // @todo Make this work properly with the remove button.
        // phpcs:disable
        // '',
        // phpcs:enable
        $this->t('Weight'),
      ],
      '#tableselect' => FALSE,
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => $group_class,
        ],
      ],
      '#prefix' => '<div class="pi-marketing-items__table">',
      '#suffix' => '</div><p>Leave a marketing item blank to remove it.</p>',
    ];

    // Step through and events item.
    for ($i = 0; $i < $num_of_rows; $i++) {

      // Reset the settings array.
      $settings = [];

      // Set the table to be draggable.
      $settings['#attributes']['class'][] = 'draggable';

      // Set the weight.
      $settings['#weight'] = $i;

      // The first column of the table, that will house the arrows
      // for rearranging events urls.
      $settings['arrow'] = [
        '#type' => 'markup',
        '#markup' => '',
        '#title_display' => 'invisible',
      ];

      $settings['item'] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Item'),
      ];

      // The link element.
      $settings['item']['image'] = [
        '#type' => 'media_library',
        '#allowed_bundles' => ['icon'],
        '#title' => $this->t('Marketing item icon'),
        '#default_value' => $marketing_items[$i]['image'] ?? '',
      ];

      // The heading for the marketing item.
      $settings['item']['heading'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Marketing item heading'),
        '#default_value' => $marketing_items[$i]['heading'] ?? '',
      ];

      // The text for the marketing item.
      $settings['item']['text'] = [
        '#type' => 'text_format',
        '#title' => $this->t('Marketing item text'),
        '#default_value' => $marketing_items[$i]['text']['value'] ?? '',
      ];

      // The url for the marketing item.
      $settings['item']['url'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Marketing item URL'),
        '#default_value' => $marketing_items[$i]['url'] ?? '',
      ];

      // The weight element.
      $settings['weight'] = [
        '#type' => 'weight',
        '#title_display' => 'invisible',
        '#default_value' => $i,
        '#attributes' => ['class' => [$group_class]],
      ];

      // Add the settings to the items array, which is full row
      // in the table.
      $form['items_fieldset']['items'][] = $settings;
    }

    // The add expand/collapse group button.
    $form['items_fieldset']['add_group'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add Marketing Item'),
      '#submit' => [[$this, 'marketingItemAddOne']],
      '#ajax' => [
        'callback' => [$this, 'marketingItemCallback'],
        'wrapper' => 'items-wrapper',
      ],
      '#attributes' => [
        'class' => ['button--large'],
      ],
    ];

    // The details to hold the events items.
    $form['heading_fieldset'] = [
      '#type' => 'details',
      '#title' => $this->t('Header text'),
      '#open' => TRUE,
    ];

    // The text element.
    $form['heading_fieldset']['header_text'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Header text'),
      '#cols' => 60,
      '#rows' => 5,
      '#format' => $config->get('header_text')['format'] ?? 'full_html',
      '#default_value' => $config->get('header_text')['value'] ?? NULL,
    ];

    // Details for the background color.
    $form['heading_fieldset']['color'] = [
      '#type' => 'details',
      '#title' => $this->t('Background Color'),
      '#open' => TRUE,
    ];

    // The background color.
    $form['heading_fieldset']['color']['bg_color'] = [
      '#type' => 'color_picker',
      '#title' => $this->t('Background Colour'),
      '#description' => $this->t('Select the background colour.'),
      '#default_value' => $config->get('bg_color') ?? '#FFFFFF',
      '#color_values' => _players_cfg_colours(),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    // Get the values from the form state.
    $values = $form_state->getValues();

    $marketing_items = [];

    foreach ($values['items_fieldset']['items'] as $item) {
      $marketing_items[] = [
        'image' => $item['item']['image'],
        'heading' => $item['item']['heading'],
        'text' => $item['item']['text'],
        'url' => $item['item']['url'],
      ];
    }

    // Set the config.
    $this->config(static::SETTINGS)
      ->set('header_text', $values['heading_fieldset']['header_text'] ?? NULL)
      ->set('bg_color', $values['heading_fieldset']['color']['bg_color'] ?? NULL)
      ->set('marketing_items', $marketing_items)
      ->save();

    // Clear all caches so the new site settings up.
    drupal_flush_all_caches();

    parent::submitForm($form, $form_state);
  }

  /**
   * Add one more marketing item to the form.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function marketingItemAddOne(array &$form, FormStateInterface $form_state) {

    // Increase by 1 the number of rows and set it in
    // the form_state.
    $num_of_rows = $form_state->get('num_of_rows');
    $num_of_rows++;
    $form_state->set('num_of_rows', $num_of_rows);

    // Set the add_more_called flag, so that we will load
    // the items by form_state, rather than block config.
    $form_state->set('add_more_called', TRUE);

    // Get the current values of the form_state.
    $values = $form_state->getValues();

    // Get the items from the form state, which will contain
    // the events settings.
    $items = $values['items_fieldset']['items'];

    $marketing_items = [];

    // Step through each of the items and set values to be used
    // in the events block config.
    foreach ($items as $item) {

      $marketing_items['items'][] = [
        'image' => $item['item']['image'],
        'heading' => $item['item']['heading'],
        'text' => $item['item']['text'],
        'url' => $item['item']['url'],
      ];
    }

    // Set the events from the items in the form_state, so that we
    // will load newly added values.
    $form_state->set('marketing_items', $marketing_items);

    // Rebuild form with 1 extra row.
    $form_state->setRebuild();
  }

  /**
   * The ajax call back for marketing item add one.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return mixed
   *   The form element to be overwritten.
   */
  public function marketingItemCallback(array &$form, FormStateInterface $form_state) {
    return $form['items_fieldset'];
  }

}
