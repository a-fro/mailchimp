<?php

/**
 * @file
 * Contains Drupal\mailchimp_campaign\Tests\MailchimpCampaignTestBase.
 */

namespace Drupal\mailchimp_campaign\Tests;

use Drupal\mailchimp_campaign_test\MailchimpCampaignConfigOverrider;
use Drupal\simpletest\WebTestBase;

/**
 * Sets up MailChimp Campaign module tests.
 */
abstract class MailchimpCampaignTestBase extends WebTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    // Use a profile that contains required modules:
    $this->profile = $this->originalProfile;

    parent::setUp();

    \Drupal::configFactory()->addOverride(new MailchimpCampaignConfigOverrider());
  }

}
