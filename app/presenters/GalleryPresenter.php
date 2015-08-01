<?php 

namespace App\Presenters;

use Nette;

class GalleryPresenter extends BasePresenter 
{
  public $database;

  public function __construct(Nette\Database\Context $database)
  {
      $this->database = $database;
  }

  public function actionCreate() 
  {
      if(!$this->user->isLoggedIn() ) 
      {
          $this->redirect('Sign:in');
      }
  }

  public function actionDelete($albumId)
  {
      if(!$this->user->isLoggedIn() ) 
      {
          $this->redirect('Sign:in');
      }

      $this->template->album = $this->database->table('albums')->get($albumId);

      if( !$this->template->album ) {
         $this->error('Záznam nenájdený.');
      } 
  }

  public function actionEdit($albumId)
  {
      if(!$this->user->isLoggedIn() ) 
      {
          $this->redirect('Sign:in');
      }

      $album = $this->database->table('albums')->get($albumId);

      if(!$album)   $this->error('Album nenájdený.');

      $this['albumForm']->setDefaults( $album->toArray() );
  }

  public function albumFormSucceeded($form) 
  {
    if( !$this->user->isLoggedIn() )  $this->error('Musíš byť prihlásený.');

    $values = $form->getValues();
    $albumId = $this->getParameter('albumId');

    if(!$albumId) {
        $this->database->table('albums')->insert($values);
    } else {
        $this->database->table('albums')->get($albumId)
                                        ->update($values);
    } 

    $this->redirect('default');
  }

  protected function createComponentAlbumForm()
  {
    $form = new Nette\Application\UI\Form;

    $form->addText('album','Názov albumu')
         ->setAttribute('class','form-control')
         ->setRequired();

    $form->addSubmit('submit','Ulož záznam')
         ->setAttribute('class','btn btn-primary btn-large');

    $form->onSuccess[] = $this->albumFormSucceeded;

    $form->addProtection();
    return $form;
  }

  public function deleteFormSucceeded()
  {
    $id = $this->getParameter('albumId');
    $this->database->table('albums')->get($id)->delete();

    $this->flashMessage('Príspevok zmazaný.','success');
    $this->redirect('default');
  }

  public function formCancelled()
  {
    $this->redirect('default');
  }

  public function renderDefault()
  {
      $albums = $this->database->table('albums')
                               ->order('created_at DESC');

      $this->template->albums = $albums;
  }
}