<?php

/**
 * Hybrid_Providers_GitLab - DigitalOcean provider adapter based on the OAuth2 protocol.
 */
class Hybrid_Providers_DigitalOcean extends Hybrid_Provider_Model_OAuth2
{
	// default permissions
	// (no scope) => public read-only access (includes public user profile info, public repo info, and gists).
	public $scope = "api";

	/**
	* IDp wrappers initializer
	*/
	function initialize()
	{
		parent::initialize();

		// Provider api end-points
		$this->api->api_base_url  = "https://cloud.digitalocean.com";
		$this->api->authorize_url = "https://cloud.digitalocean.com/v1/oauth/authorize";
		$this->api->token_url     = "https://cloud.digitalocean.com/v1/oauth/token";
	}

	/**
	* load the user profile from the IDp api client
	*/
	function getUserProfile()
	{
		$data = $this->api->api( "v2/account" );

		if ( ! isset( $data->id ) ){
			throw new Exception( "User profile request failed! {$this->providerId} returned an invalid response.", 6 );
		}

		$this->user->profile->identifier  = @ $data->uuid;
		$this->user->profile->displayName = @ $data->email; // No display name value from the API so we use email
		$this->user->profile->email       = @ $data->email;
		$this->user->profile->region      = @ $data->location;

    // Digital ocean returns a flag marking the email as verified or not
    // We compare this to the email in use and set the emailVerified
    // value accodringly.
    if (isset($data->email_verified) and TRUE == $data->email_verified and $data->email == $data->email_verified) {
      $this->user->profile->emailVerified = $data->email;
    }

		return $this->user->profile;
	}
}
