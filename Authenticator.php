<?php

/**
 * This file is part of the Netrium Framework
 *
 * Copyright (c) 2015 Martin Sadovy (http://sodae.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Netrium\Addons\Twitter;

use Nette;
use Nette\Http\Request;
use Nette\Http\Response;

/**
 * Twitter web authenticator
 *
 * @author Martin Sadovy
 */
class Authenticator
{

	/**
	 * @var IStorage
	 */
	private $storage;

	/**
	 * @var \TwitterOAuth
	 */
	private $twitterOAuth;

	/**
	 * @var Request
	 */
	private $httpRequest;

	/**
	 * @var Response
	 */
	private $httpResponse;

	public function __construct(\TwitterOAuth $twitterOAuth, IStorage $storage, Request $httpRequest, Response $httpResponse)
	{
		$this->storage = $storage;
		$this->twitterOAuth = clone $twitterOAuth;
		$this->httpRequest = $httpRequest;
		$this->httpResponse = $httpResponse;
    }

	/**
	 * Try authenticate to twitter account
	 * @return FALSE|array
	 * @throws AuthenticationException
	 */
	public function tryAuthenticate()
	{
		try {
			if (!$this->storage->isAuthorized()) {
				$this->authorize();
			} else {
				$accessToken = $this->verify();
				if ($user = $this->twitterOAuth->get('account/verify_credentials')) {
					$this->resetAuthorize();
					return array(
						'user' => $user,
						'accessToken' => array(
							'key' => $accessToken['oauth_token'],
							'secret' => $accessToken['oauth_token_secret'],
					));
				} else {
					throw new AuthenticationException("Could not authenticate you.");
				}
			}
		} catch (AuthenticationException $e) {
			$this->resetAuthorize();
			throw $e;
		}
	}

	/**
	 * First step in oAuth authorization
	 * @throws AuthenticationException
	 * @throws Nette\Application\AbortException
	 */
	private function authorize()
	{
		$this->resetAuthorize();

		$requestToken = $this->twitterOAuth->getRequestToken($this->httpRequest->url->getAbsoluteUrl());
		$this->storage->setOAuthTokens($requestToken['oauth_token'], $requestToken['oauth_token_secret']);

		if ($this->twitterOAuth->http_code === 200) {
			$this->storage->setAuthorized();
			$url = $this->twitterOAuth->getAuthorizeURL($this->storage->getOAuthTokenKey());
			$this->httpResponse->redirect($url);
			throw new Nette\Application\AbortException; // stop!
		} else {
			throw new AuthenticationException("Could not connect to Twitter. Refresh the page or try again later.");
		}
	}

	/**
	 * Second step in authorization
	 * @return boolean
	 * @throws AuthenticationException
	 */
	private function verify()
	{
		$this->twitterOAuth->setOAuthToken($this->storage->getOAuthTokenKey(), $this->storage->getOAuthTokenSecret());

		$verifier = $this->httpRequest->getQuery('oauth_verifier');
		if (!$verifier) {
			throw new AuthenticationException("Missing request parametr 'oauth_verifier'");
		}

		$accessToken = $this->twitterOAuth->getAccessToken($verifier);
		if ($this->twitterOAuth->http_code === 200) {
			return $accessToken;
		} else {
			throw new AuthenticationException("Could not authenticate you.");
		}
	}

	/**
	 * Restart storage and oAuth-er for reauthorization
	 */
	public function resetAuthorize()
	{
		$this->twitterOAuth->cleanOAuthToken();
		$this->storage->clean();
	}

}
