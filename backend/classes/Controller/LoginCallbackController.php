<?php

namespace Minixer\Controller;

use Minixer\Entity\User;
use Minixer\Repository\TokenRepository;
use Minixer\Util\DateTimeUtil;
use Minixer\Util\SessionUtil;
use Minixer\Util\StringUtil;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class LoginCallbackController extends ControllerBase
{
    private $tokenRepository;

    public function __construct(TokenRepository $tokenRepository)
    {
        $this->tokenRepository = $tokenRepository;
    }

    public function __invoke(Request $request)
    {
        $oauthToken = $request->get('oauth_token');
        if (empty($oauthToken)) {
            throw new \Exception('invalid request');
        }

        $user = SessionUtil::getUser();
        if (empty($user)) {
            throw new \Exception('invalid request');
        }

        $consumerKey = $GLOBALS['app']['TWITTER_CONSUMER_KEY'];
        $consumerSecret = $GLOBALS['app']['TWITTER_CONSUMER_SECRET'];
        $oauth = new \OAuth($consumerKey, $consumerSecret, OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_URI);
        $oauth->setToken($oauthToken, $user->getOAuthTokenSecret());

        try {
            $tokenInfo = $oauth->getAccessToken('https://twitter.com/oauth/access_token');

            $oauthToken = $tokenInfo['oauth_token'];
            $oauthTokenSecret = $tokenInfo['oauth_token_secret'];
            $oauth->setToken($oauthToken, $oauthTokenSecret);
            $oauth->fetch('https://api.twitter.com/1.1/account/verify_credentials.json');
            $json = json_decode($oauth->getLastResponse());

            $profileImageUrl = $json->profile_image_url_https;
            $profileImageUrl = str_replace('_normal.png', '.png', $profileImageUrl);
            $apiToken = StringUtil::getRandom(16);

            $userData = new User([
                'id' => $json->id_str,
                'name' => $json->screen_name,
                'api_token' => $apiToken,
                'oauth_token' => $oauthToken,
                'oauth_token_secret' => $oauthTokenSecret,
                'profile_image_url' => $profileImageUrl,
                'status' => 'login',
                'last_loaded_at' => DateTimeUtil::getImmutableByNow(),
            ]);

            SessionUtil::setUser($userData);
            $this->tokenRepository->set($json->id_str, $apiToken);
        } catch (\Exception $e) {
            SessionUtil::removeUser();
            throw $e;
        }

        return new RedirectResponse('/mypage');
    }
}