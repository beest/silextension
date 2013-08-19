<?php

/**
 * @todo Inject data store, server and signature method
 * @todo Unit tests
 */

namespace Silextension\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Silextension\OAuth\DataStore\Test as TestDataStore;
use Silextension\OAuth\Server;
use Silextension\OAuth\SignatureMethod\HMACSHA1 as SignatureHmacSha1;
use Silextension\OAuth\Util;
use Silextension\OAuth\Request;

class OAuthProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $oauthDataStore = new TestDataStore();
        $oauthServer = new Server($oauthDataStore);
        $oauthServer->add_signature_method(new SignatureHmacSha1());

        $app->before(function(Request $request) use ($oauthServer)
        {
            // Construct the full URL including port
            // This will be normalized by the OAuthRequest class
            $url = ($request->isSecure() ? 'https' : 'http') . '://' . $request->getHost() . ':' . $request->getPort() . $request->getPathInfo();

            $method = $request->getMethod();

            // The request parameters are collected as follows:
            // 1. GET parameters from the URL query string
            // 2. Request body parameters (only for requests with Content-Type of application/x-www-form-urlencoded)
            // 3. Parameters in the OAuth HTTP Authorization header
            // The parameters are filtered, sorted and concatenated by the OAuth\Request class

            $params = $request->query->all();

            if ($method == 'POST' &&  $request->headers->has('Content-Type') && $request->headers->get('Content-Type') == 'application/x-www-form-urlencoded') {
                $bodyParams = Util::parse_parameters($request->getContent());
                $params = array_merge($params, $bodyParams);
            }

            // Authorization header is excluded from Symfony Request object
            // Therefore need to look at Apache headers directly
            $apacheHeaders = apache_request_headers();

            if (isset($apacheHeaders['Authorization']) && substr($apacheHeaders['Authorization'], 0, 6) == 'OAuth ') {
                $authParams = Util::split_header($apacheHeaders['Authorization']);
                $params = array_merge($params, $authParams);
            }

            $oauthRequest = new Request($method, $url, $params);
            $oauthServer->verify_request($oauthRequest);
        });
    }

    public function boot(Application $app)
    {
    }
}
