<?php

namespace App\Model;

class ImagesRepository extends Repository {

  public function getForAlbum($albumId) {
    return $this->getAll()->where('album_id', $albumId);
  }

}
