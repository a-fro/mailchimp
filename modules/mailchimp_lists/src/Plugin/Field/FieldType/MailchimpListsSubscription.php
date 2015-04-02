<?php
/**
 * @file
 * Contains \Drupal\mailchimp_lists\Plugin\Field\FieldType\MailchimpListsSubscription.
 */

namespace Drupal\mailchimp_lists\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Plugin implementation of the 'mailchimp_lists_subscription' field type.
 *
 * @FieldType (
 *   id = "mailchimp_lists_subscription",
 *   label = @Translation("Mailchimp Subscription"),
 *   description = @Translation("Allows an entity to be subscribed to a Mailchimp list."),
 *   default_widget = "mailchimp_lists_select",
 *   default_formatter = "mailchimp_lists_subscribe_default"
 * )
 */
class MailchimpListsSubscription extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $columns = array(
      'mc_list_id' => array(
        'type' => 'varchar',
        'length' => 32,
        'not null' => FALSE,
      ),
      'double_opt_in' => array(
        'type' => 'int',
        'size' => 'tiny',
        'not null' => TRUE,
        'default' => 0,
      ),
      'send_welcome' => array(
        'type' => 'int',
        'size' => 'tiny',
        'not null' => TRUE,
        'default' => 0,
      ),
    );
    return array(
      'columns' => $columns,
    );
  }

  /**
   * {@inheritdoc}
   */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    $element = array();

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function storageSettingsForm(array &$form, FormStateInterface $form_state, $has_data) {
    $element = array();

    $lists = mailchimp_get_lists();
    $options = array('' => t('-- Select --'));
    foreach ($lists as $mc_list) {
      $options[$mc_list['id']] = $mc_list['name'];
    }
    // TODO: Get all subscription fields; check for assigned MC list IDs.
    /*
    $fields = field_info_fields();
    foreach ($fields as $field) {
      if ($field['type'] == 'mailchimp_lists_subscription') {
        if ($field['id'] != $this_field['id'] && isset($field['settings']['mc_list_id'])) {
          unset($options[$field['settings']['mc_list_id']]);
        }
      }
    }
    */

    $refresh_lists_url = Url::fromRoute('mailchimp_lists.refresh');
    $mailchimp_url = Url::fromUri('https://admin.mailchimp.com');

    $element['mc_list_id'] = array(
      '#type' => 'select',
      '#title' => t('MailChimp List'),
      '#multiple' => FALSE,
      '#description' => t('Available MailChimp lists which are not already
        attached to Mailchimp Subscription Fields. If there are no options,
        make sure you have created a list at !MailChimp first, then !cacheclear.',
        array(
          '!MailChimp' => \Drupal::l('MailChimp', $mailchimp_url),
          '!cacheclear' => \Drupal::l('clear your list cache', $refresh_lists_url),
        )),
      '#options' => $options,
      '#default_value' => !empty($this->getValue('mc_list_id')) ? $this->getValue('mc_list_id') : FALSE,
      '#required' => TRUE,
    );
    $element['double_opt_in'] = array(
      '#type' => 'checkbox',
      '#title' => 'Require subscribers to Double Opt-in',
      '#description' => 'New subscribers will be sent a link with an email they must follow to confirm their subscription.',
      '#default_value' => !empty($this->getValue('double_opt_in')) ? $this->getValue('double_opt_in') : FALSE,
    );
    $element['send_welcome'] = array(
      '#type' => 'checkbox',
      '#title' => 'Send a welcome email to new subscribers',
      '#description' => 'New subscribers will be sent a welcome email once they are confirmed.',
      '#default_value' => !empty($this->getValue('send_welcome')) ? $this->getValue('send_welcome') : FALSE,
    );

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['mc_list_id'] = DataDefinition::create('string')
      ->setLabel(t('MailChimp List'))
      ->setDescription(t('The MailChimp list attached to this field.'));
    $properties['double_opt_in'] = DataDefinition::create('integer')
      ->setLabel(t('Double Opt-in'))
      ->setDescription(t('Boolean. True when new subscribers must confirm their subscription.'));
    $properties['send_welcome'] = DataDefinition::create('integer')
      ->setLabel(t('Send Welcome Email'))
      ->setDescription(t('Boolean. True when new subscribers are sent a welcome email.'));
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value = $this->get('mc_list_id')->getValue();
    return $value === NULL || $value === '';
  }
}
