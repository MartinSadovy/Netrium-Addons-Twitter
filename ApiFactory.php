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


class ApiFactory
{


	/** @var string */
	private $consumerKey;

	/** @var string */
	private $consumerSecretKey;

	public function __construct($key, $secret)
	{
		$this->consumerKey = $key;
		$this->consumerSecretKey = $secret;
	}


	/**
	 * Create api for twitter API
	 * @param string|null $tokenKey users oauth token
	 * @param string|null $tokenSecret users oauth secret token
	 * @return \TwitterOAuth
	 * @throws \InvalidArgumentException if $tokenKey is filled but $tokenSecret not.
	 */
	public function create($tokenKey = NULL, $tokenSecret = NULL)
	{
		$service = new \TwitterOAuth($this->consumerKey, $this->consumerSecretKey);
		if ($tokenKey !== NULL && $tokenSecret !== NULL) {
			$service->setOAuthToken($tokenKey, $tokenSecret);
		} elseif ($tokenKey !== NULL && $tokenSecret === NULL) {
			throw new \InvalidArgumentException('Twitter api factory needs both (key and secret) tokens.');
		}
		return $service;
	}

}
