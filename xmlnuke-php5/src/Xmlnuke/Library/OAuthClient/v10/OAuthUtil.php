<?php
// vim: foldmethod=marker

namespace OAuthClient\v10;

class OAuthUtil {/*{{{*/
  public static function urlencode_rfc3986($input) {/*{{{*/
  if (is_array($input)) {
    return array_map(array('OAuthUtil','urlencode_rfc3986'), $input);
  } else if (is_scalar($input)) {
    return str_replace('+', ' ',
                         str_replace('%7E', '~', rawurlencode($input)));
  } else {
    return '';
  }
  }/*}}}*/


  // This decode function isn't taking into consideration the above
  // modifications to the encoding process. However, this method doesn't
  // seem to be used anywhere so leaving it as is.
  public static function urldecode_rfc3986($string) {/*{{{*/
    return rawurldecode($string);
  }/*}}}*/
}/*}}}*/