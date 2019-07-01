<?php

namespace App\Model;

class PostsRepository extends Repository {

  public function getLatestPosts($limit = 3) {
    return $this->getAll()->order('id DESC')->limit($limit);
  }
}
