<?php
/*
 * Abraham Williams (abraham@abrah.am) http://abrah.am
 *
 * Basic lib to work with Twitter's OAuth beta. This is untested and should not
 * be used in production code. Twitter's beta could change at anytime.
 *
 * Code based on:
 * Fire Eagle code - http://github.com/myelin/fireeagle-php-lib
 * twitterlibphp - http://github.com/poseurtech/twitterlibphp
 */

/* Load OAuth lib. You can find it at http://oauth.net */

/**
 * Twitter OAuth class
 */
class TwitterOAuth extends baseOAuth {/*{{{*/
  /* Set up the API root URL */
  public static $TO_API_ROOT = "https://twitter.com";

  /**
   * Set API URLS
   */
  function requestTokenURL() { return self::$TO_API_ROOT.'/oauth/request_token'; }
  function authorizeURL() { return self::$TO_API_ROOT.'/oauth/authorize'; }
  function accessTokenURL() { return self::$TO_API_ROOT.'/oauth/access_token'; }

}/*}}}*/