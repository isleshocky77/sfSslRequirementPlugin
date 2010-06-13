<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 *
 * @package    symfony
 * @subpackage plugin
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Jonathan H. Wage <jonwage@gmail.com>
 * @version    SVN: $Id$
 */
class sfSslRequirementActionMixin
{
  /**
   * Registers the new methods in the component class.
   *
   * @param sfEventDispatcher A sfEventDispatcher instance
   */
  static public function register(sfEventDispatcher $dispatcher)
  {
    $mixin = new sfSslRequirementActionMixin();

    $dispatcher->connect('component.method_not_found', array($mixin, 'listenToMethodNotFound'));

    return $mixin;
  }

  /**
   * Listens to component.method_not_found event.
   *
   * @param  sfEvent A sfEvent instance
   *
   * @return Boolean true if the method has been found in this class, false otherwise
   */
  public function listenToMethodNotFound(sfEvent $event)
  {
    if (!method_exists($this, $method = $event['method']))
    {
      return false;
    }

    $event->setReturnValue(call_user_func(array($this, $method), $event->getSubject()));

    return true;
  }

  /**
   * Returns true if the action must always be called in SSL.
   *
   * @param  sfAction A sfAction instance
   *
   * @return Boolean  true if the action must always be called in SSL, false otherwise
   */
  protected function sslRequired(sfAction $action)
  {
    return $action->getSecurityValue('require_ssl');
  }

  /**
   * Returns true if the action can be called in SSL.
   *
   * @param  sfAction A sfAction instance
   *
   * @return Boolean  true if the action can be called in SSL, false otherwise
   */
  protected function sslAllowed($action)
  {
    return $action->getSecurityValue('allow_ssl');
  }

  /**
   * Returns the SSL URL for the given action.
   *
   * @param  sfAction A sfAction instance
   *
   * @return Boolean  The fully qualified SSL URL for the given action
   */
  protected function getSslUrl($action)
  {
    if (!$domain = $action->getSecurityValue('ssl_domain'))
    {
      $domain = substr_replace($action->getRequest()->getUri(), 'https', 0, 4);
    }

    return $domain;
  }

  /**
   * Returns the non SSL URL for the given action.
   *
   * @param  sfAction A sfAction instance
   *
   * @return Boolean  The fully qualified non SSL URL for the given action
   */
  protected function getNonSslUrl($action)
  {
    if (!$domain = $action->getSecurityValue('non_ssl_domain'))
    {
      $domain = substr_replace($action->getRequest()->getUri(), 'http', 0, 5);
    }

    return $domain;
  }
}
