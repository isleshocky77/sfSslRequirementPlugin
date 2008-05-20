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
    $security = $action->getSecurityConfiguration();
    $actionName = $action->getActionName();

    if (isset($security[$actionName]['require_ssl']))
    {
      return $security[$actionName]['require_ssl'];
    }

    if (isset($security['all']['require_ssl']))
    {
      return $security['all']['require_ssl'];
    }

    return false;
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
    $security = $action->getSecurityConfiguration();
    $actionName = $action->getActionName();

    // If ssl is required, then we can assume they also want to allow it
    if ($this->sslRequired($action))
    {
      return true;
    }

    if (isset($security[$actionName]['allow_ssl']))
    {
      return $security[$actionName]['allow_ssl'];
    }

    if (isset($security['all']['allow_ssl']))
    {
      return $security['all']['allow_ssl'];
    }

    return false;
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
    $security = $action->getSecurityConfiguration();
    $actionName = $action->getActionName();

    if (isset($security[$actionName]['ssl_domain']))
    {
      return $security[$actionName]['ssl_domain'].$action->getRequest()->getScriptName().$action->getRequest()->getPathInfo();
    }
    else if (isset($security['all']['ssl_domain']))
    {
      return $security['all']['ssl_domain'].$action->getRequest()->getScriptName().$action->getRequest()->getPathInfo();
    }
    else
    {
      return substr_replace($action->getRequest()->getUri(), 'https', 0, 4);
    }
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
    $security = $action->getSecurityConfiguration();
    $actionName = $action->getActionName();

    if (isset($security[$actionName]['non_ssl_domain']))
    {
      return $security[$actionName]['non_ssl_domain'].$action->getRequest()->getScriptName().$action->getRequest()->getPathInfo();
    }
    else if (isset($security['all']['non_ssl_domain']))
    {
      return $security['all']['non_ssl_domain'].$action->getRequest()->getScriptName().$action->getRequest()->getPathInfo();
    }
    else
    {
      return substr_replace($action->getRequest()->getUri(), 'http', 0, 5);
    }
  }
}
