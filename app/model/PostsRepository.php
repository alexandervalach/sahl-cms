<?php

namespace App\Model;

class PostsRepository extends Repository {

  public function getLatestPosts($limit = 3) {
    return $this->getAll()->order('id DESC')->limit($limit);
  }

  public function getImages($postId) {
    $con = $this->getConnection();
    return $con->table('post_images')->where('post_id', $postId);
  }
}
