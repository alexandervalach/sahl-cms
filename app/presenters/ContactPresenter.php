<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\UI\Form;
use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;

class ContactPresenter extends BasePresenter {

    protected function createComponentContactForm() {
        $form = new Form;

        $form->addText('name', 'Meno:')
                ->setRequired("Meno je povinné pole.");

        $form->addText('email', 'E-mail:')
                ->setRequired("Email je povinný údaj.")
                ->addRule(Form::EMAIL, 'Neplatný e-mail.');

        $form->addTextArea('message', 'Správa:')
                ->setRequired("Text je povinný údaj.")
                ->setAttribute('class', 'form-control');

        $form->addSubmit('save', 'Odoslať');

        $form->onSuccess[] = $this->submittedContactForm;

        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    public function submittedContactForm($form) {
        $mail = new Message;
        $mailer = new SendmailMailer;

        $message = $form->getValues();

        $mail->setFrom($message->email, $message->name)
                ->setSubject('Spišká amatérska hokejová liga.')
                ->addTo('alexander.valach@gmail.com')
                ->setBody($message->message);

        $mailer->send($mail);

        $this->flashMessage('Ďakujeme, e-mail bol úspešne odoslaný.', 'success');
        $this->redirect('this');
    }

}
