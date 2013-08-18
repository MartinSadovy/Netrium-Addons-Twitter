<?php

/**
 * This file is part of the Netrium Framework
 *
 * Copyright (c) 2013 Martin Sadovy (http://sadovy.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Netrium\Addons\Twitter;

use Nette;

/**
 * Twitter extension
 *
 * @author Martin Sadovy
 */
class TwitterExtension extends Nette\DI\CompilerExtension
{

	public function loadConfiguration()
	{
		$config = $this->getConfig(array(
			'authenticator.sessionNamespace' => 'Twitter'
		));
		if (!isset($config['consumerKey']) || !isset($config['consumerSecretKey']))
			throw new Nette\InvalidArgumentException('Twitter extension have to consumerKey and consumerSecretKey parameters');

		$builder = $this->getContainerBuilder();

		$api = $builder->addDefinition($this->prefix('api'))
				->setClass('TwitterOAuth', array(
					$config['consumerKey'],
					$config['consumerSecretKey']
				))->setShared(FALSE);

		if (isset($config['accessKey']) && isset($config['accessSecret'])) {
			$api->addSetup('setOAuthToken', array(
				$config['accessKey'],
				$config['accessSecret']
			));
		}

		$builder->addDefinition($this->prefix('authenticator.storage'))
			->setClass('Netrium\Addons\Twitter\SessionStorage')
			->setFactory(get_called_class() . '::createSessionStorage', array('@session', $config['authenticator.sessionNamespace']));

		$builder->addDefinition($this->prefix('authenticator'))
			->setClass('Netrium\Addons\Twitter\Authenticator', array(
				'@' . $this->prefix('api'),
				'@' . $this->prefix('authenticator.storage'),
				'@nette.httpContext'
		));
	}

	public static function createSessionStorage($session, $name)
	{
		return new SessionStorage($session->getSection($name));
	}

}
