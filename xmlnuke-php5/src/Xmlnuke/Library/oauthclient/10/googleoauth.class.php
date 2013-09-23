<?php

/**
 * Twitter OAuth class
 */
class GoogleOAuth extends baseOAuth {/*{{{*/
  /* Set up the API root URL */
  public static $TO_API_ROOT = "https://www.google.com";

  /**
   * Set API URLS
   */
  function requestTokenURL() { return self::$TO_API_ROOT.'/accounts/OAuthGetRequestToken'; }
  function authorizeURL() { return self::$TO_API_ROOT.'/accounts/OAuthAuthorizeToken'; }
  function accessTokenURL() { return self::$TO_API_ROOT.'/accounts/OAuthGetAccessToken'; }

}/*}}}*/
?>