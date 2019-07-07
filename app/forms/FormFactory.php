<?php

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
  public function create()
  {
    $form = new Form;
    // Prostor pro výchozí nastavení.
    return $form;
  }
}