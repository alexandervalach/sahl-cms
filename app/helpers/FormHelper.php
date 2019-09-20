<?php

namespace App\Helpers;

use Nette\Application\UI\Form;
use Nette\Forms\Controls\SelectBox;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Controls\TextInput;

/**
 * Class FormHelper
 * @package App\Helpers
 */
class FormHelper {

  /**
   * @param Form $form
   */
  public static function setBootstrapFormRenderer(Form $form) {
        $renderer = $form->getRenderer();
        $renderer->wrappers['controls']['container'] = NULL;
        $renderer->wrappers['form']['container'] = NULL;
        $renderer->wrappers['pair']['container'] = 'div class="form-group"';
        $rednerer->wrappers['label']['container'] = NULL;
        $renderer->wrappers['control']['cotainer'] = NULL;
        $renderer->wrappers['group']['label'] = NULL;
        $renderer->wrappers['group']['container'] = NULL;
        $renderer->wrappers['group']['p'] = NULL;

        foreach ($form->getComponents() as $component) {
            if ($component instanceOf TextInput) {
                $component->getControlPrototype()->class = "form-control";
            }

            if ($component instanceOf TextArea) {
                $component->getControlPrototype()->class = "form-control";
            }

            if ($component instanceOf SubmitButton) {
                if (empty($component->getControlPrototype()->class)) {
                    $component->getControlPrototype()->class = "btn btn-primary";
                }
            }

            if ($component instanceOf SelectBox) {
                $component->getControlPrototype()->class = "form-control";
            }
        }
    }

    public static function changeEmptyToZero($values) {
        foreach ($values as $value) {
            if (empty($value)) {
                $value = 0;
            }
        }
    }

}
