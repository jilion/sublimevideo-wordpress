<?php
require 'vendor/PHP-OAuth2/client.php';

class SublimeVideoAPI {

  public $api_endpoint;
  public $options;
  public $client;
  public $sites;
  public $code;

  # DON'T CHANGE THAT!
  const OAUTH_CONSUMER_KEY    = 'eYD6r4OwfwfvmsjGDIZGzhfPzmoHu7hXNA4LQxVb';
  const OAUTH_CONSUMER_SECRET = '36S9c4fFd8G4SBOsv9j3ymUeHBk824CJ8hdjpfnc';
  # DON'T CHANGE THAT!

  const OAUTH_PROVIDER     = 'https://my.sublimevideo.net';
  const API_ENDPOINT       = 'https://api.sublimevideo.net';
  const AUTHORIZATION_PATH = '/oauth/authorize';
  const TOKEN_PATH         = '/oauth/access_token';

  static $default_options = array('api_version' => 1, 'content_type' => 'json');

  public function __construct($options=array()) {
    $this->oauth_token    = get_option('sv_oauth_token');
    $this->oauth_provider = get_option('sv_dev_oauth_provider') ? get_option('sv_dev_oauth_provider') : self::OAUTH_PROVIDER;
    $this->api_endpoint   = get_option('sv_dev_api_endpoint') ? get_option('sv_dev_api_endpoint') : self::API_ENDPOINT;
    $this->options        = array_merge(self::$default_options, $options);
    $this->sites          = null;
    $this->client         = new OAuth2Client(
      get_option('sv_dev_oauth_consumer_key') ? get_option('sv_dev_oauth_consumer_key') : self::OAUTH_CONSUMER_KEY,
      get_option('sv_dev_oauth_consumer_secret') ? get_option('sv_dev_oauth_consumer_secret') : self::OAUTH_CONSUMER_SECRET, OAuth2Client::AUTH_TYPE_FORM
    );
    $this->client->setAccessTokenType(OAuth2Client::ACCESS_TOKEN_OAUTH);
    $this->client->setAccessToken($this->oauth_token);
  }

  public function authorizationUrl($redirect_uri) {
    return $this->client->getAuthenticationUrl($this->oauth_provider.self::AUTHORIZATION_PATH, $redirect_uri);
  }

  public function accessToken($params) {
    $response = $this->client->getAccessToken($this->oauth_provider.self::TOKEN_PATH, OAuth2Client::GRANT_TYPE_AUTH_CODE, $params);
    return $response['body'];
  }

  public function sites($refresh=false) {
    if (!$this->sites || $refresh) {
      $response = $this->client->fetch($this->api_endpoint.'/sites', array(), OAuth2Client::HTTP_METHOD_GET, $this->http_headers());

      if (isset($response['body']) && isset($response['body']->sites)) {
        $this->sites = $response['body']->sites;
      }
      else {
        $this->sites = null;
      }
      $this->code = $response['code'];
    }
    return $this->sites;
  }

  public function http_headers() {
    global $wp_version;

    return array(
      'User-Agent' => "WordPress/{$wp_version} | SublimeVideo/".SUBLIMEVIDEO_PLUGIN_VERSION,
      'Accept' => 'application/vnd.sublimevideo-v'.$this->options['api_version'].'+'.$this->options['content_type']
    );
  }

}

?>