<?php
/**
 * @file
 * A form to collect an email adress for RSVP details.
 */
namespace Drupal\rsvplist\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class RSVPForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    // TODO: Implement getFormId() method.
    return 'rsvplist_email_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    // Attempt to get the fully loaded node object of the viewed page.
    $node = \Drupal::routeMatch()->getParameter('nade');
    /**
     * Some page may not be nodes though and $node wil be NULL on those pages.
     * if a node was loaded, get the node id.
     *
     */
    if ( !(is_null($node)) ) {
      $nid = $node->id();
    }
    else {
      // id a node could not be loaded, default to 0;
      $nid = 0;
    }
    /**
     * Establish th $form render array.
     * It has an email text field, a submit button, and hidden field containing the node ID.
     */
      $form['email'] = [
        '#type' => 'textfield',
        '#title' => t('Email adress'),
        '#size' => 25,
        '#description' => t('We will send update to the email adress you provide.'),
        '#required' => TRUE,
      ];
      $form['submit'] = [
        '#type' => 'submit',
        '#value' => t('RSVP'),
      ];
      $form['nid'] = [
        '#type' => 'hidden',
        '#value' => $nid,
      ];
      return $form;
  }
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $email = $form_state->getValue("email");
    // Get the email validator service
    $email_validator = \Drupal::service('email.validator');
    if (!$email_validator->isValid($email)) {
      $form_state->setErrorByName('email', $this->t('It appears that %email is not valid email. Please try again', [ '%email' => $email ]));
    }
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    //    $submitted_email = $form_state->getValue('email');
    //    $submitted_nid  = $form_state->getValue('nid');
    //    $this->messenger()->addMessage(t('The form is working! You entered @entry and node id is @nid ',
    //      ['@entry' => $submitted_email,
    //        '@nid' => $submitted_nid
    //      ])
    //    );
    try {
      // Begin phase 1: initaite variables to save.

      //Get current user ID.
      $uid = \Drupal::currentUser()->id();

      // Demonstration for how to load a full user object of the current user.
      // this $full_user variable is not needed for this code,
      // but is show for demondtration purposes.
      $full_user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());

      //obtain values as entered into the form.
      $email = $form_state->getValue('email');
      $nid  = $form_state->getValue('nid');
      $current_time = \Drupal::time()->getRequestTime();
      // End Phase 1

      // Begin Phase 2 : save the value to the database

      //start to build a query builder object  $query
      $query = \Drupal::database()->insert('rsvplist');

      //specify the fields that the query wil insert into.
      $query->fields([
        'uid',
        'nid',
        'mail',
        '*created',
      ]);
      // Set the values of the fields we selected.
      // Note that they way must be in the same order as we defined them
      // in the $query->fields above.
      $query->values([
        $uid,
        $nid,
        $email,
        $current_time,
      ]);
      //Execute the query!
      //Drupal handler the exact syntax of the query automatically!
      $query->execute();
      // End Phase 2

      // Phase 3 : Display a success message

      // Provide the form submit a noce message.
      \Drupal::messenger()->addMessage(
        t('thank you for your RSVP, you are on the list for the event!')
      );
      // End Phase 3
    }
    catch (\Exception $e) {
      $this->messenger()->addMessage(
        t('Unable to save RSVP settings at this time due to database error.
        Please try again.')
      );
    }
  }

}
