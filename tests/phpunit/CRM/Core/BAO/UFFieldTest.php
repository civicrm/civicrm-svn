<?php
require_once 'CiviTest/CiviUnitTestCase.php';
require_once 'CiviTest/Contact.php';
class CRM_Core_BAO_UFFieldTest extends CiviUnitTestCase {

  function setUp() {
    parent::setUp();

    $this->quickCleanup(array('civicrm_uf_group', 'civicrm_uf_field'));
  }

  public function testGetAvailable_byGid() {
    $ufGroupId = $this->createUFGroup(array(
      array(
        'field_name' => 'do_not_sms',
        'field_type' => 'Contact',
      ),
      array(
        'field_name' => 'first_name',
        'field_type' => 'Individual',
      ),
    ));
   $fields = CRM_Core_BAO_UFField::getAvailableFields($ufGroupId);

    // 1a. Make sure that each entity appears with at least one field
    $this->assertFalse(isset($fields['Contact']['do_not_sms']));
    $this->assertEquals('city', $fields['Contact']['city']['name']);
    $this->assertFalse(isset($fields['Individual']['first_name']));
    $this->assertEquals('birth_date', $fields['Individual']['birth_date']['name']);
    $this->assertEquals('organization_name', $fields['Organization']['organization_name']['name']);
    $this->assertEquals('amount_level', $fields['Contribution']['amount_level']['name']);
    $this->assertEquals('participant_note', $fields['Participant']['participant_note']['name']);
    $this->assertEquals('join_date', $fields['Membership']['join_date']['name']);
    $this->assertEquals('activity_date_time', $fields['Activity']['activity_date_time']['name']);

    // 1b. Make sure that some of the blacklisted fields don't appear
    $this->assertFalse(isset($fields['Contribution']['is_pay_later']));
    $this->assertFalse(isset($fields['Participant']['participant_role_id']));
    $this->assertFalse(isset($fields['Membership']['membership_type_id']));
  }

  public function testGetAvailable_full() {
    $fields = CRM_Core_BAO_UFField::getAvailableFields();

    // Make sure that each entity appears with at least one field
    $this->assertEquals('do_not_sms', $fields['Contact']['do_not_sms']['name']);
    $this->assertEquals('city', $fields['Contact']['city']['name']);
    $this->assertEquals('first_name', $fields['Individual']['first_name']['name']);
    $this->assertEquals('birth_date', $fields['Individual']['birth_date']['name']);
    $this->assertEquals('organization_name', $fields['Organization']['organization_name']['name']);
    $this->assertEquals('amount_level', $fields['Contribution']['amount_level']['name']);
    $this->assertEquals('participant_note', $fields['Participant']['participant_note']['name']);
    $this->assertEquals('join_date', $fields['Membership']['join_date']['name']);
    $this->assertEquals('activity_date_time', $fields['Activity']['activity_date_time']['name']);

    // Make sure that some of the blacklisted fields don't appear
    $this->assertFalse(isset($fields['Contribution']['is_pay_later']));
    $this->assertFalse(isset($fields['Participant']['participant_role_id']));
    $this->assertFalse(isset($fields['Membership']['membership_type_id']));
  }

  /**
   * Make sure that the existence of a profile doesn't break listing all fields
   *
  public function testGetAvailable_mixed() {
    FIXME
    $this->testGetAvailable_full();
    $this->testGetAvailable_byGid();
    $this->testGetAvailable_full();
    $this->testGetAvailable_byGid();
  }*/

  /**
   * @param array $fields list of fields to include in the profile
   * @return int field id
   */
  protected function createUFGroup($fields) {
    $ufGroup = CRM_Core_DAO::createTestObject('CRM_Core_DAO_UFGroup');
    $this->assertTrue(is_numeric($ufGroup->id));

    foreach ($fields as $field) {
      $defaults = array(
        'version' => 3,
        'uf_group_id' => $ufGroup->id,
        'visibility' => 'Public Pages and Listings',
        'weight' => 1,
        'label' => 'Label for ' . $field['field_name'],
        'is_searchable' => 1,
        'is_active' => 1,
        'location_type_id' => NULL,
      );
      $params = array_merge($field, $defaults);
      $ufField = civicrm_api('UFField', 'create', $params);
      $this->assertAPISuccess($ufField);
    }

    return $ufGroup->id;
  }
}
