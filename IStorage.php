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

/**
 * Interface of storage for save information while authentization
 *
 * @author Martin Sadovy
 */
interface IStorage
{

	/**
	 * Application authorizated
	 */
	public function isAuthorized();

	public function setAuthorized();

	/**
	 * oAuth Token
	 */
	public function getOAuthTokenKey();

	public function getOAuthTokenSecret();

	public function setOAuthTokens($key, $secret);

	/**
	 * Clean data in storage
	 */
	public function clean();
}
