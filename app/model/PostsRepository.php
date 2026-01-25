<?php

namespace App\Model;

use Nette\Database\Table\Selection;
use Nette\Database\Table\ActiveRow;

class PostsRepository extends Repository {

  public function getLatestPosts($limit = 3) {
    return $this->getAll()->order('id DESC')->limit($limit);
  }

  public function getImages(ActiveRow|int $postId): Selection
  {
    $db = $this->getConnection();
    return $db->table('post_images')->where('post_id', $postId);
  }
}
