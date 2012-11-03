HelloSign-PHP
=============

PHP Wrapper for Hello Sign API.


Example usage:

    <?php
    require_once '/path/to/hellosign.php';
    
    $hs = new \HelloSign\API('user@example.com', 'password');
    
    try {
        $response = $hs->api('account');
        
        if (!$response->containsError()) {
            var_dump($response->getResponse());
        } else {
            echo $response->getError();
        }
    } catch (\HelloSign\Exception $e) {
        echo $e->getMessage();
    }

NOTE: Hello Sign API is in its infancy, expect changes.
I am seeing things that I imagine they will rework/rethink ... such as:

 * Using 0 based index for signers, but 1 based index for files. Consistency please.
 * Creating user accounts for people instead of invitations. Really?
   - Results in vague 'emma' e-mails talking about hellofax
   - This should be ```account/invite``` that gives back a token/statement that account already exists.
     Invitation to the email address to create an account with a password that THEY set.
 * Some poor semantics for API e.g. ```/team/destroy``` vs ```/team/delete```, add_member vs add_user (consistency),
   cc_email_addresses vs ccs
 * Odd API argument ordering e.g. ```/reusable_form/add_user/[:reusable_form_id]```
   This can cause confusing, thinking that the form_id is the user id.
   Something more along the lines of ```/reusable_form/[:reusable_form_id]/add_user/[:user_id_or_email]``` 
   would seem more logical.
