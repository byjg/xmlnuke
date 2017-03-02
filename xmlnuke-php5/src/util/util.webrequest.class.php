<?php

/**
 * Class to abstract Soap and REST calls
 * @author jg
 *
 */

class WebRequest
{

    protected $url;
    protected $requestUrl;
    protected $soapClass = null;
    protected $requestHeader = array();
    protected $responseHeader = null;
    protected $cookies = array();
    protected $lastStatus = "";
    protected $curlOptions = array();

    /**
     * Constructor
     *
     * @param string $url
     * @param array $curlOptions Array of CURL Options
     */
    public function __construct($url, $curlOptions = null)
    {
        $this->url = $url;
        $this->requestUrl = $url;

        $this->defaultCurlOptions();
        if (is_array($curlOptions)) {
            foreach ($curlOptions as $key => $value) {
                $this->setCurlOption($key, $value);
            }
        }
    }

    /**
     * Defines Basic credentials for access the service.
     *
     * @param string $username
     * @param string $password
     */
    public function setCredentials($username, $password)
    {
        $this->setCurlOption(CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        $this->setCurlOption(CURLOPT_USERPWD, $username . ":" . $password);
    }

    /**
     * Get the current CURLOPT_REFERER
     *
     * @return string
     */
    public function getReferer()
    {
        return $this->getCurlOption(CURLOPT_REFERER);
    }

    /**
     * Set the CURLOPT_REFERER
     *
     * @param string $value
     */
    public function setReferer($value)
    {
        $this->setCurlOption(CURLOPT_REFERER, $value);
    }

    /**
     * Get the status of last request (get, put, delete, post)
     *
     * @return integer
     */
    public function getLastStatus()
    {
        return $this->lastStatus;
    }

    /**
     * Get an array with the curl response header
     *
     * @return array
     */
    public function getResponseHeader()
    {
        return $this->responseHeader;
    }

    /**
     * Add a request header
     *
     * @param string $key
     * @param string $value
     */
    public function addRequestHeader($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $newKey => $newValue) {
                $this->addRequestHeader($newKey, $newValue);
            }
        } else {
            $key = preg_replace_callback(
                '/([\s\-_]|^)([a-z0-9-_])/',
                function ($match) {
                    return strtoupper($match[0]);
                },
                $key
            );
            $this->requestHeader[] = "$key: $value";
        }
    }

    /**
     * Add a cookie
     *
     * @param string $key
     * @param string $value If value is null so, try to parse
     */
    public function addCookie($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $newKey => $newValue) {
                $this->addCookie($newKey, $newValue);
            }
        } else {
            $value = preg_replace('/(;\s*path=.+)/', '', $value);

            if (is_numeric($key)) {
                $this->cookies[] = $value;
            } else {
                $this->cookies[] = "$key=$value";
            }
        }
    }

    /**
     * Get the current CURLOPT_FOLLOWLOCATION
     *
     * @return boolean
     */
    public function isFollowingLocation()
    {
        return $this->getCurlOption(CURLOPT_FOLLOWLOCATION);
    }

    /**
     * Set the CURLOPT_FOLLOWLOCATION
     *
     * @param bool $value
     */
    public function setFollowLocation($value)
    {
        $this->setCurlOption(CURLOPT_FOLLOWLOCATION, $value);
    }

    /**
     * Setting the Proxy
     *
     * The full representation of the proxy is scheme://url:port,
     * but the only required is the URL;
     *
     * Some examples:
     *    my.proxy.com
     *    my.proxy.com:1080
     *    https://my.proxy.com:1080
     *    socks4://my.proxysocks.com
     *    socks5://my.proxysocks.com
     *
     * @param string $url The Proxy URL in the format scheme://url:port
     * @param string $username
     * @param string $password
     */
    public function setProxy($url, $username = null, $password = "")
    {
        $this->setCurlOption(CURLOPT_PROXY, $url);
        if (!is_null($username)) {
            $this->setCurlOption(CURLOPT_PROXYUSERPWD, "$username:$password");
        }
    }

    /**
     *
     * @return SoapClient
     */
    protected function getSoapClient()
    {
        if (is_null($this->soapClass)) {
            $this->soapClass = new SoapClient(
                null,
                array(
                    "location" => $this->url,
                    "uri" => "urn:xmethods-delayed-quotes",
                    "style" => SOAP_RPC,
                    "use" => SOAP_ENCODED
                )
            );

            if ($this->getCurlOption(CURLOPT_HTTPAUTH) == CURLAUTH_BASIC) {
                $curlPwd = explode(":", $this->getCurlOption(CURLOPT_USERPWD));
                $username = $curlPwd[0];
                $password = $curlPwd[1];
                $this->soapClass->setCredentials($username, $password);
            }
        }

        return $this->soapClass;
    }

    /**
     * Call a Soap client.
     *
     * For example:
     *
     * $webreq = new WebRequest("http://www.byjg.com.br/webservice.php/ws/cep");
     * $result = $webreq->soapCall("obterCep", new array("cep", "11111233"));
     *
     * @param string $method
     * @param array $params
     * @param array $soapOptions
     * @return string
     */
    public function soapCall($method, $params = null, $soapOptions = null)
    {
        $soapParams = null;

        if (is_array($params)) {
            $soapParams = array();
            foreach ($params as $key => $value) {
                $soapParams[] = new SoapParam($value, $key);
            }
        }

        if (!is_array($soapOptions) || (is_null($soapOptions))) {
            $soapOptions = array(
                "uri" => "urn:xmethods-delayed-quotes",
                "soapaction" => "urn:xmethods-delayed-quotes#getQuote"
            );
        }

        // Chamando método do webservice
        $result = $this->getSoapClient()->__call(
            $method,
            $soapParams,
            $soapOptions
        );

        return $result;
    }

    /**
     * Set the default curl options.
     * You can override this method to setup your own default options.
     * You can pass the options to the constructor also;
     */
    protected function defaultCurlOptions()
    {
        $this->curlOptions[CURLOPT_CONNECTTIMEOUT] = 30;
        $this->curlOptions[CURLOPT_TIMEOUT] = 30;
        $this->curlOptions[CURLOPT_HEADER] = true;
        $this->curlOptions[CURLOPT_RETURNTRANSFER] = true;
        $this->curlOptions[CURLOPT_USERAGENT] = "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)";
        $this->curlOptions[CURLOPT_FOLLOWLOCATION] = true;
        $this->curlOptions[CURLOPT_SSL_VERIFYHOST] = false;
        $this->curlOptions[CURLOPT_SSL_VERIFYPEER] = false;
    }

    /**
     * Set a custom CURL option
     *
     * @param int $key
     * @param mixed $value
     * @throws InvalidArgumentException
     */
    public function setCurlOption($key, $value)
    {
        if (!is_int($key)) {
            throw new InvalidArgumentException('It is not a CURL_OPT argument');
        }
        if ($key == CURLOPT_HEADER || $key == CURLOPT_RETURNTRANSFER) {
            throw new InvalidArgumentException('You cannot change CURLOPT_HEADER or CURLOPT_RETURNTRANSFER');
        }

        if (!is_null($value)) {
            $this->curlOptions[$key] = $value;
        } else {
            unset($this->curlOptions[$key]);
        }
    }

    /**
     * Get the current Curl option
     *
     * @param int $key
     * @return mixed
     */
    public function getCurlOption($key)
    {
        return (isset($this->curlOptions[$key]) ? $this->curlOptions[$key] : null);
    }

    /**
     * @param array|string|null $fields
     * @return string|array|null
     */
    protected function getMultiFormData($fields)
    {
        if (is_array($fields)) {
            return http_build_query($fields);
        }

        return $fields;
    }

    /**
     * @param array|string $fields
     */
    protected function setPostString($fields)
    {
        $replaceHeader = true;
        foreach ($this->requestHeader as $header) {
            if (stripos($header, 'content-type') !== false) {
                $replaceHeader = false;
            }
        }

        if ($replaceHeader) {
            $this->addRequestHeader("Content-Type", 'application/x-www-form-urlencoded');
        }

        $this->setCurlOption(CURLOPT_POSTFIELDS, $this->getMultiFormData($fields));
    }

    /**
     * @param array|string|null $fields
     */
    protected function setQueryString($fields)
    {
        $queryString = $this->getMultiFormData($fields);

        if (!empty($queryString)) {
            $this->requestUrl = $this->url . (strpos($this->url, "?") === false ? "?" : "&") . $queryString;
        }
    }

    /**
     * Request the method using the CURLOPT defined previously;
     *
     * @return resource
     */
    protected function curlInit()
    {
        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL, $this->requestUrl);
        $this->requestUrl = $this->url;  // Reset request URL
        // Set Curl Options
        foreach ($this->curlOptions as $key => $value) {
            curl_setopt($curlHandle, $key, $value);
        }

        // Check if have header
        if (count($this->requestHeader) > 0) {
            curl_setopt($curlHandle, CURLOPT_HTTPHEADER, $this->requestHeader);
            $this->requestHeader = []; // Reset request Header
        }

        // Add Cookies
        if (count($this->cookies) > 0) {
            curl_setopt($curlHandle, CURLOPT_COOKIE, implode(";", $this->cookies));
            $this->cookies = []; // Reset request Header
        }

        return $curlHandle;
    }

    /**
     * @param resource $curlHandle
     * @return string
     * @throws Exception
     */
    protected function curlGetResponse($curlHandle)
    {
        $result = curl_exec($curlHandle);
        $error = curl_error($curlHandle);
        if ($result === false) {
            curl_close($curlHandle);
            throw new Exception("CURL - " . $error);
        }

        $headerSize = curl_getinfo($curlHandle, CURLINFO_HEADER_SIZE);
        $this->lastStatus = curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);
        curl_close($curlHandle);

        $this->responseHeader = $this->parseHeader(substr($result, 0, $headerSize));
        return substr($result, $headerSize);
    }

    /**
     * @param string $rawHeaders
     * @return array
     */
    protected function parseHeader($rawHeaders)
    {
        $headers = array();
        $key = '';

        foreach (explode("\n", $rawHeaders) as $headerLine) {
            $headerLine = explode(':', $headerLine, 2);

            if (isset($headerLine[1])) {
                if (!isset($headers[$headerLine[0]])) {
                    $headers[$headerLine[0]] = trim($headerLine[1]);
                } elseif (is_array($headers[$headerLine[0]])) {
                    $headers[$headerLine[0]] = array_merge($headers[$headerLine[0]], [trim($headerLine[1])]);
                } else {
                    $headers[$headerLine[0]] = array_merge([$headers[$headerLine[0]]], [trim($headerLine[1])]);
                }

                $key = $headerLine[0];
            } else {
                if (substr($headerLine[0], 0, 1) == "\t") {
                    $headers[$key] .= "\r\n\t" . trim($headerLine[0]);
                } elseif (!$key) {
                    $headers[0] = trim($headerLine[0]);
                }
            }
        }
        return $headers;
    }

    /**
     *
     */
    protected function clearRequestMethod()
    {
        $this->setCurlOption(CURLOPT_POST, null);
        $this->setCurlOption(CURLOPT_PUT, null);
        $this->setCurlOption(CURLOPT_CUSTOMREQUEST, null);
    }

    /**
     * @param array|string|null $params
     * @param resource|null $curlHandle
     * @return null|resource
     */
    public function prepareGet($params = null, $curlHandle = null)
    {
        $this->clearRequestMethod();
        $this->setQueryString($params);
        if (empty($curlHandle)) {
            $curlHandle = $this->curlInit();
        }
        return $curlHandle;
    }

    /**
     * Make a REST Get method call
     *
     * @param array|null $params
     * @return string
     */
    public function get($params = null)
    {
        $curlHandle = $this->prepareGet($params);
        return $this->curlGetResponse($curlHandle);
    }

    /**
     * @param array|string|null $params
     * @param resource|null $curlHandle
     * @param int $curlOption
     * @param mixed $curlValue
     * @return resource
     */
    protected function prepare($params, $curlHandle, $curlOption, $curlValue)
    {
        $this->clearRequestMethod();
        $this->setCurlOption($curlOption, $curlValue);
        $this->setPostString($params);
        if (empty($curlHandle)) {
            $curlHandle = $this->curlInit();
        }
        return $curlHandle;
    }

    /**
     * @param string|array|null $params
     * @param resource|null $curlHandle
     * @return resource
     */
    public function preparePost($params = '', $curlHandle = null)
    {
        return $this->prepare(is_null($params) ? '' : $params, $curlHandle, CURLOPT_POST, true);
    }

    /**
     * Make a REST POST method call with parameters
     * @param array|string $params
     * @return string
     */
    public function post($params = '')
    {
        $handle = $this->preparePost($params);
        return $this->curlGetResponse($handle);
    }

    /**
     * @param MultiPartItem[] $params
     * @param resource|null $curlHandle
     * @return null|resource
     */
    public function preparePostMultiFormData($params = [], $curlHandle = null)
    {
        $this->clearRequestMethod();
        $this->setCurlOption(CURLOPT_POST, true);

        $boundary = 'boundary-' . md5(time());
        $body = '';
        foreach ($params as $item) {
            $body .= "--$boundary\nContent-Disposition: form-data; name=\"{$item->getField()}\";";
            $fileName = $item->getFileName();
            if (!empty($fileName)) {
                $body .= " filename=\"{$item->getFileName()}\";";
            }
            $contentType = $item->getContentType();
            if (!empty($contentType)) {
                $body .= "\nContent-Type: {$item->getContentType()}";
            }
            $body .= "\n\n{$item->getContent()}\n";
        }
        $body .= "--$boundary--";

        $this->addRequestHeader("Content-Type", "multipart/form-data; boundary=$boundary");

        $this->setPostString($body);
        if (empty($curlHandle)) {
            $curlHandle = $this->curlInit();
        }
        return $curlHandle;
    }

    /**
     * Make a REST POST method call with parameters
     *
     * @param MultiPartItem[] $params
     * @return string
     * @throws Exception
     */
    public function postMultiPartForm($params = [])
    {
        $handle = $this->preparePostMultiFormData($params);
        return $this->curlGetResponse($handle);
    }

    /**
     * Make a REST POST method call sending a payload
     *
     * @param string $data
     * @param string $contentType
     * @return string
     */
    public function postPayload($data, $contentType = "text/plain")
    {
        $this->addRequestHeader("Content-Type", $contentType);
        return $this->post($data);
    }

    /**
     * @param array|string|null $params
     * @param resource|null $curlHandle
     * @return resource
     */
    public function preparePut($params = null, $curlHandle = null)
    {
        return $this->prepare($params, $curlHandle, CURLOPT_CUSTOMREQUEST, 'PUT');
    }

    /**
     * Make a REST PUT method call with parameters
     *
     * @param array|string $params
     * @return string
     */
    public function put($params = null)
    {
        $handle = $this->preparePut($params);
        return $this->curlGetResponse($handle);
    }

    /**
     * Make a REST PUT method call sending a payload
     *
     * @param string $data
     * @param string $contentType
     * @return string
     */
    public function putPayload($data, $contentType = "text/plain")
    {
        $this->addRequestHeader("Content-Type", $contentType);
        return $this->put($data);
    }

    /**
     * @param array|string|null $params
     * @param resource|null $curlHandle
     * @return resource
     */
    public function prepareDelete($params = null, $curlHandle = null)
    {
        return $this->prepare($params, $curlHandle, CURLOPT_CUSTOMREQUEST, 'DELETE');
    }


    /**
     * Make a REST DELETE method call with parameters
     *
     * @param array|string $params
     * @return string
     */
    public function delete($params = null)
    {
        $handle = $this->prepareDelete($params);
        return $this->curlGetResponse($handle);
    }

    /**
     * Make a REST DELETE method call sending a payload
     *
     * @param string $data
     * @param string $contentType
     * @return string
     */
    public function deletePayload($data = null, $contentType = "text/plain")
    {
        $this->addRequestHeader("Content-Type", $contentType);
        return $this->delete($data);
    }

    /**
     * Makes a URL Redirection based on the current client navigation (Browser)
     *
     * @param array $params
     * @param bool $atClientSide If true send a javascript for redirection
     */
    public function redirect($params = null, $atClientSide = false)
    {
        $this->setQueryString($params);

        ob_clean();
        header('Location: ' . $this->requestUrl);
        if ($atClientSide) {
            echo "<script language='javascript'>window.top.location = '" . $this->requestUrl . "'; </script>";
        }
    }
}

class MultiPartItem
{
    protected $field;

    protected $content;

    protected $filename;

    protected $contentType;

    /**
     * MultiPartItem constructor.
     *
     * @param $field
     * @param $content
     * @param $filename
     * @param $contentType
     */
    public function __construct($field, $content = "", $filename = "", $contentType = "")
    {
        $this->field = $field;
        $this->content = $content;
        $this->filename = $filename;
        $this->contentType = $contentType;
    }

    public function loadFile($filename, $contentType = "")
    {
        if (!file_exists($filename)) {
            throw new FileNotFoundException("File '$filename' does not found!");
        }

        $this->content = file_get_contents($filename);
        $this->filename = basename($filename);
        $this->contentType = $contentType;
    }

    public function getField()
    {
        return $this->field;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function getFilename()
    {
        return $this->filename;
    }

    public function setField($field)
    {
        $this->field = $field;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function setFilename($filename)
    {
        $this->filename = $filename;
    }

    public function getContentType()
    {
        return $this->contentType;
    }

    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
    }

}
