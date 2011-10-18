<?php

###########################################
# OAuth Client 1.0
###########################################

# OAuth Client 1.0 Flow Implementation
require_once(PHPXMLNUKEDIR . "bin/com.xmlnuke/net.oauthclient10.class.php");

# OAuth Core Logic
require_once(PHPXMLNUKEDIR . 'bin/modules/oauthclient/10/oauth.class.php');

# OAuth Logic
require_once(PHPXMLNUKEDIR . 'bin/modules/oauthclient/10/baseoauth.class.php');

# OAuth Specific Client Configuration
require_once(PHPXMLNUKEDIR . 'bin/modules/oauthclient/10/twitteroauth.class.php');

###########################################
# OAuth Client 2.0
###########################################

# OAuth Client 2.0 Flow Implementation
require_once(PHPXMLNUKEDIR . "bin/com.xmlnuke/net.oauthclient20.class.php");

# OAuth Logic
require_once(PHPXMLNUKEDIR . 'bin/modules/oauthclient/20/baseoauth20.class.php');

# OAuth Specific Client Configuration
require_once(PHPXMLNUKEDIR . 'bin/modules/oauthclient/20/facebookoauth20.class.php');


?>