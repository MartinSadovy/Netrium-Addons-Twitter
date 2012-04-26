<?php

/**
 * This file is part of the Netrium Framework
 *
 * Copyright (c) 2012 Martin Sadovy (http://sadovy.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */



namespace Netrium\Addons\Twitter;

use Nette;



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
	 * @var Nette\Http\Context
	 */
	private $httpContext;

	public function __construct(\TwitterOAuth $twitterOAuth, IStorage $storage, Nette\Http\Context $httpContext)
	{
		$this->storage = $storage;
		$this->twitterOAuth = clone $twitterOAuth;
		$this->httpContext = $httpContext;
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

		$requestToken = $this->twitterOAuth->getRequestToken($this->httpContext->request->url->getAbsoluteUrl());
		$this->storage->setOAuthTokens($requestToken['oauth_token'], $requestToken['oauth_token_secret']);

		if ($this->twitterOAuth->http_code === 200) {
			$this->storage->setAuthorized();
			$url = $this->twitterOAuth->getAuthorizeURL($this->storage->getOAuthTokenKey());
			$this->httpContext->response->redirect($url);
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

		$varifier = $this->httpContext->request->getQuery('oauth_verifier');
		if (!$varifier) {
			throw new AuthenticationException("Missing request parametr 'oauth_verifier'");
		}

		$accessToken = $this->twitterOAuth->getAccessToken($varifier);
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
