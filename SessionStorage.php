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

/**
 * Session storage for save information while authentization
 *
 * @author Martin Sadovy
 */
class SessionStorage implements IStorage
{

	/** @var Nette\Http\SessionSection */
	public $session;

	public function __construct(Nette\Http\SessionSection $session)
	{
		$this->session = $session;
	}

	public function isAuthorized()
	{
		return (bool) $this->session->authorized;
	}

	public function setAuthorized()
	{
		return $this->session->authorized = TRUE;
	}

	public function getOAuthTokenKey()
	{
		return $this->session->oAuthTokenKey;
	}

	public function getOAuthTokenSecret()
	{
		return $this->session->oAuthTokenSecret;
	}

	public function setOAuthTokens($key, $secret)
	{
		$this->session->oAuthTokenKey = $key;
		$this->session->oAuthTokenSecret = $secret;
	}

	public function clean()
	{
		$this->session->remove();
	}

}
