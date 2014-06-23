<?php

namespace OAuthClient\v20;

use Exception;


/**
 * GoogleOAuth20 is an OAuth 2.0 client implementation
 * More information can be found at https://developers.google.com/accounts/docs/OAuth2
 *
 * @author jg
 */
class GoogleOAuth20 extends BaseOAuth20
{
	
	public function authorizationURL() { return "https://accounts.google.com/o/oauth2/auth"; }

	public function accessTokenURL() { return "https://accounts.google.com/o/oauth2/token"; }
	
	public function validateRequest($result) {
		$statusCode = trim(parent::validateRequest($result));
		
		if ($statusCode == '200')
			return $result;
		else
		{
			if ($result != "")
			{
				$obj = json_decode($result);
				$statusCode .= ": " . $obj->error->message;
			}
			throw new Exception($statusCode . "\n\n" . $result);
		}
	}
	
	protected function preparedUrl($url) {
		return parent::preparedUrl($this->GRAPH_API . $url);
	}
	
	public function getData($objectId = "me", $params = null)
	{
		return json_decode($this->get(($objectId[0] != "/" ? "/" : "") . $objectId, $params));
	}
	
	public function publishData($objectId = "me", $type = "", $params = null)
	{
		return json_decode($this->post(($objectId[0] != "/" ? "/" : "") . $objectId . "/" . $type, $params));
	}
}

class GoogleScope20
{
	const AdsenseManagement = "https://www.googleapis.com/auth/adsense";
	const GoogleAffiliateNetwork = "https://www.googleapis.com/auth/gan";
	const Analytics = "https://www.googleapis.com/auth/analytics.readonly";
	const GoogleBooks = "https://www.googleapis.com/auth/books";
	const Blogger = "https://www.googleapis.com/auth/blogger";
	const Calendar = "https://www.googleapis.com/auth/calendar";
	const GoogleCloudStorage = "https://www.googleapis.com/auth/devstorage.read_write";
	const Contacts = "https://www.google.com/m8/feeds/";
	const ContentAPIforShopping = "https://www.googleapis.com/auth/structuredcontent";
	const ChromeWebStore = "https://www.googleapis.com/auth/chromewebstore.readonly";
	const DocumentsList = "https://docs.google.com/feeds/";
	const GoogleDrive = "https://www.googleapis.com/auth/drive.file";
	const Gmail = "https://mail.google.com/mail/feed/atom";
	const GooglePlus = "https://www.googleapis.com/auth/plus.me";
	const GroupsProvisioning = "https://apps-apis.google.com/a/feeds/groups/";
	const GoogleLatitude = "https://www.googleapis.com/auth/latitude.all.best+https://www.googleapis.com/auth/latitude.all.city";
	const Moderator = "https://www.googleapis.com/auth/moderator";
	const NicknamesProvisioning = "https://apps-apis.google.com/a/feeds/alias/";
	const Orkut = "https://www.googleapis.com/auth/orkut";
	const PicasaWeb = "https://picasaweb.google.com/data/";
	const Sites = "https://sites.google.com/feeds/";
	const Spreadsheets = "https://spreadsheets.google.com/feeds/";
	const Tasks = "https://www.googleapis.com/auth/tasks";
	const URLShortener = "https://www.googleapis.com/auth/urlshortener";
	const UserinfoEmail = "https://www.googleapis.com/auth/userinfo.email";
	const UserinfoProfile = "https://www.googleapis.com/auth/userinfo.profile";
	const UserProvisioning = "https://apps-apis.google.com/a/feeds/user/";
	const WebmasterTools = "https://www.google.com/webmasters/tools/feeds/";
	const YouTube = "https://gdata.youtube.com";
}

?>
