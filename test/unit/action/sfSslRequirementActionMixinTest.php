<?php

if (!isset($_SERVER['SYMFONY']))
{
  die("You must set the \"SYMFONY\" environment variable to the symfony lib dir (export SYMFONY=/path/to/symfony/lib/).\n");
}

require_once $_SERVER['SYMFONY'].'/vendor/lime/lime.php';
require_once $_SERVER['SYMFONY'].'/autoload/sfCoreAutoload.class.php';
sfCoreAutoload::register();

require_once dirname(__FILE__).'/../../../lib/action/sfSslRequirementActionMixin.class.php';
require_once dirname(__FILE__).'/MockAction.class.php';
require_once dirname(__FILE__).'/MockRequest.class.php';

class MixinProxy extends sfSslRequirementActionMixin
{
  public $mixin;

  public function __call($m, $a)
  {
    return call_user_func_array(array($this->mixin, $m), $a);
  }
}

$t = new lime_test(5);

$proxy = new MixinProxy();
$proxy->mixin = new sfSslRequirementActionMixin();

$action = new MockAction();
$request = new MockRequest();
$action->request = $request;

// ->getSslUrl()
$t->diag('->getSslUrl()');

$action->securityValues = array('ssl_domain' => 'https://example.com/foo');
$t->is($proxy->getSslUrl($action), 'https://example.com/foo', '->getSslUrl() uses the action\'s "ssl_domain" security value');

$action->securityValues = array();
$request->uri = 'http://example.com/foo/bar';
$t->is($proxy->getSslUrl($action), 'https://example.com/foo/bar', '->getSslUrl() converts the current URI if no "ssl_domain" is set');

// ->getNonSslUrl()
$t->diag('->getNonSslUrl()');

$action->securityValues = array('non_ssl_domain' => 'http://example.com/foo');
$t->is($proxy->getNonSslUrl($action), 'http://example.com/foo', '->getNonSslUrl() uses the action\'s "non_ssl_domain" security value');

$action->securityValues = array();
$request->uri = 'https://example.com/foo/bar';
$t->is($proxy->getNonSslUrl($action), 'http://example.com/foo/bar', '->getNonSslUrl() converts the current URI if no "non_ssl_domain" is set');

// ->sslAllowed()
$t->diag('->sslAllowed()');

$action->securityValues = array('require_ssl' => true);
$t->is($proxy->sslAllowed($action), true, '->sslAllowed() returns true when "require_ssl" is true');
