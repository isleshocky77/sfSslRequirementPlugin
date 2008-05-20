<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 *
 * @package    symfony
 * @subpackage plugin
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfSslRequirementFilter extends sfFilter
{
  public function execute ($filterChain)
  {
    // execute only once and only if not using an environment that is disabled for SSL
    if ($this->isFirstCall() && !sfConfig::get('app_disable_sslfilter'))
    {
      // get the cool stuff
      $context = $this->getContext();
      $request = $context->getRequest();

      // only redirect if not posting and we actually have an http(s) request
      if ($request->getMethod() != sfRequest::POST && substr($request->getUri(), 0, 4) == 'http')
      {
        $controller = $context->getController();

        // get the current action instance
        $actionEntry    = $controller->getActionStack()->getLastEntry();
        $actionInstance = $actionEntry->getActionInstance();

        // request is SSL secured
        if ($request->isSecure())
        {
          // but SSL is not allowed
          if (!$actionInstance->sslAllowed() && $this->redirectToHttp())
          {
            $controller->redirect($actionInstance->getNonSslUrl());
            exit();
          }
        }
        // request is not SSL secured, but SSL is required
        elseif ($actionInstance->sslRequired() && $this->redirectToHttps())
        {
          $controller->redirect($actionInstance->getSslUrl());
          exit();
        }
      }
    }

    $filterChain->execute();
  }

  protected function redirectToHttps()
  {
    return true;
  }

  protected function redirectToHttp()
  {
    return true;
  }
}
