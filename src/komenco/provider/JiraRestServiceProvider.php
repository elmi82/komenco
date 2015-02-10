<?php namespace komenco\provider;
/*
 * Copyright (C) 2015, BMW Car IT GmbH
 *
 * Permission is hereby granted, free of charge, to any person obtaining a
 * copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 * DEALINGS IN THE SOFTWARE.
 */

require_once __DIR__ .  '/../../..' . '/vendor/autoload.php';

use Silex\ServiceProviderInterface;
use Silex\Application;
use chobie\Jira\Api;
use chobie\Jira\Api\Authentication\Anonymous;
use chobie\Jira\Api\Authentication\Basic;
use komenco\jira\GuzzleClient;

class JiraRestServiceProvider implements ServiceProviderInterface {
	protected $config;

	public function __construct(array $config) {
		$defaults = array(
			'base_url' => 'http://localhost:8181/',
			'basic_auth' => array()
		);

		$this->config = array_merge($defaults, $config);
	}

	public function boot(Application $app) {}

	public function register(Application $app) {
		$app['jira.api.client'] = $app->share(function ($app) {
			return new GuzzleClient($app['jira.oauth.client']);
		});

		if (empty($this->config['basic_auth'])) {
			$auth = new Anonymous();
		} else {
			$auth = new Basic($this->config['basic_auth']['username'],
								$this->config['basic_auth']['password']);
		}

		$app['jira.api'] = $app->share(function ($app) use ($auth){
			$client = new Api($this->config['base_url'],
								$auth,
								$app['jira.api.client']);

			return $client;
		});
	}
}
