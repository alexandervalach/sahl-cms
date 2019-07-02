<?php

namespace App\Model;

use Nette;
use Nette\Security as NS;
use Nette\Security\Passwords;
use Nette\Security\IIdentity;

/**
 * Users management.
 */
class UserManager implements NS\IAuthenticator
{
  const COLUMN_NAME = 'username';

  /** @var UsersRepository */
  private $usersRepository;

  public function __construct(UsersRepository $usersRepository) {
    $this->usersRepository = $usersRepository;
  }

  /**
   * Performs an authentication.
   * @return NS\Identity
   * @throws NS\AuthenticationException
   */
  public function authenticate(array $credentials): IIdentity
  {
    list($username, $password) = $credentials;

    $row = $this->usersRepository->findByValue(self::COLUMN_NAME, $username)->fetch();

    if (!$row) {
      throw new NS\AuthenticationException('Používateľ neexistuje.');
    }

    if (!NS\Passwords::verify($password, $row->password)) {
      throw new NS\AuthenticationException('Nesprávne heslo.');
    }

    return new NS\Identity($row->id, $row->role, [self::COLUMN_NAME => $row->username]);
  }
}
