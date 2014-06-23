<?php
// vim: foldmethod=marker

namespace OAuthClient\v10;

class OAuthSignatureMethod {/*{{{*/
  public function check_signature(&$request, $consumer, $token, $signature) {
    $built = $this->build_signature($request, $consumer, $token);
    return $built == $signature;
  }
}/*}}}*/

