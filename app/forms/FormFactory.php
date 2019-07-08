<?php

declare(strict_types = 1);

namespace App\Forms;

use Nette\Application\UI\Form;
use Nette\SmartObject;

/**
 * Factory for form rendering
 * @package App\Forms
 */
class FormFactory
{
  use SmartObject;

  /**
   * Creates and returns form with default settings
   * @return Form
   */
  public function create(): Form
  {
    $form = new Form;
    return $form;
  }
}