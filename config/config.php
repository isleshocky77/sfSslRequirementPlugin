<?php

require_once(dirname(__FILE__).'/../lib/action/sfSslRequirementActionMixin.class.php');

sfSslRequirementActionMixin::register($this->dispatcher);
